<?php

namespace App\Livewire;

use App\Http\Controllers\SMSController;
use App\Models\BillingInfo;
use App\Models\CollectionSummary;
use App\Models\CustomersInfo;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionEdit extends Component
{
    use WithPagination;

    public $user_list;

    public $customer_list = '';

    public $info_data = [];

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
        if (! hasAccess(['Super Admin'], ['payment-collection-edit'])) {
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
                ->select('customers_infos.id', 'customers_infos.customer_unique_id', 'customers_infos.customer_name', 'customers_infos.email', 'customers_infos.mobile', 'customers_infos.status', 'p_p_p_secrets.username as username')
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
        if ($this->paid_amount > 0) {
            $this->advance_paid = (int) $this->paid_amount - (int) $this->total_amount;
            $extra_month = floor(((int) $this->paid_amount + (int) $this->info_data->billing->paid_amount) / (int) ($this->info_data->billing->monthly_rent == 0 || $this->info_data->billing->monthly_rent == null ? 1 : $this->info_data->billing->monthly_rent)); // Discard decimals to get a whole number

            $this->expire_date = Carbon::parse($this->info_data->billing->auto_disable_date)
                ->addMonths($extra_month)
                ->format('Y-m-d');
        } else {
            $this->expire_date = '';
        }
    }

    public function savePayment()
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
                    'due_amount' => $this->due_amount,
                ]);
            DB::commit();
            flash('Payment added successfully.')->success();
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
            $this->reset();
            $this->dispatch('focusInput');
        } catch (\Throwable $th) {
            DB::rollBack();
            flash('Error:'.$th->getMessage())->error();
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

        $this->total_amount = $this->info_data->billing->due_amount;
        $this->due_amount = (int) $this->total_amount - (int) $this->paid_amount;
    }

    public function deleteCollection($id)
    {
        $collection = CollectionSummary::find($id);
        if ($collection) {
            $colleted = $collection->collection_amount;

            $billing = BillingInfo::where('customer_bill_unique_id', $collection->customer_collection_unique_id)->first();
            if (! $billing) {
                flash()->error('Billing information not found.');

                return;
            }

            DB::beginTransaction();
            try {
                // Update billing record
                $billing->update([
                    'paid_amount' => $billing->paid_amount - $colleted,
                    'due_amount' => $billing->due_amount + $colleted,
                ]);

                // Revert Voucher status if it was a voucher payment
                if ($collection->payment_method === 'voucher' || $collection->payment_type === 'voucher') {
                    $voucher = Voucher::where('code', $collection->transaction_id)->first();
                    if ($voucher) {
                        $voucher->update([
                            'status' => 'unused',
                            'used_by_customer_id' => null,
                            'used_at' => null,
                        ]);
                    }
                }

                // Delete the collection record
                $collection->delete();

                DB::commit();

                $successMsg = 'Collection deleted successfully.';
                if ($collection->payment_type === 'online' || $collection->payment_method === 'voucher') {
                    $successMsg .= ' Method: '.strtoupper($collection->payment_method).', TrxID/Code: '.$collection->transaction_id;
                }
                flash()->success($successMsg);

                // Send SMS if customer info is loaded
                if ($this->info_data && isset($this->info_data->customer_unique_id) && $this->info_data->customer_unique_id === $collection->customer_collection_unique_id) {
                    $data = [
                        'recipient' => $this->info_data->mobile,
                        'customer_name' => $this->info_data->customer_name,
                        'collection_amount' => $colleted,
                        'ip_or_user_name' => $this->info_data->pppUser->username ?? '',
                        'due_amount' => $billing->due_amount,
                        'company_name' => siteUrlSettings('site_name'),
                        'company_mobile' => siteUrlSettings('site_phone'),
                    ];

                    try {
                        $response = app(SMSController::class)->collectionDeleteSMS($data);
                        if ($response && $response->isSuccessful()) {
                            flash()->success($response->getMessage());
                        } else {
                            flash()->error($response ? $response->getMessage() : 'Failed to send SMS notification.');
                        }
                    } catch (\Exception $smsEx) {
                        \Log::error('Collection Delete SMS failed: '.$smsEx->getMessage());
                        flash()->error('Collection Delete SMS failed: '.$smsEx->getMessage());
                    }
                }

                $this->info_data = [];
            } catch (\Exception $e) {
                DB::rollBack();
                flash()->error('Error deleting collection: '.$e->getMessage());
            }
        } else {
            flash()->error('Collection not found.');
        }
        $this->info_data = [];
    }

    public function render()
    {
        return view('livewire.collection-edit')->layout('layouts.app');
    }
}
