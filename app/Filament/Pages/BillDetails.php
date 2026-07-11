<?php

namespace App\Filament\Pages;

use App\Models\BillingInfo;
use App\Models\CollectionSummary;
use App\Models\CustomersInfo;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BillDetails extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.bill-details';

    protected static ?string $navigationLabel = 'Bill Details';

    protected static ?string $title = 'Bill Details';

    protected static ?int $navigationSort = 3;

    public $customer;

    public $billing;

    public $pppUser;

    public $lastPayment;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->guard('ppp')->check();
    }

    public function mount(): void
    {
        $user = Auth::guard('ppp')->user();
        if (! $user) {
            abort(403);
        }

        $this->pppUser = $user;
        $this->customer = CustomersInfo::with(['pppUser', 'package'])
            ->where('ppp_user_id', $user->id)
            ->first();

        if ($this->customer) {
            $this->billing = BillingInfo::where('customer_bill_unique_id', $this->customer->customer_unique_id)->first();

            $this->lastPayment = CollectionSummary::where('customer_collection_unique_id', $this->customer->customer_unique_id)
                ->where('payment_status', 'paid')
                ->orderByDesc('collection_date')
                ->first();
        }
    }
}
