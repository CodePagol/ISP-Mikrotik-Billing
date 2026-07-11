@if (auth()->user()->roles()->exists() || auth()->user()->permissions()->exists())
    @if (auth()->user()->hasRole('Reseller'))
        <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">

            {{-- ── Dashboard (always visible) ── --}}
            <li class="nav-item">
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Reseller Panel') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('reseller.dashboard') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-speedometer2 me-2"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Dashboard') }}</span>
                    </div>
                </a>
            </li>

            {{-- ── Customers ── --}}
            @canany(['view-customer', 'create-customer', 'edit-customer', 'delete-customer'])
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">{{ __('Customers') }}</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>
                    <a wire:navigate.hover wire:current.exact="active" class="nav-link"
                        href="{{ route('reseller.customers.index') }}" role="button">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon"><i class="bi bi-people-fill"></i></span>
                            <span class="nav-link-text ps-1">{{ __('Customer List') }}</span>
                        </div>
                    </a>
                    @can('create-customer')
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('reseller.customers.create') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-person-fill-add"></i></span>
                                <span class="nav-link-text ps-1">{{ __('New Customer') }}</span>
                            </div>
                        </a>
                    @endcan
                </li>
            @endcanany

            {{-- ── Billing & Payments ── --}}
            @canany(['payment-collection', 'payment-collection-edit', 'payment-collection-invoice', 'payment-history',
                'payment-collection-report', 'collection-list', 'without-collection-list', 'amount-collection'])
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">{{ __('Billing') }}</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>
                    @can('payment-collection')
                        <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('payment-collection') }}"
                            role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-cash-coin"></i></span>
                                <span class="nav-link-text ps-1">{{ __('Payment Collection') }}</span>
                            </div>
                        </a>
                    @endcan
                    @can('payment-collection-edit')
                        <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('collection-edit') }}"
                            role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-pencil-square"></i></span>
                                <span class="nav-link-text ps-1">{{ __('Collection Edit') }}</span>
                            </div>
                        </a>
                    @endcan
                    @can('payment-collection-invoice')
                        <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('payment-invoice') }}"
                            role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-receipt"></i></span>
                                <span class="nav-link-text ps-1">{{ __('Payment Invoice') }}</span>
                            </div>
                        </a>
                    @endcan
                    @canany(['payment-collection-report', 'collection-list', 'without-collection-list',
                        'amount-collection'])
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('collection-report.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-bar-chart-line"></i></span>
                                <span class="nav-link-text ps-1">{{ __('Collection Report') }}</span>
                            </div>
                        </a>
                    @endcanany
                </li>
            @endcanany

            {{-- ── Wallet & Vouchers (always visible to all resellers) ── --}}
            <li class="nav-item">
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('My Account') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('reseller.wallet.index') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-wallet2"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Wallet & Earnings') }}</span>
                    </div>
                </a>
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('reseller.vouchers.index') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-ticket-perforated-fill"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Vouchers') }}</span>
                    </div>
                </a>
            </li>

            {{-- ── Setup & Access ── --}}
            @canany(['package-setup'])
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">{{ __('Setup') }}</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider" />
                        </div>
                    </div>
                    @can('package-setup')
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('reseller.packages.index') }}" role="button">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="bi bi-box2"></i></span>
                                <span class="nav-link-text ps-1">{{ __('Packages') }}</span>
                            </div>
                        </a>
                    @endcan
                </li>
            @endcanany

        </ul>
    @else
        <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
            <li class="nav-item">
                <!-- label-->
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Dashboard') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <!-- parent pages-->
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('dashboard') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-speedometer2 me-2"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Dashboard') }}</span>
                    </div>
                </a>
            </li>
            <li class="nav-item">
                <!-- label-->
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Mikrotik') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <!-- parent pages-->
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('mikrotik-sync') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-router-fill"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Mikrotik Sync') }}</span>
                    </div>
                </a>

                <!-- Mikrotik Setup Dropdown -->
                <a class="nav-link dropdown-indicator collapsed" href="#mikrotikSetup" role="button"
                    data-bs-toggle="collapse" aria-expanded="false" aria-controls="mikrotikSetup">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-tools"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Mikrotik Setup') }}</span>
                    </div>
                </a>
                <ul class="nav collapse" id="mikrotikSetup" style="">
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-ip-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('IP & Pool') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-pppoe-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('PPPoE Server') }}</span></div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-radius-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('RADIUS') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-firewall-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Firewall') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-walled-garden') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Walled Garden') }}</span><span class="badge rounded-pill ms-2 badge-subtle-info">{{ __('New') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-queue-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Queues') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-vpn-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('VPN Server') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-interface-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Interfaces & VLAN') }}</span></div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-traffic-monitor') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Live Traffic') }}</span><span class="badge rounded-pill ms-2 badge-subtle-success">{{ __('New') }}</span>
                            </div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-backup-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Backup & Restore') }}</span><span
                                    class="badge rounded-pill ms-2 badge-subtle-primary">{{ __('Admin') }}</span></div>
                        </a></li>
                </ul>

                <!-- Hotspot (Unified) -->
            <li class="nav-item">
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('mikrotik-hotspot-manager') }}">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-wifi"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Hotspot') }}</span>
                    </div>
                </a>
            </li>
            <li class="nav-item">
                <!-- label-->
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Customers') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <!-- parent pages-->
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('new-customer') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-person-fill-add"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('New Customer') }}</span>
                    </div>
                </a>
                <a wire:navigate.hover wire:current.exact="active" class="nav-link"
                    href="{{ route('customers.index') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-people-fill"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Customers') }}</span>
                    </div>
                </a>
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('admin-tickets') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-chat-left-text-fill"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Support Tickets') }}</span>
                    </div>
                </a>
                <!-- parent pages-->
                <a class="nav-link dropdown-indicator collapsed" href="#collections" role="button"
                    data-bs-toggle="collapse" aria-expanded="true" aria-controls="collections">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-cash-coin"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Collection') }}</span>
                    </div>
                </a>
                <ul class="nav collapse" id="collections" style="">
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('payment-collection') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Payment Collection') }}</span></div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('collection-edit') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Collection Edit') }}</span></div>
                        </a>
                    </li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('payment-invoice') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Payment Invoice') }}</span></div>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <!-- label-->
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Reports') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <!-- parent pages-->
                <a class="nav-link dropdown-indicator collapsed" href="#reports" role="button"
                    data-bs-toggle="collapse" aria-expanded="true" aria-controls="reports">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Reports') }}</span>
                    </div>
                </a>
                <ul class="nav collapse" id="reports" style="">
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('collection-report.index') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Collections Report') }}</span></div>
                        </a></li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('customer-summary') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Customer Summary') }}</span></div>
                        </a>
                    </li>
                    <li class="nav-item"><a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('dis-report') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('DIS Summary') }}</span><span class="badge rounded-pill ms-2 badge-subtle-success">{{ __('New') }}</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </li>
            </li>

            {{-- ── Finance ── --}}
            <li class="nav-item">
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Finance') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('admin.expenses') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-wallet2"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Expense Management') }}</span>
                    </div>
                </a>
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('admin.profit-summary') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon"><i class="bi bi-graph-up-arrow text-success"></i></span>
                        <span class="nav-link-text ps-1">{{ __('Profit & Loss') }}</span>
                    </div>
                </a>
            </li>

            <li class="nav-item">
                <!-- label-->
                <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                    <div class="col-auto navbar-vertical-label">{{ __('Admin') }}</div>
                    <div class="col ps-0">
                        <hr class="mb-0 navbar-vertical-divider" />
                    </div>
                </div>
                <!-- parent pages-->
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('address-setup') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-buildings"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Address Setup') }}</span>
                    </div>
                </a>
                <!-- parent pages-->
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('package-list-setup') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-box2"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Package Setup') }}</span>
                    </div>
                </a>
                <!-- Purchase Requests page-->
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('admin.purchase-requests') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-cart-check-fill"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Purchase Requests') }}</span>
                    </div>
                </a>
                <!-- reseller setup page-->
                <a wire:navigate.hover wire:current="active" class="nav-link"
                    href="{{ route('admin.resellers.index') }}" role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-person-badge-fill"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Reseller Setup') }}</span>
                    </div>
                </a>
                <!-- Logs Management Dropdown -->
                <a class="nav-link dropdown-indicator collapsed" href="#logsSetupDropdown" role="button"
                    data-bs-toggle="collapse" aria-expanded="false" aria-controls="logsSetupDropdown">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-journal-text"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Logs & Audits') }}</span>
                    </div>
                </a>
                <ul class="nav collapse" id="logsSetupDropdown">
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('admin.activity-logs') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Activity Logs') }}</span></div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('admin.login-logs') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Login Logs') }}</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('admin.system-logs') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('System Logs') }}</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('mikrotik-log-viewer') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Router Logs') }}</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('admin.vouchers') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('Reseller Vouchers') }}</span></div>
                        </a>
                    </li>
                </ul>

                <!-- Customer Reviews page-->
                <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('admin.reviews') }}"
                    role="button">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-chat-heart"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('Customer Reviews') }}</span>
                    </div>
                </a>
                <!-- parent pages-->
                <!-- SMS Management Dropdown -->
                <a class="nav-link dropdown-indicator collapsed" href="#smsSetupDropdown" role="button"
                    data-bs-toggle="collapse" aria-expanded="false" aria-controls="smsSetupDropdown">
                    <div class="d-flex align-items-center">
                        <span class="nav-link-icon">
                            <i class="bi bi-envelope-check"></i>
                        </span>
                        <span class="nav-link-text ps-1">{{ __('SMS Management') }}</span>
                    </div>
                </a>
                <ul class="nav collapse" id="smsSetupDropdown">
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('sms-setup') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('SMS Setup') }}</span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a wire:navigate.hover wire:current="active" class="nav-link"
                            href="{{ route('sms-bridge.index') }}">
                            <div class="d-flex align-items-center"><span class="nav-link-text ps-1">{{ __('SMS Bridge') }}</span>
                            </div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    @endif
@else
    <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">
        <li class="nav-item">
            <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                <div class="col-auto navbar-vertical-label">{{ __('Account') }}</div>
                <div class="col ps-0">
                    <hr class="mb-0 navbar-vertical-divider" />
                </div>
            </div>
            <a wire:navigate.hover wire:current="active" class="nav-link" href="{{ route('profile.show') }}"
                role="button">
                <div class="d-flex align-items-center">
                    <span class="nav-link-icon"><i class="bi bi-person-fill"></i></span>
                    <span class="nav-link-text ps-1">{{ __('Profile') }}</span>
                </div>
            </a>
        </li>
    </ul>
@endif
