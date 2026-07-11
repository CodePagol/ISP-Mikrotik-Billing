<?php

namespace App\Filament\Pages;

use App\Http\Controllers\MikrotikController;
use App\Models\BillingInfo;
use App\Models\CollectionSummary;
use App\Models\CustomersInfo;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PortalDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected string $view = 'filament.pages.portal-dashboard';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = '';

    public $customer;

    public $billing;

    public $pppUser;

    public $package;

    public $recentPayments;

    public $dueAmount = 0;

    public ?string $connectionStatus = 'checking...';

    // Customer Review system variables
    public $showReviewModal = false;
    public $reviewRating = 5;
    public $reviewComment = '';
    public $existingReview = null;
    public $timerRemainingMs = 0;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->guard('ppp')->check();
    }

    public function triggerReviewModal()
    {
        $this->showReviewModal = true;
    }

    public function submitReview()
    {
        $user = Auth::guard('ppp')->user();
        if (!$user) {
            abort(403);
        }

        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewComment' => 'required|string|min:5|max:1000',
        ]);

        if ($this->existingReview) {
            if ($this->existingReview->edit_count >= 2) {
                session()->flash('review_error', 'You have already edited your review the maximum allowed times (2 times).');
                return;
            }

            $this->existingReview->update([
                'rating' => $this->reviewRating,
                'comment' => $this->reviewComment,
                'edit_count' => $this->existingReview->edit_count + 1,
            ]);

            session()->flash('review_success', 'Review updated successfully.');
        } else {
            $this->existingReview = \App\Models\CustomerReview::create([
                'ppp_user_id' => $user->id,
                'rating' => $this->reviewRating,
                'comment' => $this->reviewComment,
                'edit_count' => 0,
            ]);

            session()->flash('review_success', 'Review submitted successfully. Thank you!');
        }

        $this->showReviewModal = false;
    }

    public function mount(): void
    {
        $user = Auth::guard('ppp')->user();
        if (! $user) {
            abort(403);
        }

        $this->pppUser = $user;
        $this->customer = CustomersInfo::with(['pppUser', 'package', 'customerAddress'])
            ->where('ppp_user_id', $user->id)
            ->first();

        // Retrieve existing review
        $this->existingReview = \App\Models\CustomerReview::where('ppp_user_id', $user->id)->first();

        if ($this->existingReview) {
            $this->reviewRating = $this->existingReview->rating;
            $this->reviewComment = $this->existingReview->comment;
        }

        // Trigger review popup conditions
        if ($user->login_count >= 2) {
            if (!$this->existingReview) {
                $this->showReviewModal = true;
            }
        } elseif ($user->login_count == 1 && !$this->existingReview) {
            $loginTime = session('portal_login_time', now());
            $elapsedSeconds = now()->diffInSeconds($loginTime);
            $thirtyMinsInSeconds = 30 * 60;

            if ($elapsedSeconds >= $thirtyMinsInSeconds) {
                $this->showReviewModal = true;
            } else {
                $this->timerRemainingMs = ($thirtyMinsInSeconds - $elapsedSeconds) * 1000;
            }
        }

        if ($this->customer) {
            $this->billing = BillingInfo::where('customer_bill_unique_id', $this->customer->customer_unique_id)->first();
            $this->package = $this->customer->package;

            $this->recentPayments = CollectionSummary::where('customer_collection_unique_id', $this->customer->customer_unique_id)
                ->orderByDesc('collection_date')
                ->take(5)
                ->get();

            if ($this->billing) {
                $this->dueAmount = (float) ($this->billing->due_amount ?? 0);
            }
        }
    }

    public function getStatusColor(): string
    {
        $status = $this->customer?->status ?? 'disabled';

        return match ($status) {
            'active' => 'emerald',
            'free' => 'blue',
            default => 'rose',
        };
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (! $this->billing?->auto_disable_date) {
            return null;
        }
        $expiry = Carbon::parse($this->billing->auto_disable_date);

        return max(0, now()->diffInDays($expiry, false));
    }

    public function checkLiveStatus()
    {
        if (! $this->customer || ! $this->customer->pppUser) {
            $this->connectionStatus = 'offline';

            return;
        }

        $pppUser = $this->customer->pppUser;
        $routerName = $pppUser->router_name;
        $username = $pppUser->username;

        if (! $routerName || ! $username) {
            $this->connectionStatus = 'offline';

            return;
        }

        try {
            $ctrl = app(MikrotikController::class);

            // Query MikroTik active sessions for this specific user, bypassing the cache
            $activeSessions = $ctrl->singleRead(
                $routerName,
                '/ppp/active/print',
                'ppp active print without-paging terse where name='.$ctrl->mtQuote($username),
                ['name' => $username],
                false,
                true // bypassCache
            );

            if (is_array($activeSessions) && count($activeSessions) > 0 && is_array($activeSessions[0])) {
                $this->connectionStatus = 'online';
            } else {
                $this->connectionStatus = 'offline';
            }
        } catch (\Exception $e) {
            Log::warning("Failed to check live status for PPP User {$username} on router {$routerName}: ".$e->getMessage());
            $this->connectionStatus = 'unknown';
        }
    }
}
