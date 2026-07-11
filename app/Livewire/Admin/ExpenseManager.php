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

    public function boot(): void
    {
        $this->userSuggestions = collect();
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
        $this->showModal       = true;
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
        IspExpense::findOrFail($id)->delete();
        flash()->success('Expense deleted.');
    }

    public function triggerPrint(int $id): void
    {
        $this->printExpense = IspExpense::with(['addedBy', 'linkedUser', 'linkedReseller.user'])->findOrFail($id);
    }

    public function closePrint(): void
    {
        $this->printExpense = null;
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
        $query = IspExpense::with(['addedBy', 'linkedUser', 'linkedReseller.user'])
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

        return view('livewire.admin.expense-manager', compact(
            'expenses', 'totals', 'grandTotal', 'categories', 'years', 'months', 'resellers'
        ))->layout('layouts.app');
    }
}
