<?php

namespace App\Livewire;

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\SMSController;
use App\Models\BillingInfo;
use App\Models\CollectionSummary;
use App\Models\CustomersInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentCollection extends Component
{
    use WithPagination;

    public $user_list;

    public $customer_list = '';

    public ?CustomersInfo $info_data = null;

    public $collectionSummary = [];

    public $highlightedIndex = 0;

    public $customers = [];

    // In your Livewire component
    public $total_amount = 0;

    public $paid_amount;

    public $due_amount = '';

    public $expire_date = '';

    public $advance_paid = 0;

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['payment-collection'])) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }
    
    /**
     * Returns a CustomersInfo query builder scoped to the reseller's
     * own customers when the logged-in user is a Reseller, otherwise
     * returns an unscoped query for admins.
     */
    private function resellerScope()
    {
        $user = auth()->user();

        if ($user->hasRole('Reseller') && $user->reseller) {
            return CustomersInfo::where('reseller_id', $user->reseller->id);
        }

        return CustomersInfo::query();
    }

    public function updatedCustomerList()
    {
        if ($this->customer_list) {
            // Fetch customers dynamically based on the search term
            $this->customers = $this->resellerScope()
                ->search($this->customer_list)
                ->leftJoin('p_p_p_secrets', 'p_p_p_secrets.id', '=', 'customers_infos.ppp_user_id')
                ->with('customerAddress')
                ->select('customers_infos.id', 'customers_infos.customer_unique_id', 'customers_infos.customer_name', 'customers_infos.email', 'customers_infos.mobile', 'p_p_p_secrets.username as username')
                ->take(10)
                ->get();
        } else {
            $this->customers = [];
        }

        // Reset highlighted index whenever the list updates
        $this->highlightedIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightedIndex < count($this->customers) - 1) {
            $this->highlightedIndex++;
        }
    }

    public function decrementHighlight()
    {
        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        }
    }

    public function selectHighlightedCustomer()
    {
        if (isset($this->customers[$this->highlightedIndex])) {
            $selectedCustomer = $this->customers[$this->highlightedIndex];
            $this->selectCustomer(encrypt($selectedCustomer->customer_unique_id));
        }
    }

    public function calculatePayment()
    {
        if ($this->info_data) {
            $this->due_amount = (int) $this->info_data->billing->due_amount - (int) $this->paid_amount;
        }

        if ($this->paid_amount >= 0 && $this->info_data) {
            $this->advance_paid = (int) $this->paid_amount - (int) $this->total_amount;

            if ($this->due_amount > 0) {
                $extra_month = floor(((int) $this->due_amount) / (int) ($this->info_data->billing->monthly_rent == 0 || $this->info_data->billing->monthly_rent == null ? 1 : $this->info_data->billing->monthly_rent));

                if ($extra_month < $this->info_data->billing->auto_disable_month) {
                    $this->expire_date = Carbon::parse($this->info_data->billing->auto_disable_date)->month(now()->month)->year(now()->year)
                        ->subMonths($extra_month)
                        ->format('Y-m-d');
                } else {
                    $this->expire_date = Carbon::parse($this->info_data->billing->auto_disable_date)->month(now()->month)->year(now()->year)
                        ->subMonths($this->info_data->billing->auto_disable_month)
                        ->addMonths(1)
                        ->format('Y-m-d');
                }
            } elseif ($this->advance_paid > 0) {
                if ($this->info_data->billing->monthly_rent == 0 || $this->info_data->billing->monthly_rent == null) {
                    $extra_month = 1;
                } else {
                    $extra_month = 1 + floor(((int) $this->advance_paid) / (int) $this->info_data->billing->monthly_rent);
                }

                $this->expire_date = Carbon::parse($this->info_data->billing->auto_disable_date)->month(now()->month)->year(now()->year)
                    ->addMonths($extra_month)
                    ->format('Y-m-d');
            } else {
                $this->expire_date = Carbon::parse($this->info_data->billing->auto_disable_date)->month(now()->month)->year(now()->year)
                    ->addMonths(1)
                    ->format('Y-m-d');
            }
        } else {
            $this->expire_date = '';
        }
    }

    public function selectCustomer($value)
    {
        // $this->paid_amount;
        $this->expire_date = '';
        $customer_id = decrypt($value);
        $this->customer_list = '';
        $this->customers = [];
        $this->info_data = $this->resellerScope()
            ->where('customer_unique_id', $customer_id)
            ->with([
                'customerAddress',
                'billing',
                'official',
                'pppUser',
                // 'collectionSummary' => function ($query) {
                //     $query->whereMonth('collection_date', Carbon::now()->month)
                //         ->whereYear('collection_date', Carbon::now()->year);
                // }
            ])
            ->first();
        $this->collectionSummary = CollectionSummary::where('customer_collection_unique_id', $customer_id)
            ->whereMonth('collection_date', Carbon::now()->month)
            ->whereYear('collection_date', Carbon::now()->year)
            ->get();
        $this->paid_amount = '';
        $this->total_amount = $this->info_data->billing->due_amount;
        $this->due_amount = (int) $this->total_amount - (int) $this->paid_amount;
    }

    public function savePayment()
    {
        if ($this->info_data->status != 'active') {
            sweetalert()->showDenyButton()->info('Are you sure you want to Enable this customer?');
        }
        if ($this->info_data->status == 'active') {
            $this->paymentSubmit();
        }
    }

    #[On('sweetalert:confirmed')]
    public function onConfirmed(array $payload): void
    {
        $customerUniqueId = is_array($this->info_data)
            ? ($this->info_data['customer_unique_id'] ?? null)
            : ($this->info_data->customer_unique_id ?? null);

        if ($customerUniqueId) {
            $customer = CustomersInfo::where('customer_unique_id', $customerUniqueId)->first();
            if ($customer) {
                $customer->update([
                    'status' => 'active',
                ]);
                $this->info_data = $customer;
            }

            $customerEnable = new CustomersController;
            $customerEnable->customerEnable(encrypt($customerUniqueId));
        }
        $this->paymentSubmit();
    }

    #[On('sweetalert:denied')]
    public function onDeny(array $payload): void
    {
        $this->paymentSubmit();
    }

    public function paymentSubmit()
    {
        DB::beginTransaction();
        try {
            CollectionSummary::create([
                'customer_collection_unique_id' => $this->info_data->customer_unique_id,
                'collection_date' => Carbon::now(),
                'collection_amount' => $this->paid_amount,
                'collected_by' => auth()->user()->email,
                'payment_status' => 'paid',
            ]);

            BillingInfo::where('customer_bill_unique_id', $this->info_data->customer_unique_id)
                ->update([
                    'paid_amount' => $this->paid_amount + $this->info_data->billing->paid_amount,
                    'paid_date' => Carbon::now(),
                    'auto_disable_date' => $this->expire_date,
                    'due_amount' => $this->due_amount,
                ]);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            sweetalert()->error($th->getMessage(), ['title' => 'Error']);
            return;
        }

        try {
            // Fire reseller commission event
            event(new \App\Events\PackagePurchased($this->info_data, (float) $this->paid_amount));

            flash()->success('Payment added successfully.');
            $data = [
                'recipient' => $this->info_data->mobile,
                'customer_name' => $this->info_data->customer_name,
                'collection_amount' => $this->paid_amount,
                'ip_or_user_name' => $this->info_data->pppUser->username ?? '',
                'due_amount' => $this->due_amount,
                'company_name' => siteUrlSettings('site_name'),
            ];

            // Call the SMSController's method
            $response = app(SMSController::class)->paymentCollectionSMS($data);
            if ($response && $response->isSuccessful()) {
                flash()->success($response->getMessage());
            } else {
                flash()->error($response ? $response->getMessage() : 'Failed to send SMS notification.');
            }
        } catch (\Throwable $th) {
            sweetalert()->error($th->getMessage(), ['title' => 'Error']);
        } finally {
            $this->reset();
            $this->dispatch('focusInput');
        }
    }

    public function render()
    {
        return view('livewire.payment-collection')->layout('layouts.app');
    }
}
