<div>
    <x-slot name="header">
        {{ __('Manage Roles') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-4">
        <div
            class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-dark"><i
                    class="bi bi-shield-lock-fill text-success me-2"></i>{{ __('Role Management') }}</h5>
            @can('create-user-role')
                <x-button-success wire:click="newRole" wire:loading.attr="disabled" class="rounded-3 px-3 btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('Add New Role') }}
                </x-button-success>
            @endcan
        </div>
        <div class="card-body pt-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 p-2 bg-light rounded-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Show</span>
                    <select class="form-select form-select-sm rounded-3 shadow-sm" style="width: 85px;"
                        wire:model.live="perPage" aria-label="Default select example">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="*">All</option>
                    </select>
                    <span class="text-muted small">entries</span>
                </div>
            </div>

            <div class="row g-3">
                @forelse ($roles as $role)
                    @php
                        $permissionsCount = $role->permissions->count();
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border border-info-subtle rounded-4 shadow-sm hover-lift transition-all"
                            style="transition: all 0.2s ease-in-out;">
                            <div class="card-body p-4 d-flex flex-column h-100">

                                {{-- Card Header: Role Name & Icon --}}
                                <div
                                    class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-light">
                                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                        <span
                                            class="d-inline-flex align-items-center justify-content-center rounded-3 bg-opacity-10 text-success p-2"
                                            style="width: 38px; height: 38px; flex-shrink: 0;">
                                            <i class="bi bi-shield-lock-fill" style="font-size: 1.1rem;"></i>
                                        </span>
                                        <div class="overflow-hidden">
                                            <h6 class="mb-0 fw-bold text-dark text-truncate"
                                                title="{{ $role->name }}">{{ $role->name }}</h6>
                                            <span class="text-muted" style="font-size: 0.72rem; font-weight: 500;">
                                                @if ($role->name === 'Super Admin')
                                                    All System Privileges
                                                @else
                                                    {{ $permissionsCount }}
                                                    {{ Str::plural('Permission', $permissionsCount) }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Body: Permissions List --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($role->name === 'Super Admin')
                                            <span
                                                class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1"
                                                style="font-size: 0.72rem; font-weight: 500;"><i
                                                    class="bi bi-star-fill me-1"></i>All Permissions</span>
                                        @else
                                            @forelse ($role->permissions->take(8) as $permission)
                                                <span
                                                    class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-2.5 py-1"
                                                    style="font-size: 0.72rem; font-weight: 500;">{{ $permission->name }}</span>
                                            @empty
                                                <span
                                                    class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1"
                                                    style="font-size: 0.72rem; font-weight: 500;">No Permissions
                                                    Assigned</span>
                                            @endforelse

                                            @if ($permissionsCount > 8)
                                                <span
                                                    class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 fw-bold"
                                                    style="font-size: 0.72rem;">+ {{ $permissionsCount - 8 }}
                                                    more</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Card Footer: Action Buttons --}}
                                <div class="mt-4 pt-3 border-top border-light d-flex justify-content-end gap-2">
                                    @if ($role->name !== 'Super Admin')
                                        @can('edit-user-role')
                                            <button class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1 fw-semibold"
                                                wire:click="editRole({{ $role->id }})" title="Edit"
                                                style="font-size: 0.75rem;"><i
                                                    class="bi bi-pencil-square me-1"></i>Edit</button>
                                        @endcan
                                        @can('delete-user-role')
                                            <button class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1 fw-semibold"
                                                wire:click="deleteRole({{ $role->id }}, '{{ $role->name }}')"
                                                title="Delete" style="font-size: 0.75rem;"><i
                                                    class="bi bi-trash me-1"></i>Delete</button>
                                        @endcan
                                    @else
                                        <span
                                            class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1"
                                            style="font-size: 0.72rem; font-weight: 500;"><i
                                                class="bi bi-lock-fill me-1"></i>System Protected</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center bg-light rounded-4 border border-dashed">
                        <span class="text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>No Roles Found!
                        </span>
                    </div>
                @endforelse
            </div>

            <style>
                .hover-lift:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .08) !important;
                }
            </style>

            <div class="mt-3">
                {{ $roles->links() }}
            </div>
        </div>
    </div>

    @include('livewire.admin.role.role-form')
</div>
