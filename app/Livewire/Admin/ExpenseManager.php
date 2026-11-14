<?php

namespace App\Livewire\Admin;

use App\Models\IspExpense;
use App\Models\Reseller;
use App\Models\User;
use App\Services\ResellerWalletService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ExpenseManager extends Component
{
    use WithPagination;

    // Form fields
    public string $category     = 'item_purchase';
    public string $title        = '';
    public string $description  = '';
    public string $amount       = '';
    public string $expense_date = '';
    public string $reference_no = '';

    // Employee linking (for employee_salary category)
    public ?int $linkedUserId    = null;
    public string $userSearch    = '';
    public Collection $userSuggestions;

    // Reseller linking (for reseller_payout category)
    public ?int $linkedResellerId = null;

    // Edit state
    public ?int $editId = null;
    public ?IspExpense $printExpense = null;

    // Filters
    public string $filterCategory = '';
    public string $filterMonth    = '';
    public string $filterYear     = '';

    public bool $showModal = false;

    // Direct Inventory Integration when creating Expense
    public bool $autoTrackInventory = true;
    public ?int $invCategoryId = null;
    public string $invTrackingType = 'serialized';
    public float $invQuantity = 1;
    public string $invBrand = '';
    public string $invModel = '';
    public string $invSerials = '';
    public string $invSupplier = '';
    public int $invWarrantyMonths = 0;
    public string $invLocation = 'Main Store';

    // Inventory Lifecycle Modal
    public bool $showExpenseInventoryModal = false;
    public ?IspExpense $inventoryModalExpense = null;
    public Collection $expenseInventoryItems;

    // Track Inventory from Expense Modal
    public bool $showTrackModal = false;
    public ?int $trackExpenseId = null;
    public ?int $trackCategoryId = null;
    public string $trackTrackingType = 'serialized';
    public float $trackQuantity = 1;
    public string $trackSerialNumbers = '';
    public string $trackBrand = '';
    public string $trackModel = '';
    public string $trackSupplier = '';
    public int $trackWarrantyMonths = 0;

    public function boot(): void
    {
        $this->userSuggestions = collect();
        $this->expenseInventoryItems = collect();
    }

    public function mount(): void
    {
        $this->expense_date  = now()->format('Y-m-d');
        $this->filterMonth   = (string) now()->month;
        $this->filterYear    = (string) now()->year;
        $this->userSuggestions = collect();
    }

    public function openCreate(): void
    {
        $this->reset(['editId', 'category', 'title', 'description', 'amount', 'reference_no',
                      'linkedUserId', 'userSearch', 'linkedResellerId']);
        $this->category        = 'item_purchase';
        $this->expense_date    = now()->format('Y-m-d');
        $this->userSuggestions = collect();

        $firstCat = class_exists(\Plugins\InventoryManager\Models\InventoryCategory::class)
            ? \Plugins\InventoryManager\Models\InventoryCategory::first()
            : null;
        $this->invCategoryId     = $firstCat?->id;
        $this->invTrackingType   = $firstCat?->tracking_type ?? 'serialized';
        $this->autoTrackInventory = true;
        $this->invQuantity       = 1;
        $this->invBrand          = '';
        $this->invModel          = '';
        $this->invSerials        = '';
        $this->invSupplier       = '';
        $this->invWarrantyMonths = 0;
        $this->invLocation       = 'Main Store';

        $this->showModal       = true;
    }

    public function updatedInvCategoryId($val): void
    {
        if (class_exists(\Plugins\InventoryManager\Models\InventoryCategory::class)) {
            $cat = \Plugins\InventoryManager\Models\InventoryCategory::find($val);
            if ($cat) {
                $this->invTrackingType = $cat->tracking_type;
                if ($this->invTrackingType === 'serialized' && empty($this->invSerials)) {
                    $this->invQuantity = 1;
                }
            }
        }
    }

    public function updatedInvSerials(): void
    {
        $lines = array_filter(array_map('trim', explode("\n", (string) $this->invSerials)));
        if (!empty($lines)) {
            $this->invQuantity = count($lines);
        }
    }

    public function openEdit(int $id): void
    {
        $expense = IspExpense::with(['linkedUser', 'linkedReseller'])->findOrFail($id);
        $this->editId          = $expense->id;
        $this->category        = $expense->category;
        $this->title           = $expense->title;
        $this->description     = $expense->description ?? '';
        $this->amount          = (string) $expense->amount;
        $this->expense_date    = $expense->expense_date->format('Y-m-d');
        $this->reference_no    = $expense->reference_no ?? '';
        $this->linkedUserId    = $expense->linked_user_id;
        $this->linkedResellerId = $expense->linked_reseller_id;
        $this->userSearch      = $expense->linkedUser?->name ?? '';
        $this->userSuggestions = collect();
        $this->showModal       = true;
    }

    /** Live-search users as the admin types in the employee name field */
    public function updatedUserSearch(string $value): void
    {
        if (strlen(trim($value)) < 2) {
            $this->userSuggestions = collect();
            return;
        }

        $this->userSuggestions = User::whereDoesntHave('reseller')
            ->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%")
                  ->orWhere('mobile', 'like', "%{$value}%");
            })
            ->limit(8)
            ->get(['id', 'name', 'email', 'mobile']);
    }

    /** Called when admin picks a user from the suggestion list */
    public function selectUser(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $this->linkedUserId    = $user->id;
            $this->userSearch      = $user->name;
            $this->title           = "Salary — {$user->name}";
        }
        $this->userSuggestions = collect();
    }

    /** Clear the linked user selection */
    public function clearLinkedUser(): void
    {
        $this->linkedUserId    = null;
        $this->userSearch      = '';
        $this->userSuggestions = collect();
    }

    public function save(): void
    {
        abortIfNoAccess(['Super Admin', 'Accountant'], ['manage-expenses']);

        $this->validate([
            'category'     => 'required|in:item_purchase,raw_bill,employee_salary,reseller_payout,miscellaneous',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:100',
        ]);

        // Extra validation for reseller_payout
        if ($this->category === 'reseller_payout') {
            $this->validate(['linkedResellerId' => 'required|exists:resellers,id'],
                ['linkedResellerId.required' => 'Please select a reseller for Commission Payout.']);
        }

        $refNo = $this->reference_no ?: null;
        if (!$refNo) {
            $latest = IspExpense::where('reference_no', 'like', 'EXP-%')->latest('id')->first();
            if ($latest && preg_match('/^EXP-(\d+)$/', $latest->reference_no, $matches)) {
                $nextNum = (int)$matches[1] + 1;
            } else {
                $nextNum = 100001;
            }
            $refNo = 'EXP-' . $nextNum;
        }

        $data = [
            'category'           => $this->category,
            'title'              => $this->title,
            'description'        => $this->description ?: null,
            'amount'             => $this->amount,
            'expense_date'       => $this->expense_date,
            'reference_no'       => $refNo,
            'added_by'           => auth()->id(),
            'linked_user_id'     => $this->category === 'employee_salary' ? $this->linkedUserId : null,
            'linked_reseller_id' => $this->category === 'reseller_payout' ? $this->linkedResellerId : null,
        ];

        try {
            DB::transaction(function () use ($data, $refNo) {
                if ($this->editId) {
                    $expense = IspExpense::findOrFail($this->editId);
                    $expense->update($data);
                    flash()->success('Expense updated successfully.');
                } else {
                    $expense = IspExpense::create($data);
                    flash()->success('Expense added successfully.');

                    // If this is a Reseller Commission Payout → debit their wallet
                    if ($this->category === 'reseller_payout' && $this->linkedResellerId) {
                        $reseller = Reseller::findOrFail($this->linkedResellerId);
                        $walletService = new ResellerWalletService();
                        $walletService->debit(
                            $reseller,
                            (float) $this->amount,
                            "Commission Payout: {$this->title} (Ref: {$refNo})",
                            'isp_expense',
                            $expense->id
                        );
                    }

                    // Direct Inventory Stock Tracking
                    if ($this->category === 'item_purchase' && $this->autoTrackInventory && $this->invCategoryId && class_exists(\Plugins\InventoryManager\Models\InventoryItem::class)) {
                        $serials = [];
                        if ($this->invTrackingType === 'serialized' && !empty(trim($this->invSerials))) {
                            $serials = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', (string) $this->invSerials))));
                        }

                        $qty = !empty($serials) ? count($serials) : max(1, (float) $this->invQuantity);
                        $unitPrice = $qty > 0 ? (float) $this->amount / $qty : (float) $this->amount;
                        $batchId = 'BATCH-' . strtoupper(\Illuminate\Support\Str::random(8));

                        if ($this->invTrackingType === 'serialized') {
                            if (!empty($serials)) {
                                foreach ($serials as $sn) {
                                    $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                                        'category_id'           => $this->invCategoryId,
                                        'batch_id'              => $batchId,
                                        'expense_id'            => $expense->id,
                                        'title'                 => $this->title,
                                        'brand'                 => $this->invBrand ?: null,
                                        'model_no'              => $this->invModel ?: null,
                                        'tracking_type'         => 'serialized',
                                        'serial_number'         => $sn,
                                        'quantity'              => 1,
                                        'purchase_price'        => $unitPrice,
                                        'purchase_date'         => $this->expense_date,
                                        'supplier'              => $this->invSupplier ?: null,
                                        'warranty_months'       => $this->invWarrantyMonths,
                                        'status'                => 'in_stock',
                                        'current_location_type' => 'warehouse',
                                        'current_location_name' => $this->invLocation ?: 'Main Store',
                                        'created_by'            => auth()->id(),
                                    ]);

                                    \Plugins\InventoryManager\Models\InventoryStockLog::create([
                                        'item_id'         => $item->id,
                                        'type'            => 'purchase',
                                        'quantity_change' => 1,
                                        'balance_after'   => 1,
                                        'reference_no'    => $refNo,
                                        'note'            => "Added to inventory from Expense Ref: {$refNo}",
                                        'user_id'         => auth()->id(),
                                    ]);
                                }
                            } else {
                                for ($i = 1; $i <= (int) $qty; $i++) {
                                    $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                                        'category_id'           => $this->invCategoryId,
                                        'batch_id'              => $batchId,
                                        'expense_id'            => $expense->id,
                                        'title'                 => $this->title,
                                        'brand'                 => $this->invBrand ?: null,
                                        'model_no'              => $this->invModel ?: null,
                                        'tracking_type'         => 'serialized',
                                        'serial_number'         => null,
                                        'quantity'              => 1,
                                        'purchase_price'        => $unitPrice,
                                        'purchase_date'         => $this->expense_date,
                                        'supplier'              => $this->invSupplier ?: null,
                                        'warranty_months'       => $this->invWarrantyMonths,
                                        'status'                => 'in_stock',
                                        'current_location_type' => 'warehouse',
                                        'current_location_name' => $this->invLocation ?: 'Main Store',
                                        'created_by'            => auth()->id(),
                                    ]);

                                    \Plugins\InventoryManager\Models\InventoryStockLog::create([
                                        'item_id'         => $item->id,
                                        'type'            => 'purchase',
                                        'quantity_change' => 1,
                                        'balance_after'   => 1,
                                        'reference_no'    => $refNo,
                                        'note'            => "Added to inventory from Expense Ref: {$refNo}",
                                        'user_id'         => auth()->id(),
                                    ]);
                                }
                            }
                        } else {
                            $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                                'category_id'           => $this->invCategoryId,
                                'batch_id'              => $batchId,
                                'expense_id'            => $expense->id,
                                'title'                 => $this->title,
                                'brand'                 => $this->invBrand ?: null,
                                'model_no'              => $this->invModel ?: null,
                                'tracking_type'         => 'bulk',
                                'quantity'              => $qty,
                                'purchase_price'        => $unitPrice,
                                'purchase_date'         => $this->expense_date,
                                'supplier'              => $this->invSupplier ?: null,
                                'warranty_months'       => $this->invWarrantyMonths,
                                'status'                => 'in_stock',
                                'current_location_type' => 'warehouse',
                                'current_location_name' => $this->invLocation ?: 'Main Store',
                                'created_by'            => auth()->id(),
                            ]);

                            \Plugins\InventoryManager\Models\InventoryStockLog::create([
                                'item_id'         => $item->id,
                                'type'            => 'purchase',
                                'quantity_change' => $qty,
                                'balance_after'   => $qty,
                                'reference_no'    => $refNo,
                                'note'            => "Added to inventory from Expense Ref: {$refNo}",
                                'user_id'         => auth()->id(),
                            ]);
                        }
                    }
                }
            });

            $this->showModal = false;
            $this->reset(['editId', 'category', 'title', 'description', 'amount', 'reference_no',
                          'linkedUserId', 'userSearch', 'linkedResellerId']);
            $this->userSuggestions = collect();
            $this->resetPage();
        } catch (\Exception $e) {
            flash()->error($e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        abortIfNoAccess(['Super Admin'], []);

        $expense = IspExpense::with('inventoryItems')->findOrFail($id);

        $activeDeployed = $expense->inventoryItems()->where('status', 'deployed')->count();
        if ($activeDeployed > 0) {
            flash()->error(__("Cannot delete this expense because :count linked inventory item(s) are currently deployed to clients.", ['count' => $activeDeployed]));
            return;
        }

        // Clean up in-stock inventory items belonging to this expense
        $inStock = $expense->inventoryItems()->where('status', 'in_stock')->get();
        foreach ($inStock as $item) {
            $item->stockLogs()->delete();
            $item->delete();
        }

        $expense->delete();
        flash()->success(__('Expense and linked in-stock inventory items deleted successfully.'));
    }

    public function triggerPrint(int $id): void
    {
        $this->printExpense = IspExpense::with(['addedBy', 'linkedUser', 'linkedReseller.user', 'inventoryItems.category'])->findOrFail($id);
    }

    public function closePrint(): void
    {
        $this->printExpense = null;
    }

    public function viewExpenseInventory(int $id): void
    {
        $this->inventoryModalExpense = IspExpense::with([
            'inventoryItems.category',
            'inventoryItems.customer',
            'inventoryItems.assignedUser',
            'inventoryItems.assignments.customer',
            'inventoryItems.assignments.technician',
        ])->findOrFail($id);

        $this->expenseInventoryItems = $this->inventoryModalExpense->inventoryItems;
        $this->showExpenseInventoryModal = true;
    }

    public function closeExpenseInventory(): void
    {
        $this->showExpenseInventoryModal = false;
        $this->inventoryModalExpense = null;
        $this->expenseInventoryItems = collect();
    }

    public function openTrackInventory(int $id): void
    {
        $expense = IspExpense::findOrFail($id);
        $this->trackExpenseId = $expense->id;
        $firstCat = \Plugins\InventoryManager\Models\InventoryCategory::first();
        $this->trackCategoryId = $firstCat?->id;
        $this->trackTrackingType = $firstCat?->tracking_type ?? 'serialized';
        $this->trackQuantity = 1;
        $this->trackSerialNumbers = '';
        $this->trackBrand = '';
        $this->trackModel = '';
        $this->trackSupplier = '';
        $this->trackWarrantyMonths = 0;
        $this->showTrackModal = true;
    }

    public function closeTrackModal(): void
    {
        $this->showTrackModal = false;
        $this->trackExpenseId = null;
    }

    public function submitTrackInventory(): void
    {
        abortIfNoAccess(['Super Admin', 'Accountant'], ['manage-expenses']);

        $expense = IspExpense::findOrFail($this->trackExpenseId);

        $this->validate([
            'trackCategoryId'   => 'required|exists:inventory_categories,id',
            'trackTrackingType' => 'required|in:serialized,bulk',
            'trackQuantity'     => 'required|numeric|min:1',
        ]);

        $serials = [];
        if ($this->trackTrackingType === 'serialized') {
            if (!empty(trim($this->trackSerialNumbers))) {
                $serials = array_filter(array_map('trim', explode("\n", $this->trackSerialNumbers)));
            }
            $qty = !empty($serials) ? count($serials) : max(1, (int) $this->trackQuantity);
        } else {
            $qty = (float) $this->trackQuantity;
        }

        $unitPrice = $qty > 0 ? (float) $expense->amount / $qty : (float) $expense->amount;
        $batchId = 'BATCH-' . strtoupper(\Illuminate\Support\Str::random(8));

        DB::transaction(function () use ($expense, $qty, $unitPrice, $batchId, $serials) {
            if ($this->trackTrackingType === 'serialized') {
                if (!empty($serials)) {
                    foreach ($serials as $sn) {
                        $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                            'category_id'           => $this->trackCategoryId,
                            'batch_id'              => $batchId,
                            'expense_id'            => $expense->id,
                            'title'                 => $expense->title,
                            'brand'                 => $this->trackBrand ?: null,
                            'model_no'              => $this->trackModel ?: null,
                            'tracking_type'         => 'serialized',
                            'serial_number'         => $sn,
                            'quantity'              => 1,
                            'purchase_price'        => $unitPrice,
                            'purchase_date'         => $expense->expense_date,
                            'supplier'              => $this->trackSupplier ?: null,
                            'warranty_months'       => $this->trackWarrantyMonths,
                            'status'                => 'in_stock',
                            'current_location_type' => 'warehouse',
                            'current_location_name' => 'Main Store',
                            'created_by'            => auth()->id(),
                        ]);

                        \Plugins\InventoryManager\Models\InventoryStockLog::create([
                            'item_id'         => $item->id,
                            'type'            => 'purchase',
                            'quantity_change' => 1,
                            'balance_after'   => 1,
                            'reference_no'    => $expense->reference_no,
                            'note'            => "Added to inventory from Expense Ref: {$expense->reference_no}",
                            'user_id'         => auth()->id(),
                        ]);
                    }
                } else {
                    for ($i = 1; $i <= $qty; $i++) {
                        $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                            'category_id'           => $this->trackCategoryId,
                            'batch_id'              => $batchId,
                            'expense_id'            => $expense->id,
                            'title'                 => $expense->title,
                            'brand'                 => $this->trackBrand ?: null,
                            'model_no'              => $this->trackModel ?: null,
                            'tracking_type'         => 'serialized',
                            'serial_number'         => null,
                            'quantity'              => 1,
                            'purchase_price'        => $unitPrice,
                            'purchase_date'         => $expense->expense_date,
                            'supplier'              => $this->trackSupplier ?: null,
                            'warranty_months'       => $this->trackWarrantyMonths,
                            'status'                => 'in_stock',
                            'current_location_type' => 'warehouse',
                            'current_location_name' => 'Main Store',
                            'created_by'            => auth()->id(),
                        ]);

                        \Plugins\InventoryManager\Models\InventoryStockLog::create([
                            'item_id'         => $item->id,
                            'type'            => 'purchase',
                            'quantity_change' => 1,
                            'balance_after'   => 1,
                            'reference_no'    => $expense->reference_no,
                            'note'            => "Added to inventory from Expense Ref: {$expense->reference_no}",
                            'user_id'         => auth()->id(),
                        ]);
                    }
                }
            } else {
                $item = \Plugins\InventoryManager\Models\InventoryItem::create([
                    'category_id'           => $this->trackCategoryId,
                    'batch_id'              => $batchId,
                    'expense_id'            => $expense->id,
                    'title'                 => $expense->title,
                    'brand'                 => $this->trackBrand ?: null,
                    'model_no'              => $this->trackModel ?: null,
                    'tracking_type'         => 'bulk',
                    'quantity'              => $qty,
                    'purchase_price'        => $unitPrice,
                    'purchase_date'         => $expense->expense_date,
                    'supplier'              => $this->trackSupplier ?: null,
                    'warranty_months'       => $this->trackWarrantyMonths,
                    'status'                => 'in_stock',
                    'current_location_type' => 'warehouse',
                    'current_location_name' => 'Main Store',
                    'created_by'            => auth()->id(),
                ]);

                \Plugins\InventoryManager\Models\InventoryStockLog::create([
                    'item_id'         => $item->id,
                    'type'            => 'purchase',
                    'quantity_change' => $qty,
                    'balance_after'   => $qty,
                    'reference_no'    => $expense->reference_no,
                    'note'            => "Added {$qty} bulk units from Expense Ref: {$expense->reference_no}",
                    'user_id'         => auth()->id(),
                ]);
            }
        });

        flash()->success("Successfully linked {$qty} item(s) to Inventory Stock!");
        $this->showTrackModal = false;
        $this->trackExpenseId = null;
    }

    public function updatedFilterCategory(): void { $this->resetPage(); }
    public function updatedFilterMonth(): void    { $this->resetPage(); }
    public function updatedFilterYear(): void     { $this->resetPage(); }

    /** When category changes, clear the linked fields */
    public function updatedCategory(): void
    {
        $this->linkedUserId      = null;
        $this->linkedResellerId  = null;
        $this->userSearch        = '';
        $this->userSuggestions   = collect();
    }

    public function render()
    {
        $query = IspExpense::with(['addedBy', 'linkedUser', 'linkedReseller.user', 'inventoryItems.category', 'inventoryItems.customer'])
                           ->orderBy('expense_date', 'desc');

        if ($this->filterCategory) {
            $query->byCategory($this->filterCategory);
        }
        if ($this->filterMonth && $this->filterYear) {
            $query->byMonth((int) $this->filterMonth, (int) $this->filterYear);
        } elseif ($this->filterYear) {
            $query->whereYear('expense_date', $this->filterYear);
        }

        $expenses = $query->paginate(20);

        // Totals for current filter
        $totalQuery = IspExpense::query();
        if ($this->filterCategory) {
            $totalQuery->byCategory($this->filterCategory);
        }
        if ($this->filterMonth && $this->filterYear) {
            $totalQuery->byMonth((int) $this->filterMonth, (int) $this->filterYear);
        } elseif ($this->filterYear) {
            $totalQuery->whereYear('expense_date', $this->filterYear);
        }

        $totals = $totalQuery
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $grandTotal = $totals->sum();

        $categories = IspExpense::$categories;
        $years      = range(now()->year, now()->year - 4);
        $months     = [
            1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
            7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December',
        ];

        // Resellers for payout dropdown
        $resellers = Reseller::with('user')->where('status', 'active')->get();
        $inventoryCategories = class_exists(\Plugins\InventoryManager\Models\InventoryCategory::class)
            ? \Plugins\InventoryManager\Models\InventoryCategory::all()
            : collect();

        return view('livewire.admin.expense-manager', compact(
            'expenses', 'totals', 'grandTotal', 'categories', 'years', 'months', 'resellers', 'inventoryCategories'
        ))->layout('layouts.app');
    }
}
