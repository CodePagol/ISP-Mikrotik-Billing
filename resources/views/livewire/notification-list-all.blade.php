<div class="card shadow-sm border-0">
    <div class="card-header bg-white dark__bg-1000 py-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <h5 class="mb-0 text-900 d-flex align-items-center gap-2">
                <i class="bi bi-bell-fill text-warning"></i>
                {{ __('All Notifications Log') }}
            </h5>
            <p class="text-500 fs-11 mb-0">{{ __('Monitor and manage all system and ticket activity alerts.') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button wire:click="markAllAsRead" class="btn btn-sm btn-subtle-primary" wire:loading.attr="disabled">
                <i class="bi bi-check2-all me-1"></i> {{ __('Mark All as Read') }}
            </button>
        </div>
    </div>

    <div class="card-body p-3">
        <!-- Search and Filters -->
        <div class="row g-2 mb-4">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-500">
                        <i class="bi bi-search"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm ps-5 rounded-pill" placeholder="{{ __('Search notification message...') }}">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <select wire:model.live="statusFilter" class="form-select form-select-sm rounded-pill">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="unread">{{ __('Unread Only') }}</option>
                    <option value="read">{{ __('Read Only') }}</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <select wire:model.live="titleFilter" class="form-select form-select-sm rounded-pill">
                    <option value="">{{ __('All Titles') }}</option>
                    @foreach($titles as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <select wire:model.live="typeFilter" class="form-select form-select-sm rounded-pill">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach($types as $tp)
                        <option value="{{ $tp }}">{{ $tp }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <select wire:model.live="readByFilter" class="form-select form-select-sm rounded-pill">
                    <option value="">{{ __('All Read By') }}</option>
                    @foreach($readByUsers as $user)
                        <option value="{{ $user }}">{{ $user }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Notification List -->
        <div class="notification-container">
            @forelse ($notifications as $notification)
                <div class="p-3 mb-2 rounded-3 border d-flex align-items-start justify-content-between gap-3 transition-all {{ $notification->read_at == null ? 'bg-light-subtle border-warning border-start border-3 shadow-none' : 'bg-body border-200' }}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="avatar avatar-xl flex-shrink-0">
                            <img class="rounded-circle" src="{{ generate_avatar($notification->title) }}" alt="{{ $notification->title }}" />
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="mb-1 text-800 fw-bold">{{ $notification->title }}</h6>
                                <span class="badge rounded-pill fs-11 py-0 px-2 bg-subtle-secondary text-secondary">
                                    {{ __($notification->type ?? 'System') }}
                                </span>
                                @if($notification->read_at == null)
                                    <span class="badge rounded-pill fs-11 py-0 px-2 bg-subtle-warning text-warning">{{ __('New') }}</span>
                                @endif
                            </div>
                            <p class="text-700 fs-10 mb-2">{{ $notification->message }}</p>
                            <div class="d-flex align-items-center gap-3 flex-wrap fs-11 text-500">
                                <span>
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                @if($notification->read_at != null)
                                    <span class="text-success">
                                        <i class="bi bi-check2 me-1"></i>
                                        {{ __('Read by') }} {{ $notification->read_by }} ({{ Carbon\Carbon::parse($notification->read_at)->diffForHumans() }})
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($notification->read_at == null)
                            <button wire:click="markAsRead({{ $notification->id }})" class="btn btn-sm btn-subtle-success py-1 px-2 text-nowrap rounded-pill" title="{{ __('Mark Read') }}">
                                <i class="bi bi-check-lg"></i> <span class="d-none d-sm-inline ms-1">{{ __('Read') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <img class="mb-3" src="{{ asset('images/authentication-corner.png') }}" width="80" alt="No Data" style="opacity: 0.5;">
                    <p class="text-500 mb-0">{{ __('No notifications found matching your search or filters.') }}</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
