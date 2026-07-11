<div x-data="{ isOpen: false }" class="zoom-in">
    <!-- Header -->
    <x-slot name="header">
        {{ __('Mikrotik List') }}
    </x-slot>

    <div class="card">
        <div class="card-body">
            <div class="row">
                    <div class="p-1">
                        <!-- Toggle Button -->
                        <button
                            @click="isOpen = !isOpen; if (!isOpen) { $wire.set('router_name', ''); $wire.set('ip_address', ''); $wire.set('username', ''); $wire.set('password', ''); $wire.set('ssh_port', ''); $wire.set('api_port', ''); }"
                            class="btn btn-sm btn-primary"
                            type="button">
                            <span x-text="isOpen ? '{{ __('Hide This') }}' : '{{ __('Add Mikrotik') }}'"></span>
                        </button>
                    </div>
                    <!-- Collapse Section -->
                    <div x-show="isOpen" x-transition x-cloak>
                        <div class="card card-body">
                            <form wire:submit.prevent="submit">
                                <div class="row g-2 form-group">
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="text" id="router_name" wire:model="router_name" placeholder="{{ __('Router Name') }}" aria-label="Router Name" name="router_name" >
                                        <x-input-error for='router_name' />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="text" id="ip_address" wire:model="ip_address" placeholder="{{ __('IP Address') }}" aria-label="IP Address">
                                        <x-input-error for='ip_address' />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="text" id="username" wire:model="username" placeholder="{{ __('Username') }}" aria-label="Username">
                                        <x-input-error for='username' />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="password" id="password" wire:model="password" placeholder="{{ __('Password') }}" aria-label="Password">
                                        <x-input-error for='password' />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="number" id="ssh_port" wire:model="ssh_port" placeholder="{{ __('SSH Port') }}" aria-label="SSH Port">
                                        <x-input-error for='ssh_port' />
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                                        <input class="form-control form-control-sm" type="number" id="api_port" wire:model="api_port" placeholder="{{ __('API Port') }}" aria-label="API Port">
                                        <x-input-error for='api_port' />
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-primary mt-2" type="submit">{{ __('Submit') }}</button>
                            </form>
                        </div>
                    </div>
            </div>

            <div class="row g-3 mt-3">
                @foreach ($routers as $router)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden d-flex flex-column" style="transition: transform 0.2s ease;">
                            <!-- Header -->
                            <div class="px-3 py-2 text-white" style="background: linear-gradient(135deg, #0f172a, #1e293b);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="overflow-hidden">
                                        <h6 class="fw-bold mb-0 text-truncate text-white">{{ $router->router_name }}</h6>
                                        <small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-laptop me-1"></i>{{ $router->ip_address }}</small>
                                    </div>
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-20" style="width: 32px; height: 32px;">
                                        <i class="bi bi-router text-info" style="font-size: 1rem;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body bg-light p-3 d-flex flex-column justify-content-between">
                                <div class="space-y-2 mb-3">
                                    <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <span class="text-muted">{{ __('Username') }}:</span>
                                        <span class="fw-bold text-dark">{{ $router->username }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <span class="text-muted">{{ __('SSH Port') }}:</span>
                                        <span class="fw-bold text-dark">{{ $router->ssh_port ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center" style="font-size: 0.8rem;">
                                        <span class="text-muted">{{ __('API Port') }}:</span>
                                        <span class="fw-bold text-dark">{{ $router->api_port ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top" style="font-size: 0.8rem;">
                                        <span class="text-muted">{{ __('Customers') }}:</span>
                                        <a href="{{ route('customers.index') }}" id="customers-{{ $router->id }}" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 customers text-decoration-none" style="font-size: 0.75rem;">
                                            <i class="bi bi-people-fill me-1"></i>{{ $router->user_list_count }}
                                        </a>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <!-- Connected Toggle Switch -->
                                    <div class="checkbox-container">
                                        <input type="checkbox" id="action-{{ $router->id }}" class="toggle-checkbox"
                                            wire:click="connect_toggle({{ $router->id }})" {{ $router->action === 'connected' ? 'checked' : '' }}
                                            x-data="{
                                                dataSyncFunction(customer_id) {
                                                    $(customer_id).text('');
                                                    let spinnerSpan = document.createElement('span');
                                                    spinnerSpan.classList.add('spinner-border', 'spinner-border-sm', 'text-primary');
                                                    spinnerSpan.setAttribute('aria-hidden', 'true');
                                                    $(customer_id).append(spinnerSpan);
                                                }
                                            }"
                                            x-on:click="if($event.target.checked){dataSyncFunction('#customers-' + {{ $router->id }})}">
                                        <label for="action-{{ $router->id }}" class="toggle-label" style="transform: scale(0.85); transform-origin: left center; margin-bottom: 0;">
                                            <span class="connected-text">{{ __('Connected') }}</span>
                                            <span class="disconnected-text">{{ __('Disconnected') }}</span>
                                            <span class="toggle-switch"></span>
                                        </label>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-danger p-1" title="{{ __('Delete') }}" wire:click="delete({{ $router->id }})">
                                            <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info p-1" title="{{ __('Edit') }}" wire:click="edit({{ $router->id }})" @click="isOpen = true">
                                            <i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i>
                                        </button>
                                        <button
                                            x-data="{
                                                dataSyncFunction(customer_id) {
                                                    $(customer_id).text('');
                                                    let spinnerSpan = document.createElement('span');
                                                    spinnerSpan.classList.add('spinner-border', 'spinner-border-sm', 'text-primary');
                                                    spinnerSpan.setAttribute('aria-hidden', 'true');
                                                    $(customer_id).append(spinnerSpan);
                                                }
                                            }"
                                            x-on:click="dataSyncFunction('#customers-' + {{ $router->id }})"
                                            class="btn btn-sm btn-outline-primary p-1" title="{{ __('Sync') }}" wire:click="dataSync({{ $router->id }})">
                                            <i class="bi bi-arrow-repeat" style="font-size: 0.85rem;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination">
                {{ $routers->links() }}
            </div>
        </div>
    </div>

    <!-- Synchronize Data (to show spinner) -->
    <div
        x-data="{
            dataSyncFunction() {
                $('.customers').text('');
                let spinnerSpan = document.createElement('span');
                spinnerSpan.classList.add('spinner-border', 'spinner-border-sm', 'text-primary');
                spinnerSpan.setAttribute('aria-hidden', 'true');
                $('.customers').append(spinnerSpan);
            }
        }" x-init="$wire.allSync() && dataSyncFunction()">
    </div>
</div>
