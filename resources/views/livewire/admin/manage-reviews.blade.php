<div>
    <div class="container-fluid py-4">

        {{-- Flash Messages --}}
        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0" style="color:#1a1f36;">
                    <i class="bi bi-chat-heart me-2 text-success"></i>{{ __('Manage Customer Reviews') }}
                </h4>
                <p class="text-muted small mb-0">{{ __('Audit customer testimonials, edit comments, and control website homepage visibility') }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body py-3">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Search Reviews') }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input wire:model.live.debounce.400ms="search" type="text" 
                                placeholder="{{ __('Search by customer name, username, comments...') }}" 
                                class="form-control form-control-sm rounded-end-3 border-start-0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Rating Stars') }}</label>
                        <select wire:model.live="ratingFilter" class="form-select form-select-sm rounded-3">
                            <option value="all">{{ __('All Ratings') }}</option>
                            <option value="5">{{ __('5 Stars') }}</option>
                            <option value="4">{{ __('4 Stars') }}</option>
                            <option value="3">{{ __('3 Stars') }}</option>
                            <option value="2">{{ __('2 Stars') }}</option>
                            <option value="1">{{ __('1 Star') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">{{ __('Main Site Status') }}</label>
                        <select wire:model.live="siteFilter" class="form-select form-select-sm rounded-3">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="visible">{{ __('Visible on Site') }}</option>
                            <option value="hidden">{{ __('Hidden from Site') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reviews Table --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background:#f8f9fc;">
                            <tr class="small text-muted fw-semibold">
                                <th class="ps-4 py-3">{{ __('Customer') }}</th>
                                <th>{{ __('Rating') }}</th>
                                <th>{{ __('Comment') }}</th>
                                <th>{{ __('Edits (Max 2)') }}</th>
                                <th>{{ __('Show on Site') }}</th>
                                <th>{{ __('Submitted') }}</th>
                                <th class="text-end pe-4" style="width: 150px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                            <tr wire:key="admin-review-{{ $review->id }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $review->pppUser?->customer?->customer_name ?? 'N/A' }}</div>
                                    <div class="text-muted small">A/C: {{ $review->pppUser?->customer?->customer_unique_id ?? 'N/A' }} ({{ $review->pppUser?->username }})</div>
                                </td>
                                <td>
                                    <div class="text-amber-400 d-flex gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $review->rating >= $i ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td>
                                    <div class="small text-dark" style="max-width: 320px; white-space: normal;">
                                        {{ $review->comment }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $review->edit_count >= 2 ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle' }} border rounded-pill px-2.5 py-1 small">
                                        {{ $review->edit_count }}
                                    </span>
                                </td>
                                <td>
                                    <button wire:click="toggleShowOnSite({{ $review->id }})" class="btn {{ $review->show_on_site ? 'btn-success' : 'btn-outline-secondary' }} btn-sm rounded-pill px-2.5 py-0.5" style="font-size: 0.75rem; font-weight: 600;">
                                        <i class="bi {{ $review->show_on_site ? 'bi-eye-fill' : 'bi-eye-slash-fill' }} me-1"></i>
                                        {{ $review->show_on_site ? __('Visible') : __('Hidden') }}
                                    </button>
                                </td>
                                <td class="small text-muted">
                                    {{ $review->created_at->format('d M Y') }}
                                    <div class="x-small text-muted" style="font-size:0.75rem;">({{ $review->created_at->diffForHumans() }})</div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-1.5">
                                        <button wire:click="startEdit({{ $review->id }})" class="btn btn-outline-primary btn-sm rounded-3" title="{{ __('Edit Review') }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="confirm('{{ __('Are you sure you want to delete this review?') }}') || event.stopImmediatePropagation()" wire:click="deleteReview({{ $review->id }})" class="btn btn-outline-danger btn-sm rounded-3" title="{{ __('Delete Review') }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-left-dots fs-2 d-block mb-2"></i>
                                    {{ __('No customer reviews found.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($reviews->hasPages())
                <div class="card-footer bg-white border-0 py-3 rounded-bottom-4">
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Edit Review Modal (Inline Overlay) --}}
    @if($editingReviewId)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>{{ __('Edit Review') }}</h5>
                        <button type="button" wire:click="cancelEdit" class="btn-close"></button>
                    </div>
                    <form wire:submit.prevent="updateReview">
                        <div class="modal-body py-3">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">{{ __('Rating') }}</label>
                                <div class="text-amber-400 fs-4 d-flex gap-2 justify-content-center my-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button" wire:click="$set('editingRating', {{ $i }})" class="btn p-0 text-amber-400 focus:outline-none">
                                            <i class="bi {{ $editingRating >= $i ? 'bi-star-fill' : 'bi-star' }} fs-3"></i>
                                        </button>
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted">{{ __('Comment') }}</label>
                                <textarea wire:model="editingComment" rows="4" class="form-control rounded-3" required></textarea>
                                @error('editingComment')
                                    <span class="text-danger small mt-1 d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" wire:click="cancelEdit" class="btn btn-light rounded-3 px-3 fw-semibold">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold">{{ __('Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
