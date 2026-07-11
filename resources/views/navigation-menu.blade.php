<nav id="navbar" class="navbar navbar-expand-lg fixed-top bg-body-tertiary z-3">
    <div class="container-fluid">
        <a wire:navigate.hover wire:current="active" href="/" class="navbar-brand d-flex align-items-center mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <!-- Logo -->
            <x-application-mark class="sidebar-logo" style="height: 2rem !important; width: 3rem !important;" />
            {{-- {!! siteUrlSettings('site_name') !!} --}}
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>


        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <button id="toggleSidebar" class="d-none d-lg-block btn btn-sm btn-light ms-2"><i class="bi bi-arrow-left-square"></i></button>

            <ul class="navbar-nav ms-auto">
                <li class="nav-item d-lg-none">
                    {{-- <a href="{{ route('dashboard.index') }}" class="nav-link link-body-emphasis {{ request()->routeIs('dashboard') ? 'active' : '' }}"> --}}
                        <a wire:navigate.hover wire:current="active" href="" class="nav-link link-body-emphasis">
                        <i class="bi bi-pie-chart-fill me-2"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <!-- Teams Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="teamDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->currentTeam->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="teamDropdown">
                            <li class="dropdown-header">{{ __('Manage Team') }}</li>
                            <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">{{ __('Team Settings') }}</a></li>
                            @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('teams.create') }}">{{ __('Create New Team') }}</a></li>
                            @endcan
                            @if (Auth::user()->allTeams()->count() > 1)
                                <li><hr class="dropdown-divider"></li>
                                <li class="dropdown-header">{{ __('Switch Teams') }}</li>
                                @foreach (Auth::user()->allTeams() as $team)
                                    <li><x-switchable-team :team="$team" /></li>
                                @endforeach
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="rounded-circle" width="30" height="30">
                        @else
                            {{ Auth::user()->name }}
                        @endif
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li class="dropdown-header">{{ __('Manage Account') }}</li>
                        <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('profile.show') }}">{{ __('Profile') }}</a></li>
                        @canany(['create-role', 'edit-role', 'delete-role'])
                            <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('roles.index') }}">{{ __('Manage Role') }}</a></li>
                        @endcanany

                        @canany(['create-user', 'edit-user', 'delete-user'])
                            <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('users.index') }}">{{ __('Manage User') }}</a></li>
                        @endcanany
                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <li><a wire:navigate.hover wire:current="active" class="dropdown-item" href="{{ route('api-tokens.index') }}">{{ __('API Tokens') }}</a></li>
                        @endif
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">{{ __('Log Out') }}</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
