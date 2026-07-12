<x-dialog-modal wire:model.live="confirmingUser" maxWidth="2xl" class="mt-2">
    <x-slot name="title">
        {{ __($userType) }}
    </x-slot>

    <x-slot name="content">
        <form wire:submit.prevent="submitUser" method="post">
            <div class="mb-3 row">
                <label for="name" class="col-md-4 col-form-label text-md-end text-start">{{ __('Name') }}</label>
                <div class="col-md-7">
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Name') }}" wire:model="name" autocomplete="name">
                    <x-error name="name" />
                </div>
            </div>

            <div class="mb-3 row">
                <label for="mobile" class="col-md-4 col-form-label text-md-end text-start">{{ __('Mobile') }}</label>
                <div class="col-md-7">
                    <div wire:ignore>
                        <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror" placeholder="{{ __('Mobile') }}" x-data="intlTelInput('mobile')" autocomplete="mobile" value="{{ $this->mobile ?? '' }}">
                    </div>
                    
                    <x-error name="mobile" />
                </div>
            </div>

            <div class="mb-3 row">
                <label for="email" class="col-md-4 col-form-label text-md-end text-start">{{ __('Email Address') }}</label>
                <div class="col-md-7">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('Email Address') }}" wire:model="email" autocomplete="email">
                    <x-error name="email" />
                </div>
            </div>

            <div class="mb-3 row">
                <label for="address" class="col-md-4 col-form-label text-md-end text-start">{{ __('Address') }}</label>
                <div class="col-md-7">
                    <textarea id="address" name="address" class="form-control @error('address') is-invalid @enderror" wire:model="address"></textarea>
                    <x-error name="address" />
                </div>
            </div>

            <div class="mb-3 row">
                <label for="password" class="col-md-4 col-form-label text-md-end text-start">{{ __('Password') }}</label>
                <div class="col-md-7">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('Password') }}" wire:model="password" autocomplete="new-password">
                    <x-error name="password" />
                </div>
            </div>

            <div class="mb-3 row">
                <label for="password_confirmation" class="col-md-4 col-form-label text-md-end text-start">{{ __('Confirm Password') }}</label>
                <div class="col-md-7">
                    <input id="password_confirmation" type="password" class="form-control" placeholder="{{ __('Confirm Password') }}" wire:model="password_confirmation" autocomplete="new-password">
                </div>
            </div>

            <div class="mb-3 row">
                <label class="col-md-4 col-form-label text-md-end text-start fw-semibold text-dark">{{ __('Roles') }} <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <div class="row g-2">
                        @foreach ($userRoles ?? [] as $roleName)
                            @php
                                $isSelected = in_array($roleName, $roles ?? []);
                                $isSuperAdminSelected = in_array('Super Admin', $roles ?? []);
                                $isDisabled = $isSuperAdminSelected && $roleName !== 'Super Admin';
                            @endphp
                            <div class="col-sm-6">
                                <label class="card h-100 border rounded-3 p-1 mb-0 cursor-pointer transition-all position-relative overflow-hidden {{ $isDisabled ? 'opacity-50 border-light bg-light' : ($isSelected ? 'border-primary bg-primary-subtle bg-opacity-25' : 'border-light bg-light bg-opacity-50') }}" 
                                       style="user-select: none; transition: all 0.2s ease-in-out; {{ $isDisabled ? 'pointer-events: none;' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <span class="d-inline-flex align-items-center justify-content-center rounded-2 p-1.5 {{ $isSelected ? 'bg-primary text-white' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="width: 28px; height: 28px;">
                                                <i class="bi {{ $isSelected ? 'bi-person-badge-fill' : 'bi-person-badge' }}" style="font-size: 0.9rem;"></i>
                                            </span>
                                            <span class="fw-semibold text-truncate text-dark" style="font-size: 0.8rem;">{{ __($roleName) }}</span>
                                        </div>
                                        <input class="form-check-input" type="checkbox" value="{{ $roleName }}" wire:model.live="roles" style="display: none;" {{ $isDisabled ? 'disabled' : '' }}>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <x-error name="roles" />
                </div>
            </div>

            <div class="mb-3 row">
                <input type="submit" class="col-md-3 offset-md-5 btn btn-primary" value="{{ __('Submit') }}">
            </div>
        </form>
    </x-slot>

    <x-slot name="footer">
        <x-button-danger wire:click="$toggle('confirmingUser')" wire:loading.attr="disabled">
            {{ __('Cancel') }}
        </x-dang-button>
    </x-slot>
</x-dialog-modal>
