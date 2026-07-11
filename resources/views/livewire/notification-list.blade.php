
<li class="nav-item dropdown">
    <a  x-data @mouseenter="$wire.call('loadNotifications')"
        class="{{$notificationCount > 0 ? 'animate-ring' : ''}} nav-link px-0 notification-indicator notification-indicator-warning"
        id="navbarDropdownNotification" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false" data-hide-on-body-scroll="data-hide-on-body-scroll">
        <span class="bi bi-bell-fill fs-7" style="font-size: 35px;"></span>
        <span class="notification-indicator-number">{{$notificationCount}}</span>
    </a>

    <div wire:ignore.self class="dropdown-menu dropdown-caret dropdown-menu-end dropdown-menu-card dropdown-menu-notification dropdown-caret-bg" aria-labelledby="navbarDropdownNotification">
        <div class="card card-notification shadow-none">
            <div class="card-header">
                <div class="row justify-content-between align-items-center">
                    <div class="col-auto">
                        <h6 class="card-header-title mb-0">{{ __('Notifications') }}</h6>
                    </div>
                    <div class="col-auto ps-0 ps-sm-3"><a href="#" wire:click="markAllAsRead()" class="card-link fw-normal">{{ __('Mark All as Read') }}</a>
                    </div>
                </div>
            </div>
            <div class="scrollbar-overlay overflow-auto" style="max-height:19rem">
                <div class="list-group list-group-flush fw-normal fs-10">
                    <div class="list-group-title border-bottom">{{ __('New') }}</div>
                    @foreach ($notifications as $notification)
                        <div class="list-group-item">
                            <a class="notification notification-flush {{ $notification->read_at == null ? 'notification-unread' : '' }}"
                                @if($notification->read_at == null) wire:click="markAsRead({{ $notification->id }})" @endif>

                                <div class="notification-avatar">
                                    <div class="avatar avatar-2xl me-3">
                                        <img class="rounded-circle" src="{{ generate_avatar($notification->title) }}"
                                            alt="{{$notification->title}}" />
                                    </div>
                                </div>
                                <div class="notification-body">
                                    <p class="mb-1"><strong>{{$notification->title}}</strong> {{ __('Message :') }}
                                        {{ Str::limit($notification->message, 100) }}</p>
                                    <span class="notification-time"><span class="me-2" role="img"
                                            aria-label="Emoji">💬</span>{{$notification->created_at->diffForHumans()}}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card-footer text-center border-top"><a wire:navigate.hover class="card-link d-block" href="{{ route('notifications')}}">{{ __('View All') }}</a></div>
        </div>
    </div>
</li>
