<?php

namespace App\Livewire;

use App\Models\AddressField;
use App\Models\CustomersAddress;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AddressSetup extends Component
{
    public $label;

    public $input_type;

    public $dropdown_list = [];

    public $dropdown_input;

    public $print_preview;

    public $required;

    public $complain_preview;

    public $addressFields;

    public $receiptOrders;

    public $addressFieldId; // Track the ID of the AddressField being edited

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['address-setup'])) {
            abort(403, 'Unauthorized action.');
        }

        // Initialize properties
        $this->reset();
        $this->dataRender();
    }

    protected function rules()
    {
        return [
            'label' => 'required|string|max:255|unique:address_fields,label,'.$this->addressFieldId,
            'input_type' => 'required|in:dropdown,text,textarea',
            'dropdown_list' => 'required_if:input_type,dropdown|array',
            'dropdown_input' => $this->shouldRequireTypeInput() ? 'required|string' : 'nullable',
        ];
    }

    public function shouldRequireTypeInput()
    {
        return $this->input_type === 'dropdown' && empty($this->dropdown_list);
    }

    public function addTypeToList()
    {
        if (! empty($this->dropdown_input)) {
            // Check if the input is already in the list
            if (! in_array($this->dropdown_input, $this->dropdown_list)) {
                $this->dropdown_list[] = $this->dropdown_input;
                $this->dropdown_input = ''; // Clear input after adding to list
            } else {
                throw ValidationException::withMessages([
                    'dropdown_input' => 'This type is already in the list.',
                ]);
            }
        }
    }

    public function isTypeInUse($type)
    {
        // Check if the type is being used in the customers_address table
        return CustomersAddress::where('input_type_dropdown', $type)->exists();
    }

    public function removeTypeFromList($index)
    {
        $type = $this->dropdown_list[$index];

        // Check if the type is in use before removing
        if ($this->isTypeInUse($type)) {
            flash()->error('This type is currently in use and cannot be deleted!');
        } else {
            unset($this->dropdown_list[$index]);
            $this->dropdown_list = array_values($this->dropdown_list); // Reindex the array
        }
    }

    // Create or update the address field
    public function submit()
    {
        $this->validate();
        try {
            AddressField::updateOrCreate(
                ['id' => $this->addressFieldId],
                [
                    'label' => $this->label,
                    'input_type' => $this->input_type,
                    // 'dropdown_list' => implode(',', $this->dropdown_list), // Save as comma-separated string
                    'dropdown_list' => json_encode($this->dropdown_list), // Save as comma-separated string
                    'print_preview' => $this->print_preview ? 1 : 0,
                    'required' => $this->required ? 1 : 0,
                    'complain_preview' => $this->complain_preview ? 1 : 0,
                ]
            );
            flash()->success('Data saved successfully!');
        } catch (\Exception $e) {
            flash()->error('Error saving data: '.$e->getMessage());
        }
        $this->reset();
    }

    // Edit the address field by loading its data into the form
    public function edit($id)
    {
        $addressField = AddressField::find($id);

        if ($addressField) {
            $this->addressFieldId = $id;
            $this->label = $addressField->label;
            $this->input_type = $addressField->input_type;
            $this->dropdown_list = json_decode($addressField->dropdown_list); // Clear the input field
            $this->print_preview = $addressField->print_preview == 1;
            $this->required = $addressField->required == 1;
            $this->complain_preview = $addressField->complain_preview == 1;
        }
    }

    // Delete an address field
    public function delete($id)
    {
        $addressField = AddressField::find($id);

        if ($addressField) {
            $addressField->delete();
            flash()->success('Address Field deleted successfully!');
        }
    }

    public function updateSortOrderAddress($reorder)
    {
        // Map the incoming data structure to reorder the directorates array
        $this->addressFields = collect($reorder)->map(function ($address) {
            // Match each address based on its "value" (ID)
            return collect($this->addressFields)->firstWhere('id', (int) $address['value']);
        })->toArray();
        flash()->addInfo('Address Field List Successfully Reordered. But Not Saved.');
    }

    public function saveSortOrderAddress()
    {
        foreach ($this->addressFields as $index => $field) {
            AddressField::where('id', $field['id'])->update(['order' => $index + 1]);
        }
        flash()->success('Address Fields order saved successfully!');
    }

    public function updateSortOrderReceipt($reorder)
    {
        // Map the incoming data structure to reorder the directorates array
        $this->receiptOrders = collect($reorder)->map(function ($receipt) {
            // Match each receipt based on its "value" (ID)
            return collect($this->receiptOrders)->firstWhere('id', (int) $receipt['value']);
        })->toArray();
        flash()->addInfo('Receipt Address Field List Successfully Reordered. But Not Saved.');
    }

    public function saveSortOrderReceipt()
    {
        foreach ($this->receiptOrders as $index => $field) {
            AddressField::where('id', $field['id'])->update(['receipt_order' => $index + 1]);
        }
        flash()->success('Receipt Address Fields order saved successfully!');
    }

    public function dataRender()
    {
        $this->addressFields = AddressField::orderBy('order', 'asc')->get();
        $this->receiptOrders = AddressField::orderBy('receipt_order', 'asc')->get();
    }

    public function render()
    {
        return view('livewire.address-setup')->layout('layouts.app');
    }
}
