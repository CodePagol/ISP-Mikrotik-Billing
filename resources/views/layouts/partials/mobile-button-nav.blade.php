<nav id="navbar" class="navigation d-lg-none fixed-bottom bg-body-tertiary shadow-sm cp-z-10">
    <ul class="nav nav-tabs justify-content-center">
        @if (auth()->user()->roles()->exists() || auth()->user()->permissions()->exists())
            @if (auth()->user()->hasRole('Reseller'))
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active" href="{{ route('reseller.dashboard') }}">
                        <span class="icon">
                            <i class="bi bi-house-door"></i>
                        </span>
                        <span class="text">{{ __('Home') }}</span>
                    </a>
                </li>
                @can('payment-collection')
                    <li class="nav-item">
                        <a class="nav-link" wire:navigate.hover wire:current="active"
                            href="{{ route('payment-collection') }}">
                            <span class="icon">
                                <i class="bi bi-cash-coin"></i>
                            </span>
                            <span class="text">{{ __('Collection') }}</span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current.exact="active"
                        href="{{ route('reseller.customers.index') }}">
                        <span class="icon">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <span class="text">{{ __('Customers') }}</span>
                    </a>
                </li>
                @can('create-customer')
                    <li class="nav-item">
                        <a class="nav-link" wire:navigate.hover wire:current="active"
                            href="{{ route('reseller.customers.create') }}">
                            <span class="icon">
                                <i class="bi bi-person-fill-add"></i>
                            </span>
                            <span class="text">{{ __('New Customer') }}</span>
                        </a>
                    </li>
                @endcan
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active"
                        href="{{ route('reseller.wallet.index') }}">
                        <span class="icon">
                            <i class="bi bi-wallet2"></i>
                        </span>
                        <span class="text">{{ __('Wallet') }}</span>
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active" href="{{ route('dashboard') }}">
                        <span class="icon">
                            <i class="bi bi-house-door"></i>
                        </span>
                        <span class="text">{{ __('Home') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active"
                        href="{{ route('payment-collection') }}">
                        <span class="icon">
                            <i class="bi bi-cash-coin"></i>
                        </span>
                        <span class="text">{{ __('Collection') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current.exact="active"
                        href="{{ route('customers.index') }}">
                        <span class="icon">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <span class="text">{{ __('Customers') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active" href="{{ route('new-customer') }}">
                        <span class="icon">
                            <i class="bi bi-person-fill-add"></i>
                        </span>
                        <span class="text">{{ __('New Customer') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" wire:navigate.hover wire:current="active" href="{{ route('site-settings') }}">
                        <span class="icon">
                            <i class="bi bi-gear"></i>
                        </span>
                        <span class="text">{{ __('Settings') }}</span>
                    </a>
                </li>
            @endif
        @else
            <li class="nav-item">
                <a class="nav-link" wire:navigate.hover wire:current="active" href="{{ route('profile.show') }}">
                    <span class="icon">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <span class="text">{{ __('Profile') }}</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
