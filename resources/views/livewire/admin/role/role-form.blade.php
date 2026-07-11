<x-dialog-modal wire:model.live="confirmingRole" maxWidth="2xl" class="mt-2">
    <x-slot name="title">
        {{ $roleType }}
    </x-slot>

    <x-slot name="content">
        <form wire:submit.prevent="saveRole" method="post">
            <x-mikrotik.form-input
                labelClass="col-md-4 col-form-label text-md-end text-start"
                groupClass="col-md-7"
                label="{{ __('Role Name') }}"
                type="text"
                name="name"
                required="true"
            />

            @php
                $grouped = [];
                foreach ($permissionList ?? [] as $permission) {
                    $name = $permission->name;
                    if (str_contains($name, 'user') && !str_contains($name, 'role')) {
                        $category = 'User Management';
                    } elseif (str_contains($name, 'role')) {
                        $category = 'Role & Permission';
                    } elseif (str_contains($name, 'router') || str_contains($name, 'interface') || str_contains($name, 'traffic')) {
                        $category = 'Router Management';
                    } elseif (str_contains($name, 'hotspot') || str_contains($name, 'profile')) {
                        $category = 'Hotspot Settings';
                    } elseif (str_contains($name, 'reseller')) {
                        $category = 'Reseller Management';
                    } else {
                        $category = 'Other Permissions';
                    }
                    $grouped[$category][] = $permission;
                }
            @endphp

            <div class="mb-3 row">
                <label class="col-md-4 col-form-label text-md-end text-start fw-semibold text-dark">{{ __('Permissions') }} <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <div class="mb-2">
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm rounded-3 ps-3 pe-5" id="permissionSearch" placeholder="Search permissions..." onkeyup="filterPermissions()">
                            <i class="bi bi-search position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                        </div>
                    </div>
                    
                    <div class="border rounded-3 p-3 bg-white shadow-inner" style="max-height: 350px; overflow-y: auto;">
                        @foreach ($grouped as $category => $items)
                            <div class="permission-section mb-3">
                                <h6 class="fw-bold text-muted text-uppercase mb-2" style="font-size: 0.65rem; letter-spacing: 0.8px;">{{ $category }}</h6>
                                <div class="row g-2">
                                    @foreach ($items as $permission)
                                        @php
                                            $isSelected = in_array($permission->id, $permissions ?? []);
                                        @endphp
                                        <div class="col-sm-6 permission-card" data-name="{{ $permission->name }}">
                                            <label class="card h-100 border rounded-3 p-2 cursor-pointer transition-all position-relative overflow-hidden {{ $isSelected ? 'border-success bg-success-subtle bg-opacity-25' : 'border-light bg-light bg-opacity-50' }}" 
                                                   style="user-select: none; transition: all 0.2s ease-in-out;">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                                                        <span class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 {{ $isSelected ? 'bg-success text-white' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="width: 28px; height: 28px;">
                                                            <i class="bi {{ $isSelected ? 'bi-shield-check' : 'bi-shield' }}" style="font-size: 0.9rem;"></i>
                                                        </span>
                                                        <span class="fw-semibold text-truncate text-dark" style="font-size: 0.78rem;">{{ ucwords(str_replace(['-', 'user', 'role', 'router', 'hotspot'], ['', 'User', 'Role', 'Router', 'Hotspot'], $permission->name)) }}</span>
                                                    </div>
                                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" wire:model.live="permissions" style="display: none;">
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <x-error name="permissions" />
                </div>
            </div>

            <script>
                function filterPermissions() {
                    let input = document.getElementById('permissionSearch').value.toLowerCase();
                    let cards = document.querySelectorAll('.permission-card');
                    cards.forEach(card => {
                        let name = card.getAttribute('data-name').toLowerCase();
                        if (name.includes(input)) {
                            card.style.setProperty('display', 'block', 'important');
                        } else {
                            card.style.setProperty('display', 'none', 'important');
                        }
                    });
                }
            </script>

            <a class="icon-link icon-link-hover col-md-4 offset-md-4" style="--bs-icon-link-transform: translate3d(0, -.125rem, 0); --bs-link-hover-color-rgb: 25, 135, 84;" href="" data-bs-toggle="collapse" data-bs-target="#collapseHelp" aria-expanded="false" aria-controls="collapseHelp">
                <i class="bi bi-question-circle-fill"></i>
                {{ __('Help') }}
            </a>
            <div class="mb-3 col-11">
                <div class="collapse collapse-vertical" id="collapseHelp">
                    <div class="card card-body">
                        <p><i class="bi bi-star-fill text-success"></i> {{ __('If You select multiple permissions, this role will have all of them.') }}</p>
                        <p><i class="bi bi-star-fill text-success"></i> {{ __('If You select a single permission, this role will only have that permission.') }}</p>
                        <p><i class="bi bi-star-fill text-success"></i> {{ __('For multiple permission select, please use the Ctrl key (Cmd key on Mac).') }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <x-button-success type="submit" wire:loading.attr="disabled" class="col-md-3 offset-md-5">
                    {{ __('Save') }}
                </x-button-success>
            </div>
        </form>
    </x-slot>

    <x-slot name="footer">
        <x-button-danger wire:click="$toggle('confirmingRole')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-dang-button>
    </x-slot>
</x-dialog-modal>
