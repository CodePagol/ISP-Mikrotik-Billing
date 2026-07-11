<x-app-layout>
    <x-slot name="header">
        {{ __('Profile Dashboard') }}
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" style="max-width: 10rem; min-width: 10rem; max-height: 10rem; min-height: 10rem;" alt="Profile Image" class="profileImagePreview rounded-circle">
                        <h3>{{ Auth::user()->name }}</h3> 
                        @if(Auth::user()->roles->isNotEmpty())
                            <h4>Act as : 
                                @foreach (Auth::user()->roles as $role)
                                    {{ $role->name }}
                                    @if (!$loop->last)
                                        ,
                                    @endif
                                @endforeach
                            </h4>
                        @else
                            <p class="p-0 m-0 pt-2">Hi, Your Registration is Completed.</p>
                            <p class="p-1 m-0 fw-semibold">But No Role Assigned.</p>
                            <p class="p-0 m-0">Wait for Admin Approval.</p>
                            <p class="p-0 m-0">OR Contact Super Admin for More Information</p>
                        @endif

                        <div class="social-links mt-2">
                            <a wire:navigate.hover wire:current="active" href="#" class="twitter"><i class="bi bi-twitter"></i></a>
                            <a wire:navigate.hover wire:current="active" href="#" class="facebook"><i class="bi bi-facebook"></i></a>
                            <a wire:navigate.hover wire:current="active" href="#" class="instagram"><i class="bi bi-instagram"></i></a>
                            <a wire:navigate.hover wire:current="active" href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="row pt-3 g-1">
                        @canany(['create-role', 'edit-role', 'delete-role'])
                            <a wire:navigate.hover wire:current="active" class="btn btn-primary col-md mx-1" href="{{ route('admin-roles') }}">
                                <i class="fa-solid fa-users-gear"></i> Manage Roles</a>
                        @endcanany
                        @canany(['create-user', 'edit-user', 'delete-user'])
                            <a wire:navigate.hover wire:current="active" class="btn btn-success col-md mx-1" href="{{ route('admin-users') }}">
                                <i class="fa-solid fa-user-gear"></i> Manage Users</a>
                        @endcanany
                        @canany(['create', 'edit', 'delete','view'])
                            <a wire:navigate.hover wire:current="active" class="btn btn-info col-md mx-1" href="{{ route('dashboard') }}"><i class="fa-brands fa-squarespace"></i> {{ __('Dashboard') }}</a>
                        @endcanany
                    </div>
                </div>

                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-body pt-3">
                            <!-- Bordered Tabs -->
                            <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-overview" aria-selected="true" role="tab">Overview</button>
                                </li>
                                @if (Laravel\Fortify\Features::canUpdateProfileInformation() || Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-edit" aria-selected="false" tabindex="-1" role="tab">Edit Profile</button>
                                    </li>
                                @endif
                                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password" aria-selected="false" tabindex="-1" role="tab">Change Password</button>
                                    </li>
                                @endif
                                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-two-factor" aria-selected="false" tabindex="-1" role="tab">Two Factor</button>
                                    </li>
                                @endif
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-browser-sessions" aria-selected="false" tabindex="-1" role="tab">Browser Sessions</button>
                                </li>
                            </ul>

                            <div class="tab-content pt-2">
                                <div class="p-3 tab-pane fade show active" id="profile-overview" role="tabpanel">
                                    <fieldset class="scheduler-border">
                                        <legend class="scheduler-border fs-4">{{ __('Profile Details') }}</legend>
                                        
                                        <div class="row card-body">
                                            @foreach(Auth::user()->roles as $role)
                                                <li class="label fw-bold">{{ $role->name }} Role: Yes</li>
                                            @endforeach 

                                            @if(Auth::user()->hasRole('Super Admin'))
                                                @foreach (\Spatie\Permission\Models\Permission::pluck('name') as $permission)
                                                    <li>{{ $permission }} permission: Yes</li>
                                                @endforeach
                                            @else
                                                @foreach(Auth::user()->getPermissionsViaRoles()->pluck('name') as $permission)
                                                    <li>{{ $permission }} permission: Yes</li>
                                                @endforeach
                                            @endif
                                        </div>
                                    </fieldset>
                                </div>
                                @if (Laravel\Fortify\Features::canUpdateProfileInformation() || Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                    <div class="p-3 tab-pane fade" id="profile-edit" role="tabpanel">
                                        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                            @livewire('profile.update-profile-information-form')
                                            <x-section-border />
                                        @endif
                                        @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                                            @livewire('profile.delete-user-form')
                                        @endif
                                    </div>
                                @endif
                                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                    <div class="p-3 tab-pane fade" id="profile-change-password" role="tabpanel">
                                            @livewire('profile.update-password-form')
                                    </div>
                                @endif
                                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                                    <div class="p-3 tab-pane fade" id="profile-two-factor" role="tabpanel">
                                            @livewire('profile.two-factor-authentication-form')
                                    </div>
                                @endif
                                <div class="p-3 tab-pane fade" id="profile-browser-sessions" role="tabpanel">
                                    {{-- @if (Laravel\Jetstream\Jetstream::hasSessionFeatures()) --}}
                                        @livewire('profile.logout-other-browser-sessions-form')
                                    {{-- @endif --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="mx-auto">
        </div>
    </div>
</x-app-layout>
