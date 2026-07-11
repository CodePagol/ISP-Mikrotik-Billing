<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CustomerReview;

class ManageReviews extends Component
{
    use WithPagination;

    public string $search = '';
    public string $ratingFilter = 'all';
    public string $siteFilter = 'all';

    // Edit Review variables
    public ?int $editingReviewId = null;
    public string $editingComment = '';
    public int $editingRating = 5;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRatingFilter(): void { $this->resetPage(); }
    public function updatingSiteFilter(): void { $this->resetPage(); }

    public function toggleShowOnSite(int $id)
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $review = CustomerReview::findOrFail($id);
        $review->update([
            'show_on_site' => !$review->show_on_site
        ]);

        session()->flash('success', 'Review site visibility updated.');
    }

    public function startEdit(int $id)
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $review = CustomerReview::findOrFail($id);
        $this->editingReviewId = $review->id;
        $this->editingComment = $review->comment;
        $this->editingRating = $review->rating;
    }

    public function updateReview()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $this->validate([
            'editingComment' => 'required|string|min:5|max:1000',
            'editingRating' => 'required|integer|min:1|max:5',
        ]);

        $review = CustomerReview::findOrFail($this->editingReviewId);
        $review->update([
            'comment' => $this->editingComment,
            'rating' => $this->editingRating,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Review updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingReviewId = null;
        $this->editingComment = '';
        $this->editingRating = 5;
    }

    public function deleteReview(int $id)
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        CustomerReview::findOrFail($id)->delete();
        session()->flash('success', 'Review deleted successfully.');
    }

    public function render()
    {
        if (! hasAccess(['Super Admin'], ['all-customer'])) {
            abort(403, 'Unauthorized action.');
        }

        $query = CustomerReview::with(['pppUser.customer'])->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('comment', 'like', '%' . $this->search . '%')
                  ->orWhereHas('pppUser', function ($qu) {
                      $qu->where('username', 'like', '%' . $this->search . '%')
                        ->orWhereHas('customer', function ($qc) {
                            $qc->where('customer_name', 'like', '%' . $this->search . '%');
                        });
                  });
            });
        }

        if ($this->ratingFilter !== 'all') {
            $query->where('rating', $this->ratingFilter);
        }

        if ($this->siteFilter !== 'all') {
            $query->where('show_on_site', $this->siteFilter === 'visible');
        }

        $reviews = $query->paginate(15);

        return view('livewire.admin.manage-reviews', compact('reviews'))
            ->layout('layouts.app');
    }
}
