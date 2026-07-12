<div>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <span class="h4 mb-0"><i class="bi bi-chat-left-text me-2 text-primary"></i>{{ __('Support Tickets') }}</span>
        </div>
    </x-slot>

    <!-- Stats Overview Cards -->
    <div class="row g-3 mb-4 mt-1">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-primary-subtle text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-ticket-perforated fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold uppercase">{{ __('Total Tickets') }}</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-warning-subtle text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold uppercase">{{ __('Open') }}</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['open'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-info-subtle text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold uppercase">{{ __('In Progress') }}</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['in_progress'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-3 bg-success-subtle text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold uppercase">{{ __('Resolved') }}</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $stats['resolved'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Main Card -->
    <div class="card border-0 shadow-sm mx-0">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="d-flex align-items-center gap-1">
                        <label class="small text-muted fw-bold text-nowrap mb-0 me-1">{{ __('Show') }}:</label>
                        <select class="form-select form-select-sm border-0 bg-light" wire:model.live="perPage" style="width: 80px; border-radius: 8px;">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <label class="small text-muted fw-bold text-nowrap mb-0 me-1 ms-md-2">{{ __('Status') }}:</label>
                        <select class="form-select form-select-sm border-0 bg-light" wire:model.live="statusFilter" style="width: 140px; border-radius: 8px;">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="open">{{ __('Open') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="closed">{{ __('Closed') }}</option>
                        </select>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <label class="small text-muted fw-bold text-nowrap mb-0 me-1 ms-md-2">{{ __('Priority') }}:</label>
                        <select class="form-select form-select-sm border-0 bg-light" wire:model.live="priorityFilter" style="width: 140px; border-radius: 8px;">
                            <option value="">{{ __('All Priorities') }}</option>
                            <option value="low">{{ __('Low') }}</option>
                            <option value="medium">{{ __('Medium') }}</option>
                            <option value="high">{{ __('High') }}</option>
                        </select>
                    </div>
                </div>

                <div class="position-relative" style="min-width: 250px;">
                    <input type="text" class="form-control form-control-sm border-0 bg-light ps-4" 
                        wire:model.live="search" placeholder="{{ __('Search tickets...') }}" style="border-radius: 8px;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                </div>
            </div>
        </div>

        <div class="card-body px-3 pb-3">
            <div class="table-responsive bg-white rounded-3 mt-3">
                <table class="table table-hover align-middle border-0 mb-0">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="border-0 py-3" style="width: 110px;">{{ __('Ticket No') }}</th>
                            <th class="border-0 py-3">{{ __('Customer') }}</th>
                            <th class="border-0 py-3">{{ __('Subject & Details') }}</th>
                            <th class="border-0 py-3" style="width: 120px;">{{ __('Category') }}</th>
                            <th class="border-0 py-3" style="width: 100px;">{{ __('Priority') }}</th>
                            <th class="border-0 py-3" style="width: 110px;">{{ __('Status') }}</th>
                            <th class="border-0 py-3" style="width: 120px;">{{ __('Submitted') }}</th>
                            <th class="border-0 py-3 text-end" style="width: 140px;">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tickets as $ticket)
                        <tr class="border-bottom border-light">
                            <td class="font-monospace fw-bold text-primary py-3">#{{ $ticket->ticket_no }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">{{ $ticket->customer->customer_name ?? 'N/A' }}</span>
                                    <small class="text-muted" style="font-size: 0.75rem;">ID: {{ $ticket->customer_unique_id }} ({{ $ticket->ppp_username }})</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark">{{ $ticket->subject }}</span>
                                    <small class="text-muted text-truncate d-inline-block" style="max-width: 320px;">{{ $ticket->description }}</small>
                                </div>
                            </td>
                            <td class="text-capitalize text-secondary">{{ __($ticket->category) }}</td>
                            <td>
                                @php
                                    $priorityClass = match($ticket->priority) {
                                        'high' => 'bg-danger-subtle text-danger border border-danger-subtle',
                                        'medium' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'low' => 'bg-success-subtle text-success border border-success-subtle',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $priorityClass }} px-2 py-1" style="font-size: 0.7rem; border-radius: 6px;">
                                    {{ __(ucfirst($ticket->priority)) }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($ticket->status) {
                                        'open' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                                        'in_progress' => 'bg-primary-subtle text-primary border border-primary-subtle',
                                        'resolved' => 'bg-success-subtle text-success border border-success-subtle',
                                        'closed' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                        default => 'bg-secondary-subtle text-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }} px-2 py-1" style="font-size: 0.7rem; border-radius: 6px;">
                                    {{ __(ucfirst(str_replace('_', ' ', $ticket->status))) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small" title="{{ $ticket->created_at }}">{{ $ticket->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="text-end">
                                <button wire:click="showReplyModal({{ $ticket->id }})" class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px;">
                                    <i class="bi bi-chat-dots me-1"></i>{{ __('Manage') }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-danger py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-ticket-perforated text-muted mb-2" style="font-size: 2.5rem;"></i>
                                    <strong>{{ __('No Tickets Found!') }}</strong>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

    {{-- Reply Modal --}}
    @if($confirmingReply && $selectedTicket)
    <x-dialog-modal wire:model.live="confirmingReply" maxWidth="2xl">
        <x-slot name="title">
            <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                <span class="h5 mb-0"><i class="bi bi-chat-text text-primary me-2"></i>{{ __('Manage Ticket') }}</span>
                <span class="badge bg-primary-subtle text-primary font-monospace" style="font-size: 0.8rem; border-radius: 6px;">#{{ $selectedTicket->ticket_no }}</span>
            </div>
        </x-slot>

        <x-slot name="content">
            <div class="row g-3 mb-4 mt-1 bg-light p-3 rounded-3 mx-0">
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.65rem;">{{ __('Customer Name') }}</small>
                    <span class="text-dark fw-bold">{{ $selectedTicket->customer->customer_name ?? 'N/A' }}</span>
                </div>
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.65rem;">{{ __('PPP Username / ID') }}</small>
                    <span class="text-dark font-monospace">{{ $selectedTicket->ppp_username }} ({{ $selectedTicket->customer_unique_id }})</span>
                </div>
                @if($selectedTicket->customer && $selectedTicket->customer->mobile)
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.65rem;">{{ __('Contact Mobile') }}</small>
                    <span class="text-dark">{{ $selectedTicket->customer->mobile }}</span>
                </div>
                @endif
                <div class="col-12 col-md-6">
                    <small class="text-muted d-block uppercase fw-bold" style="font-size: 0.65rem;">{{ __('Priority & Category') }}</small>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis me-1" style="font-size: 0.68rem; border-radius: 4px;">{{ __($selectedTicket->category) }}</span>
                    <span class="badge bg-{{ $selectedTicket->priority === 'high' ? 'danger' : ($selectedTicket->priority === 'medium' ? 'warning text-dark' : 'success') }}-subtle text-{{ $selectedTicket->priority === 'high' ? 'danger' : ($selectedTicket->priority === 'medium' ? 'warning-emphasis' : 'success') }} px-2" style="font-size: 0.68rem; border-radius: 4px;">{{ __(ucfirst($selectedTicket->priority)) }}</span>
                </div>
            </div>

            <!-- Subject & Description Box -->
            <div class="card border-0 bg-white mb-4 shadow-none">
                <div class="card-body p-0">
                    <h6 class="fw-bold text-dark mb-1">{{ $selectedTicket->subject }}</h6>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size: 0.88rem; white-space: pre-line; border-left: 4px solid var(--cp-primary);">
                        {{ $selectedTicket->description }}
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="submitReply">
                <div class="mb-4">
                    <label for="adminReply" class="form-label fw-bold text-dark">{{ __('Admin Reply / Response') }}</label>
                    <textarea id="adminReply" class="form-control border-light-subtle @error('adminReply') is-invalid @enderror" 
                        wire:model="adminReply" rows="5" placeholder="{{ __('Type your reply here to send to the customer...') }}" style="border-radius: 10px; background-color: #fcfdfe;"></textarea>
                    <x-error name="adminReply" />
                </div>

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-top pt-3">
                    <div class="d-flex align-items-center gap-2">
                        <label for="status" class="form-label mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ __('Update Ticket Status:') }}</label>
                        <select id="status" class="form-select form-select-sm border-0 bg-light @error('status') is-invalid @enderror" 
                            wire:model="status" style="width: 140px; border-radius: 8px;">
                            <option value="open">{{ __('Open') }}</option>
                            <option value="in_progress">{{ __('In Progress') }}</option>
                            <option value="resolved">{{ __('Resolved') }}</option>
                            <option value="closed">{{ __('Closed') }}</option>
                        </select>
                        <x-error name="status" />
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary px-3" style="border-radius: 8px;" wire:click="$set('confirmingReply', false)">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;"><i class="bi bi-send me-1"></i>{{ __('Save Response') }}</button>
                    </div>
                </div>
            </form>
        </x-slot>

        <x-slot name="footer">
            {{-- blank --}}
        </x-slot>
    </x-dialog-modal>
    @endif
</div>
