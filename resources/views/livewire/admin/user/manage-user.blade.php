<div>
    <x-slot name="header">
        {{ __('Manage Users') }}
    </x-slot>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i>{{ __('User Directory') }}</h5>
            @can('create-user')
                <button wire:click="newUser" class="btn btn-sm btn-primary rounded-3 px-3"><i class="bi bi-plus-lg me-1"></i>{{ __('Add New User') }}</button>
            @endcan
        </div>
        <div class="card-body pt-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 p-2 bg-light rounded-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Show</span>
                    <select class="form-select form-select-sm rounded-3 shadow-sm" style="width: 85px;" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="30">30</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="*">All</option>
                    </select>
                    <span class="text-muted small">entries</span>
                </div>

                <div class="position-relative" style="width: 280px;">
                    <input type="text" class="form-control form-control-sm rounded-3 shadow-sm ps-3 pe-5" wire:model.live="search" placeholder="Search user details...">
                    <i class="bi bi-search position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                </div>
            </div>

            <div class="row g-3">
                @forelse ($users as $user)
                    @php
                        $initials = mb_strtoupper(mb_substr($user->name ?? '', 0, 1, 'UTF-8'), 'UTF-8');
                        $colors   = ['#4f46e5','#0ea5e9','#16a34a','#ea580c','#dc2626','#7c3aed','#0891b2','#ca8a04'];
                        $bgColor  = $colors[abs(crc32($user->email)) % count($colors)];
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border border-info-subtle rounded-4 shadow-sm hover-lift transition-all position-relative" style="transition: all 0.2s ease-in-out;">
                            
                            {{-- Top Action Corner / Badges --}}
                            <div class="position-absolute top-0 end-0 p-3 d-flex gap-1 align-items-center">
                                @if($user->id === Auth::id())
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.65rem; font-weight: 600;">You</span>
                                @endif
                            </div>

                            <div class="card-body p-4 d-flex flex-column h-100">
                                {{-- Avatar + Identity Header --}}
                                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                                    @if($user->profile_photo_path)
                                        <img src="{{ Storage::url($user->profile_photo_path) }}"
                                             alt="{{ $user->name }}"
                                             class="avatar rounded-circle shadow-sm border" style="width: 48px; height: 48px; object-fit: cover;">
                                    @else
                                        <div class="avatar rounded-circle d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                             style="background:{{ $bgColor }}; width: 48px; height: 48px; font-size:16px;">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <div class="overflow-hidden">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate" title="{{ $user->name }}">{{ $user->name }}</h6>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach ($user->getRoleNames() as $role)
                                                <span class="badge rounded-pill {{ $role === 'Super Admin' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} px-2 py-0.5" style="font-size: 0.62rem; font-weight: 600;">{{ $role }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- User Details --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-secondary p-1" style="width: 24px; height: 24px;">
                                            <i class="bi bi-envelope-fill" style="font-size: 0.75rem;"></i>
                                        </span>
                                        <span class="text-secondary text-truncate small" title="{{ $user->email }}">{{ $user->email }}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-secondary p-1" style="width: 24px; height: 24px;">
                                            <i class="bi bi-telephone-fill" style="font-size: 0.75rem;"></i>
                                        </span>
                                        <span class="text-secondary small">{{ $user->mobile ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light text-secondary p-1 mt-0.5" style="width: 24px; height: 24px; flex-shrink: 0;">
                                            <i class="bi bi-geo-alt-fill" style="font-size: 0.75rem;"></i>
                                        </span>
                                        <span class="text-secondary small text-line-clamp-2" style="font-size: 0.78rem;">{{ $user->address ?? 'No Address' }}</span>
                                    </div>

                                    {{-- Permissions List --}}
                                    <div class="mt-3 pt-2 border-top border-light">
                                        <div class="text-dark fw-bold mb-1.5" style="font-size: 0.72rem;"><i class="bi bi-key-fill me-1 text-info"></i>Permissions:</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? []))
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 0.65rem; font-weight: 500;"><i class="bi bi-star-fill me-1"></i>All Permissions</span>
                                            @else
                                                @php
                                                    $userPermissions = $user->getAllPermissions();
                                                    $permCount = $userPermissions->count();
                                                @endphp
                                                @forelse ($userPermissions->take(6) as $permission)
                                                    <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-2 py-0.5" style="font-size: 0.65rem; font-weight: 500;">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5" style="font-size: 0.65rem; font-weight: 500;">No Permissions</span>
                                                @endforelse
                                                @if ($permCount > 6)
                                                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">+{{ $permCount - 6 }} more</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Actions Footer --}}
                                <div class="mt-4 pt-3 border-top border-light d-flex justify-content-end gap-2">
                                    @if (in_array('Super Admin', $user->getRoleNames()->toArray() ?? []) )
                                        @if (Auth::user()->hasRole('Super Admin'))
                                            <button wire:click="editUser({{ $user->id }})" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1 fw-semibold" title="Edit" style="font-size: 0.75rem;"><i class="bi bi-pencil-square me-1"></i>Edit</button>
                                            @if (Auth::user()->id == $user->id)
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1 fw-semibold" wire:click="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')" title="Delete" style="font-size: 0.75rem;"><i class="bi bi-trash me-1"></i>Delete</button>
                                            @endif
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1" style="font-size: 0.72rem; font-weight: 500;"><i class="bi bi-lock-fill me-1"></i>System Protected</span>
                                        @endif
                                    @else
                                        @can('edit-user')
                                            <button wire:click="editUser({{ $user->id }})" class="btn btn-sm btn-outline-primary rounded-3 px-3 py-1 fw-semibold" title="Edit" style="font-size: 0.75rem;"><i class="bi bi-pencil-square me-1"></i>Edit</button>
                                        @endcan

                                        @can('delete-user')
                                            @if (Auth::user()->id != $user->id)
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-3 py-1 fw-semibold" wire:click="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')" title="Delete" style="font-size: 0.75rem;"><i class="bi bi-trash me-1"></i>Delete</button>
                                            @endif
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 py-5 text-center bg-light rounded-4 border border-dashed">
                        <span class="text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2"></i>No Users Found!
                        </span>
                    </div>
                @endforelse
            </div>

            <style>
                .hover-lift:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.08) !important;
                }
            </style>

            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    @include('livewire.admin.user.user-form')
</div>
