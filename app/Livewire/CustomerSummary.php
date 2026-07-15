<?php

namespace App\Livewire;

use App\Models\CustomersInfo;
use App\Models\CollectionSummary;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerSummary extends Component
{
    use WithPagination;

    public $user_list;

    public $customer_list = '';

    public $info_data = [];

    public $collectionSummary = [];

    public $highlightedIndex = 0;

    public $customers = [];

    public $limit = 10;

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['payment-collection-report'])) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    public function updatingCustomerList()
    {
        $this->limit = 10;
    }

    public function updatedCustomerList()
    {
        if ($this->customer_list) {
            // 1. Fetch active/soft-deleted customers matching the search term
            $activeCustomers = CustomersInfo::withTrashed()
                ->search($this->customer_list)
                ->leftJoin('p_p_p_secrets', 'p_p_p_secrets.id', '=', 'customers_infos.ppp_user_id')
                ->with('customerAddress')
                ->select('customers_infos.id', 'customers_infos.customer_unique_id', 'customers_infos.customer_name', 'customers_infos.email', 'customers_infos.mobile', 'customers_infos.status', 'p_p_p_secrets.username as username')
                ->get();

            // 2. Fetch deleted customer unique IDs (from collections or payments) matching the search term
            $activeUniqueIds = $activeCustomers->pluck('customer_unique_id')->toArray();
            
            $deletedUniqueIds = CollectionSummary::where('customer_collection_unique_id', 'like', '%'.$this->customer_list.'%')
                ->orWhere('transaction_id', 'like', '%'.$this->customer_list.'%')
                ->orWhere('invoice_no', 'like', '%'.$this->customer_list.'%')
                ->pluck('customer_collection_unique_id')
                ->merge(
                    \App\Models\PaymentSummary::where('customer_payment_unique_id', 'like', '%'.$this->customer_list.'%')
                        ->pluck('customer_payment_unique_id')
                )
                ->unique()
                ->diff($activeUniqueIds); // Exclude IDs that are already in active results

            // 3. Create mock customer models for deleted customer IDs
            $mockedCustomers = [];
            foreach ($deletedUniqueIds as $uniqueId) {
                $mock = new CustomersInfo();
                $mock->id = 'temp_' . $uniqueId;
                $mock->customer_unique_id = $uniqueId;
                $mock->customer_name = 'Deleted Customer (' . $uniqueId . ')';
                $mock->status = 'deleted';
                $mock->email = 'N/A';
                $mock->mobile = 'N/A';
                $mock->setRelation('customerAddress', collect());
                $mock->setRelation('pppUser', null);
                $mockedCustomers[] = $mock;
            }

            // 4. Merge active and mocked deleted customers
            $allCustomers = $activeCustomers->merge($mockedCustomers);

            // 5. Rank and Sort the merged list (exact match and prefix match first)
            $searchTerm = $this->customer_list;
            $this->customers = $allCustomers->sortBy(function ($customer) use ($searchTerm) {
                $uniqueId = $customer->customer_unique_id;
                $username = $customer->username ?? '';

                if (strcasecmp($uniqueId, $searchTerm) === 0) {
                    return 0; // Exact ID match
                }
                if (strcasecmp($username, $searchTerm) === 0) {
                    return 1; // Exact Username match
                }
                if (stripos($uniqueId, $searchTerm) === 0) {
                    return 2; // ID starts with
                }
                if (stripos($username, $searchTerm) === 0) {
                    return 3; // Username starts with
                }
                return 4; // Other matches
            })
            ->take($this->limit)
            ->map(function ($customer) {
                $obj = new \stdClass();
                $obj->id = $customer->id;
                $obj->customer_unique_id = $customer->customer_unique_id;
                $obj->customer_name = $customer->customer_name;
                $obj->email = $customer->email;
                $obj->mobile = $customer->mobile;
                $obj->status = $customer->status;
                $obj->username = $customer->username ?? ($customer->pppUser?->username ?? 'N/A');
                
                $addresses = [];
                if ($customer->customerAddress) {
                    foreach ($customer->customerAddress as $address) {
                        $addrObj = new \stdClass();
                        $addrObj->input_type_test = $address->input_type_test;
                        $addrObj->input_type_dropdown = $address->input_type_dropdown;
                        $addrObj->input_type_textarea = $address->input_type_textarea;
                        $addresses[] = $addrObj;
                    }
                }
                $obj->customerAddress = $addresses;
                return $obj;
            })
            ->values()
            ->toArray();
        } else {
            $this->customers = [];
        }
        $this->info_data = [];
        // Reset highlighted index whenever the list updates
        $this->highlightedIndex = 0;
    }

    public function loadMore()
    {
        $this->limit += 10;
        $this->updatedCustomerList();
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

    public function selectCustomer($value)
    {
        $this->expire_date = '';
        $customer_id = decrypt($value);
        $this->customer_list = '';
        $this->customers = [];

        $customer = CustomersInfo::withTrashed()
            ->where('customer_unique_id', $customer_id)
            ->with([
                'customerAddress',
                'billing',
                'official',
                'pppUser',
                'paymentSummary',
            ])
            ->first();

        if ($customer) {
            $this->info_data = $this->transformCustomerToStdClass($customer);
        } else {
            // Check if there are payment or collection records for this deleted customer
            $hasRecords = CollectionSummary::where('customer_collection_unique_id', $customer_id)->exists() ||
                         \App\Models\PaymentSummary::where('customer_payment_unique_id', $customer_id)->exists();
            if ($hasRecords) {
                $mock = new \stdClass();
                $mock->customer_unique_id = $customer_id;
                $mock->customer_name = 'Deleted Customer';
                $mock->status = 'deleted';
                $mock->email = 'N/A';
                $mock->mobile = 'N/A';
                
                $mock->customerAddress = [];
                
                $pppUser = new \stdClass();
                $pppUser->username = 'N/A';
                $mock->pppUser = $pppUser;
                
                $mockBilling = new \stdClass();
                $mockBilling->billing_type = 'N/A';
                $mockBilling->auto_disable_date = now();
                $mock->billing = $mockBilling;
                
                // Load payment summaries from DB
                $payments = [];
                $paymentSummaries = \App\Models\PaymentSummary::where('customer_payment_unique_id', $customer_id)->get();
                foreach ($paymentSummaries as $payment) {
                    $p = new \stdClass();
                    $p->summary_date = $payment->summary_date;
                    $p->monthly_rent = $payment->monthly_rent;
                    $p->discount = $payment->discount;
                    $p->advance = $payment->advance;
                    $p->additional_charge = $payment->additional_charge;
                    $p->vat = $payment->vat;
                    $p->previous_due = $payment->previous_due;
                    $payments[] = $p;
                }
                $mock->paymentSummary = collect($payments);

                // Load collection summaries from DB
                $collections = [];
                $collectionSummaries = CollectionSummary::where('customer_collection_unique_id', $customer_id)->get();
                foreach ($collectionSummaries as $collection) {
                    $col = [];
                    $col['collection_date'] = $collection->collection_date;
                    $col['collected_by'] = $collection->collected_by;
                    $col['collection_amount'] = $collection->collection_amount;
                    $collections[] = $col;
                }
                $mock->collectionSummary = collect($collections);

                $this->info_data = $mock;
            } else {
                $this->info_data = [];
            }
        }

        $this->dispatch('dataTable');
    }

    private function transformCustomerToStdClass($customer)
    {
        $obj = new \stdClass();
        $obj->customer_unique_id = $customer->customer_unique_id;
        $obj->customer_name = $customer->customer_name;
        $obj->status = $customer->status;
        
        // billing
        $billing = new \stdClass();
        $billing->billing_type = $customer->billing?->billing_type ?? 'N/A';
        $billing->auto_disable_date = $customer->billing?->auto_disable_date ?? null;
        $obj->billing = $billing;

        // pppUser
        $pppUser = new \stdClass();
        $pppUser->username = $customer->pppUser?->username ?? 'N/A';
        $obj->pppUser = $pppUser;

        // customerAddress
        $addresses = [];
        if ($customer->customerAddress) {
            foreach ($customer->customerAddress as $address) {
                $addr = new \stdClass();
                $addr->input_type_dropdown = $address->input_type_dropdown;
                $addr->input_type_test = $address->input_type_test;
                $addr->input_type_textarea = $address->input_type_textarea;
                $addresses[] = $addr;
            }
        }
        $obj->customerAddress = $addresses;

        // paymentSummary
        $payments = [];
        if ($customer->paymentSummary) {
            foreach ($customer->paymentSummary as $payment) {
                $p = new \stdClass();
                $p->summary_date = $payment->summary_date;
                $p->monthly_rent = $payment->monthly_rent;
                $p->discount = $payment->discount;
                $p->advance = $payment->advance;
                $p->additional_charge = $payment->additional_charge;
                $p->vat = $payment->vat;
                $p->previous_due = $payment->previous_due;
                $payments[] = $p;
            }
        }
        $obj->paymentSummary = collect($payments);

        // collectionSummary
        $collections = [];
        $collectionList = $customer->collectionSummary ?? CollectionSummary::where('customer_collection_unique_id', $customer->customer_unique_id)->get();
        foreach ($collectionList as $collection) {
            $col = [];
            $col['collection_date'] = $collection->collection_date;
            $col['collected_by'] = $collection->collected_by;
            $col['collection_amount'] = $collection->collection_amount;
            $collections[] = $col;
        }
        $obj->collectionSummary = collect($collections);

        return $obj;
    }

    public function render()
    {
        return view('livewire.customer-summary')->layout('layouts.app');
    }
}
