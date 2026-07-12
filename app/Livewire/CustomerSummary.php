<?php

namespace App\Livewire;

use App\Models\CustomersInfo;
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

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['payment-collection-report'])) {
            abort(403, 'Unauthorized action.');
        }

        return true;
    }

    public function updatedCustomerList()
    {
        if ($this->customer_list) {
            // Fetch customers dynamically based on the search term
            $this->customers = CustomersInfo::search($this->customer_list)
                ->leftJoin('p_p_p_secrets', 'p_p_p_secrets.id', '=', 'customers_infos.ppp_user_id')
                ->with('customerAddress')
                ->select('customers_infos.id', 'customers_infos.customer_unique_id', 'customers_infos.customer_name', 'customers_infos.email', 'customers_infos.mobile', 'customers_infos.status', 'p_p_p_secrets.username as username')
                ->take(10)
                ->get();
        } else {
            $this->customers = [];
        }
        $this->info_data = [];
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

    public function selectCustomer($value)
    {
        $this->expire_date = '';
        $customer_id = decrypt($value);
        $this->customer_list = '';
        $this->customers = [];

        $this->info_data = CustomersInfo::where('customer_unique_id', $customer_id)
            ->with([
                'customerAddress',
                'billing',
                'official',
                'pppUser',
                'paymentSummary',
            ])
            ->first();

        $this->dispatch('dataTable');
    }

    public function render()
    {
        return view('livewire.customer-summary')->layout('layouts.app');
    }
}
