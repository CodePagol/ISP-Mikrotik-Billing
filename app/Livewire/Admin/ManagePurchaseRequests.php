<?php

namespace App\Livewire\Admin;

use App\Models\PackagePurchaseRequest;
use Livewire\Component;
use Livewire\WithPagination;

class ManagePurchaseRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public $showDetailModal = false;
    public $selectedRequestId = null;
    public $selectedRequestNotes = '';
    public $selectedRequestStatus = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function changeStatus($id, $status)
    {
        $request = PackagePurchaseRequest::findOrFail($id);
        $request->update(['status' => $status]);
        
        flash()->success("Request status updated to " . ucfirst($status) . ".");
    }

    public function openDetailModal($id)
    {
        $request = PackagePurchaseRequest::findOrFail($id);
        $this->selectedRequestId = $request->id;
        $this->selectedRequestNotes = $request->notes ?? '';
        $this->selectedRequestStatus = $request->status;
        $this->showDetailModal = true;
    }

    public function saveDetails()
    {
        $request = PackagePurchaseRequest::findOrFail($this->selectedRequestId);
        $request->update([
            'notes' => $this->selectedRequestNotes,
            'status' => $this->selectedRequestStatus,
        ]);

        $this->showDetailModal = false;
        flash()->success("Request details saved successfully.");
    }

    public function delete($id)
    {
        PackagePurchaseRequest::findOrFail($id)->delete();
        flash()->success("Request deleted successfully.");
    }

    public function render()
    {
        $query = PackagePurchaseRequest::latest();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('package_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $requests = $query->paginate(20);

        // Fetch counts for summary cards
        $counts = [
            'all' => PackagePurchaseRequest::count(),
            'pending' => PackagePurchaseRequest::where('status', 'pending')->count(),
            'contacted' => PackagePurchaseRequest::where('status', 'contacted')->count(),
            'completed' => PackagePurchaseRequest::where('status', 'completed')->count(),
        ];

        return view('livewire.admin.manage-purchase-requests', compact('requests', 'counts'))
            ->layout('layouts.app');
    }
}
