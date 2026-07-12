<?php

namespace App\Filament\Pages;

use App\Http\Controllers\MikrotikController;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PayBill extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected string $view = 'filament.pages.pay-bill';

    protected static ?string $navigationLabel = 'Pay Bill';

    protected static ?string $title = 'Pay Bill';

    protected static ?int $navigationSort = 4;

    public $customer;

    public $billing;

    public $amount;

    public $paymentMethod = 'bkash';

    public ?string $connectionStatus = 'checking...';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->guard('ppp')->check();
    }

    public function mount()
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $this->customer = $user->customer;
        if (! $this->customer) {
            session()->flash('error', 'Customer account details not found.');

            return;
        }

        $this->billing = $this->customer->billing;
        if (! $this->billing) {
            session()->flash('error', 'Billing account details not found.');

            return;
        }

        // Set amount to pay, defaulting to outstanding due amount if any, otherwise monthly rent
        $due = (float) $this->billing->due_amount;
        $this->amount = $due > 0 ? $due : (float) $this->billing->monthly_rent;

        // Set default payment method based on what's active
        if (siteUrlSettings('payment_bkash_enabled')) {
            $this->paymentMethod = 'bkash';
        } elseif (siteUrlSettings('payment_nagad_enabled')) {
            $this->paymentMethod = 'nagad';
        } elseif (siteUrlSettings('payment_sslcommerz_enabled')) {
            $this->paymentMethod = 'sslcommerz';
        }

        $this->connectionStatus = 'checking...';
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
            \Log::warning("Failed to check live status for PPP User {$username} on router {$routerName}: ".$e->getMessage());
            $this->connectionStatus = 'unknown';
        }
    }

    public function pay()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'paymentMethod' => 'required|in:bkash,nagad,sslcommerz',
        ]);

        $routeParams = ['amount' => $this->amount];

        // Auto-detect local development to offer sandbox mock helper
        $host = request()->getHost();
        $isLocal = str_ends_with($host, '.test') || str_ends_with($host, '.local') || $host === 'localhost' || $host === '127.0.0.1';

        if ($isLocal) {
            $routeParams['mock'] = 'true';
        }

        if ($this->paymentMethod === 'bkash') {
            return redirect()->route('payment.bkash.initiate', $routeParams);
        } elseif ($this->paymentMethod === 'nagad') {
            return redirect()->route('payment.nagad.initiate', $routeParams);
        } elseif ($this->paymentMethod === 'sslcommerz') {
            return redirect()->route('payment.sslcommerz.initiate', $routeParams);
        }
    }
}
