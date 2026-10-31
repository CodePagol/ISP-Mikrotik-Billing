<?php

namespace App\Livewire;

use App\Models\AddressField;
use App\Models\CustomersAddress;
use Illuminate\Support\Facades\DB;
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

    // Staging changes in memory during edit session (only applied on main Save)
    public $originalLabel = null;
    public $originalDropdownList = [];
    public $renamedOptions = []; // [original_name => new_name]
    public $mergedOptions = [];  // [deleted_original_name => target_name]

    // Inline option editing
    public $editingOptionIndex = null;
    public $editingOptionValue = '';

    // Merge & delete option modal
    public $mergeModalOpen = false;
    public $mergeFromOption = '';
    public $mergeToOption = '';
    public $mergeCustomerCount = 0;

    // Batch Find & Replace tool (Spelling correction tool)
    public $batchModalOpen = false;
    public $batchFieldLabel = '';
    public $batchOldValue = '';
    public $batchNewValue = '';
    public $batchMatchCount = null;

    public function mount()
    {
        if (! hasAccess(['Super Admin'], ['address-setup'])) {
            abort(403, 'Unauthorized action.');
        }

        // Initialize properties
        $this->cancelEdit();
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
            $input = trim($this->dropdown_input);
            // Check if the input is already in the list
            if (! in_array($input, $this->dropdown_list)) {
                $this->dropdown_list[] = $input;
                $this->dropdown_input = ''; // Clear input after adding to list
            } else {
                throw ValidationException::withMessages([
                    'dropdown_input' => 'This option is already in the list.',
                ]);
            }
        }
    }

    public function getOriginalNameForOption($type)
    {
        $orig = array_search($type, $this->renamedOptions);
        if ($orig !== false) {
            return $orig;
        }
        return $type;
    }

    public function isTypeInUse($type)
    {
        return $this->countTypeInUse($type) > 0;
    }

    public function countTypeInUse($type)
    {
        $dbName = $this->getOriginalNameForOption($type);
        $label = $this->originalLabel ?? ($this->addressFieldId ? AddressField::find($this->addressFieldId)?->label : $this->label);
        $query = CustomersAddress::where('input_type_dropdown', $dbName);
        if ($label) {
            $query->where('label_name', $label);
        }
        return $query->count();
    }

    public function removeTypeFromList($index)
    {
        if (!isset($this->dropdown_list[$index])) {
            return;
        }

        $type = $this->dropdown_list[$index];

        // Check if any customer is currently assigned this address option in database
        $count = $this->countTypeInUse($type);
        if ($count > 0) {
            // Open merge modal so user can choose another option to migrate customers to
            $this->mergeModalOpen = true;
            $this->mergeFromOption = $type;
            $this->mergeCustomerCount = $count;
            $this->mergeToOption = '';
            return;
        }

        // Clean up staged rename if this was a renamed option
        $origKey = array_search($type, $this->renamedOptions);
        if ($origKey !== false) {
            unset($this->renamedOptions[$origKey]);
        }

        unset($this->dropdown_list[$index]);
        $this->dropdown_list = array_values($this->dropdown_list);
    }

    public function closeMergeModal()
    {
        $this->mergeModalOpen = false;
        $this->mergeFromOption = '';
        $this->mergeToOption = '';
        $this->mergeCustomerCount = 0;
    }

    public function confirmMergeAndDelete()
    {
        if (empty($this->mergeToOption)) {
            flash()->error('Please select an option to migrate customers to.');
            return;
        }

        $from = $this->mergeFromOption;
        $to = $this->mergeToOption;

        if ($from === $to) {
            flash()->error('Source and target options cannot be the same.');
            return;
        }

        // Determine original database name for $from
        $originalFrom = $from;
        $origKey = array_search($from, $this->renamedOptions);
        if ($origKey !== false) {
            $originalFrom = $origKey;
            unset($this->renamedOptions[$origKey]);
        }

        // Stage merge in memory (will execute only on main Save)
        $this->mergedOptions[$originalFrom] = $to;

        // Remove from current dropdown list
        $this->dropdown_list = array_values(array_filter($this->dropdown_list, fn($item) => $item !== $from));

        $this->closeMergeModal();
        flash()->info("Option '{$from}' staged to merge into '{$to}'. Click Save below to apply changes.");
    }

    public function startEditOption($index)
    {
        if (isset($this->dropdown_list[$index])) {
            $this->editingOptionIndex = $index;
            $this->editingOptionValue = $this->dropdown_list[$index];
        }
    }

    public function cancelEditOption()
    {
        $this->editingOptionIndex = null;
        $this->editingOptionValue = '';
    }

    public function saveEditOption()
    {
        $newValue = trim($this->editingOptionValue);
        if (empty($newValue)) {
            flash()->error('Option name cannot be empty.');
            return;
        }

        $currentVal = $this->dropdown_list[$this->editingOptionIndex];

        if ($newValue === $currentVal) {
            $this->cancelEditOption();
            return;
        }

        // Check duplicate
        foreach ($this->dropdown_list as $idx => $opt) {
            if ($idx !== $this->editingOptionIndex && strcasecmp($opt, $newValue) === 0) {
                flash()->error("An option named '{$newValue}' already exists.");
                return;
            }
        }

        // Determine original database name to track staged renaming
        $originalName = null;
        $origKey = array_search($currentVal, $this->renamedOptions);
        if ($origKey !== false) {
            $originalName = $origKey;
        } elseif (in_array($currentVal, $this->originalDropdownList)) {
            $originalName = $currentVal;
        }

        if ($originalName) {
            if ($newValue === $originalName) {
                // Reverted back to original
                unset($this->renamedOptions[$originalName]);
            } else {
                $this->renamedOptions[$originalName] = $newValue;
            }
        }

        $this->dropdown_list[$this->editingOptionIndex] = $newValue;
        $this->cancelEditOption();
    }

    // Batch Find & Replace / Spellcheck Tool
    public function openBatchModal($label = null)
    {
        $this->batchModalOpen = true;
        $this->batchFieldLabel = $label ?? ($this->addressFields->first()?->label ?? '');
        $this->batchOldValue = '';
        $this->batchNewValue = '';
        $this->batchMatchCount = null;
        $this->checkBatchMatchCount();
    }

    public function closeBatchModal()
    {
        $this->batchModalOpen = false;
        $this->batchFieldLabel = '';
        $this->batchOldValue = '';
        $this->batchNewValue = '';
        $this->batchMatchCount = null;
    }

    public function updatedBatchFieldLabel()
    {
        $this->checkBatchMatchCount();
    }

    public function updatedBatchOldValue()
    {
        $this->checkBatchMatchCount();
    }

    public function checkBatchMatchCount()
    {
        if (!empty($this->batchFieldLabel) && !empty($this->batchOldValue)) {
            $field = AddressField::where('label', $this->batchFieldLabel)->first();
            if ($field) {
                $col = 'input_type_' . $field->input_type;
                $this->batchMatchCount = CustomersAddress::where('label_name', $this->batchFieldLabel)
                    ->where($col, trim($this->batchOldValue))
                    ->count();
                return;
            }
        }
        $this->batchMatchCount = null;
    }

    public function executeBatchReplace()
    {
        $this->validate([
            'batchFieldLabel' => 'required|string',
            'batchOldValue' => 'required|string',
            'batchNewValue' => 'required|string',
        ]);

        $field = AddressField::where('label', $this->batchFieldLabel)->first();
        if (!$field) {
            flash()->error('Selected address field not found.');
            return;
        }

        $col = 'input_type_' . $field->input_type;
        $oldVal = trim($this->batchOldValue);
        $newVal = trim($this->batchNewValue);

        $affected = CustomersAddress::where('label_name', $this->batchFieldLabel)
            ->where($col, $oldVal)
            ->update([$col => $newVal]);

        // If dropdown, also update dropdown_list in address_fields if old value was present
        if ($field->input_type === 'dropdown' && !empty($field->dropdown_list)) {
            $list = json_decode($field->dropdown_list, true) ?: [];
            $replaced = false;
            foreach ($list as $k => $v) {
                if ($v === $oldVal) {
                    $list[$k] = $newVal;
                    $replaced = true;
                }
            }
            if ($replaced) {
                $field->dropdown_list = json_encode(array_values(array_unique($list)));
                $field->save();
            }
        }

        flash()->success("Successfully updated {$affected} customer address(es) from '{$oldVal}' to '{$newVal}'!");
        $this->closeBatchModal();
        $this->dataRender();
    }

    // Create or update the address field (all changes to customers & field are committed here)
    public function submit()
    {
        $this->validate();
        try {
            DB::transaction(function () {
                $totalCustomersUpdated = 0;
                $currentLabel = $this->originalLabel ?? $this->label;

                // 1. If label changed, cascade update label_name in customers_addresses
                if ($this->originalLabel && $this->originalLabel !== $this->label) {
                    CustomersAddress::where('label_name', $this->originalLabel)->update(['label_name' => $this->label]);
                    $currentLabel = $this->label;
                }

                // 2. Process staged merged options (customers moved from deleted option to remaining option)
                foreach ($this->mergedOptions as $from => $to) {
                    $finalTo = $this->renamedOptions[$to] ?? $to;
                    $count = CustomersAddress::where('label_name', $currentLabel)
                        ->where('input_type_dropdown', $from)
                        ->update(['input_type_dropdown' => $finalTo]);
                    $totalCustomersUpdated += $count;
                }

                // 3. Process staged renamed options
                foreach ($this->renamedOptions as $orig => $new) {
                    if ($orig !== $new) {
                        $count = CustomersAddress::where('label_name', $currentLabel)
                            ->where('input_type_dropdown', $orig)
                            ->update(['input_type_dropdown' => $new]);
                        $totalCustomersUpdated += $count;
                    }
                }

                // 4. Save AddressField model
                AddressField::updateOrCreate(
                    ['id' => $this->addressFieldId],
                    [
                        'label' => $this->label,
                        'input_type' => $this->input_type,
                        'dropdown_list' => !empty($this->dropdown_list) ? json_encode(array_values($this->dropdown_list)) : null,
                        'print_preview' => $this->print_preview ? 1 : 0,
                        'required' => $this->required ? 1 : 0,
                        'complain_preview' => $this->complain_preview ? 1 : 0,
                    ]
                );

                if ($totalCustomersUpdated > 0) {
                    flash()->success("Address field saved successfully! Updated {$totalCustomersUpdated} customer address record(s).");
                } else {
                    flash()->success('Address field saved successfully!');
                }
            });

            $this->cancelEdit();
            $this->dataRender();
        } catch (\Exception $e) {
            flash()->error('Error saving data: '.$e->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->addressFieldId = null;
        $this->label = null;
        $this->originalLabel = null;
        $this->input_type = null;
        $this->dropdown_list = [];
        $this->originalDropdownList = [];
        $this->renamedOptions = [];
        $this->mergedOptions = [];
        $this->dropdown_input = null;
        $this->print_preview = false;
        $this->required = false;
        $this->complain_preview = false;
        $this->cancelEditOption();
        $this->closeMergeModal();
    }

    // Edit the address field by loading its data into the form
    public function edit($id)
    {
        $addressField = AddressField::find($id);

        if ($addressField) {
            $this->addressFieldId = $id;
            $this->label = $addressField->label;
            $this->originalLabel = $addressField->label;
            $this->input_type = $addressField->input_type;
            $decoded = json_decode($addressField->dropdown_list, true) ?: [];
            $this->dropdown_list = $decoded;
            $this->originalDropdownList = $decoded;
            $this->renamedOptions = [];
            $this->mergedOptions = [];
            $this->print_preview = $addressField->print_preview == 1;
            $this->required = $addressField->required == 1;
            $this->complain_preview = $addressField->complain_preview == 1;
            $this->cancelEditOption();
        }
    }

    // Delete an address field
    public function delete($id)
    {
        $addressField = AddressField::find($id);

        if ($addressField) {
            $inUseCount = CustomersAddress::where('label_name', $addressField->label)->count();
            if ($inUseCount > 0) {
                flash()->error("Cannot delete field '{$addressField->label}' because it is in use by {$inUseCount} customer(s).");
                return;
            }

            $addressField->delete();
            flash()->success('Address Field deleted successfully!');
            $this->dataRender();
        }
    }

    public function updateSortOrderAddress($reorder)
    {
        $this->addressFields = collect($reorder)->map(function ($address) {
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
        $this->receiptOrders = collect($reorder)->map(function ($receipt) {
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
