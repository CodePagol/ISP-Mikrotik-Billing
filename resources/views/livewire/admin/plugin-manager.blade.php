<div>
    <div class="container-fluid py-4">

        {{-- Page Header --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-puzzle-fill"></i>
                    </span>
                    {{ __('Plugin Manager') }}
                </h4>
                <p class="text-muted small mb-0">
                    {{ __('Install & manage extensions via ZIP upload, GitHub clone, or Composer package.') }}
                </p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button wire:click="checkUpdates(true)" wire:loading.attr="disabled"
                    class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-semibold shadow-sm" title="{{ __('Check GitHub for new updates') }}">
                    <span wire:loading.remove wire:target="checkUpdates">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ __('Check for Updates') }}
                    </span>
                    <span wire:loading wire:target="checkUpdates">
                        <span class="spinner-border spinner-border-sm me-1"></span> {{ __('Checking...') }}
                    </span>
                </button>
                <button wire:click="$set('activeTab', 'installed')" 
                    class="btn btn-sm {{ $activeTab === 'installed' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-3 px-3 fw-semibold shadow-sm">
                    <i class="bi bi-grid-fill me-1"></i> {{ __('Installed Plugins') }} 
                    <span class="badge bg-white text-dark ms-1">{{ count($plugins) }}</span>
                </button>
                <button wire:click="$set('activeTab', 'marketplace')" 
                    class="btn btn-sm {{ $activeTab === 'marketplace' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-3 px-3 fw-semibold shadow-sm">
                    <i class="bi bi-shop me-1 text-warning"></i> {{ __('Marketplace') }}
                    <span class="badge {{ $activeTab === 'marketplace' ? 'bg-white text-dark' : 'bg-success text-white' }} ms-1">Store</span>
                </button>
                <button wire:click="$set('activeTab', 'install')" 
                    class="btn btn-sm {{ $activeTab === 'install' ? 'btn-primary' : 'btn-outline-primary' }} rounded-3 px-3 fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> {{ __('Upload / Custom') }}
                </button>
                <button wire:click="$set('activeTab', 'docs')" 
                    class="btn btn-sm {{ $activeTab === 'docs' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-3 px-3 fw-semibold shadow-sm">
                    <i class="bi bi-code-slash me-1"></i> {{ __('Developer Guide') }}
                </button>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if ($successMessage)
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div class="flex-grow-1">{{ $successMessage }}</div>
                <button type="button" class="btn-close" wire:click="$set('successMessage', null)"></button>
            </div>
        @endif

        @if ($errorMessage)
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div class="flex-grow-1">{{ $errorMessage }}</div>
                <button type="button" class="btn-close" wire:click="$set('errorMessage', null)"></button>
            </div>
        @endif

        {{-- Console Output Viewer (for Git / Composer commands) --}}
        @if ($consoleOutput)
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-dark text-light overflow-hidden">
                <div class="card-header bg-black bg-opacity-50 py-2.5 px-3 border-bottom border-secondary d-flex align-items-center justify-content-between">
                    <div class="small font-monospace text-secondary d-flex align-items-center gap-2">
                        <span class="badge bg-secondary p-1"></span>
                        <i class="bi bi-terminal-fill text-warning"></i> {{ __('Process Console Output') }}
                    </div>
                    <button type="button" wire:click="$set('consoleOutput', null)" class="btn btn-sm btn-link text-secondary p-0 text-decoration-none">
                        <i class="bi bi-x-lg"></i> {{ __('Dismiss') }}
                    </button>
                </div>
                <div class="card-body p-3 font-monospace small" style="max-height: 220px; overflow-y: auto; font-size: 0.82rem; white-space: pre-wrap;">{{ $consoleOutput }}</div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: INSTALLED PLUGINS                                           --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'installed')
            {{-- Core System Update Notification Banner --}}
            @if (!empty($coreUpdateInfo['has_update']))
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border-left: 5px solid #2563eb !important;">
                    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(37, 99, 235, 0.12) !important;">
                                <i class="bi bi-arrow-up-circle-fill fs-4" style="color: #2563eb !important;"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="fw-bold mb-0 text-dark">{{ __('ISP Mikrotik Billing System Update Available!') }}</h6>
                                    <span class="badge badge-subtle-primary rounded-pill fw-semibold">v{{ $coreUpdateInfo['latest_version'] }}</span>
                                </div>
                                <p class="text-muted small mb-0">
                                    {{ __('Your current version is v:curr. A newer system release is ready with new features and fixes.', ['curr' => $coreUpdateInfo['current_version']]) }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button wire:click="openCoreChangelog" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold">
                                <i class="bi bi-journal-text me-1"></i> {{ __('View Changelog & Update') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (count($plugins) === 0)
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <div class="card-body">
                        <div class="p-4 bg-light rounded-circle d-inline-flex mb-3">
                            <i class="bi bi-puzzle text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="fw-bold text-dark">{{ __('No Plugins Installed Yet') }}</h5>
                        <p class="text-muted small mx-auto" style="max-width: 440px;">
                            {{ __('Install your first plugin using a ZIP file, GitHub repository link, or Composer package.') }}
                        </p>
                        <button wire:click="$set('activeTab', 'install')" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> {{ __('Install a Plugin Now') }}
                        </button>
                    </div>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($plugins as $plugin)
                        <div class="col-md-6 col-xl-4">
                            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column transition-all hover-shadow">
                                <div class="card-body p-4 d-flex flex-column">
                                    {{-- Top: Icon + Title + Version --}}
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-3 p-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                                                <i class="bi bi-plugin fs-3"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0">{{ $plugin['name'] }}</h6>
                                                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                    <span class="badge badge-subtle-secondary border font-monospace" style="font-size: 0.72rem;">
                                                        v{{ $plugin['version'] }}
                                                    </span>
                                                    @if (!empty($plugin['update_info']['has_update']))
                                                        <button wire:click="openPluginChangelog('{{ $plugin['id'] }}')" class="btn btn-xs p-0 border-0" style="background:none;">
                                                            <span class="badge badge-subtle-warning text-dark border border-warning rounded-pill px-2 py-0.5 cursor-pointer" style="font-size: 0.68rem;" title="{{ __('Click to view changelog') }}">
                                                                <i class="bi bi-arrow-up-circle-fill text-warning me-1"></i> v{{ $plugin['update_info']['latest_version'] }} {{ __('Available') }}
                                                            </span>
                                                        </button>
                                                    @else
                                                        <span class="badge badge-subtle-success rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                                            <i class="bi bi-check-circle me-1"></i> {{ __('Up to date') }}
                                                        </span>
                                                    @endif
                                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                                        by <strong>{{ $plugin['author'] }}</strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- License Badge --}}
                                        @if (! $plugin['requires_license'])
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> {{ __('Free') }}
                                            </span>
                                        @elseif ($plugin['license_valid'])
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-patch-check-fill me-1"></i> {{ __('Licensed') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-shield-x me-1"></i> {{ __('Invalid Key') }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Description --}}
                                    <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.5;">
                                        {{ $plugin['description'] ?: __('No description provided.') }}
                                    </p>

                                    {{-- Metadata Tags --}}
                                    <div class="d-flex flex-wrap gap-1 mb-3 pt-2 border-top">
                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                            <i class="bi bi-folder me-1"></i> plugins/{{ $plugin['id'] }}
                                        </span>
                                        @if ($plugin['is_git_repo'])
                                            <span class="badge bg-dark text-white" style="font-size: 0.7rem;">
                                                <i class="bi bi-git me-1"></i> Git Repo
                                            </span>
                                        @endif
                                        @if ($plugin['has_migrations'])
                                            <span class="badge bg-info-subtle text-info border" style="font-size: 0.7rem;">
                                                <i class="bi bi-database me-1"></i> DB Migrations
                                            </span>
                                        @endif
                                        @if ($plugin['has_routes'])
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.7rem;">
                                                <i class="bi bi-signpost me-1"></i> Routes
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Actions Footer --}}
                                    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-auto">
                                        <div class="d-flex gap-1 flex-wrap align-items-center">
                                            @if (!empty($plugin['update_info']['has_update']))
                                                <button wire:click="openPluginChangelog('{{ $plugin['id'] }}')" 
                                                    title="{{ __('View changelog and update plugin') }}"
                                                    class="btn btn-sm btn-warning rounded-pill px-3 py-1 fw-bold shadow-sm" style="font-size: 0.78rem;">
                                                    <i class="bi bi-arrow-up-circle-fill me-1"></i> {{ __('Update: v:ver', ['ver' => $plugin['update_info']['latest_version']]) }}
                                                </button>
                                            @endif

                                            @if ($plugin['has_migrations'])
                                                <button wire:click="runMigrations('{{ $plugin['id'] }}')" 
                                                    title="{{ __('Run plugin database migrations') }}"
                                                    class="btn btn-sm btn-outline-secondary rounded-3 px-2 py-1" style="font-size: 0.78rem;">
                                                    <i class="bi bi-database-fill-gear"></i> {{ __('Migrate') }}
                                                </button>
                                            @endif

                                            @if ($plugin['requires_license'])
                                                <button wire:click="openLicenseModal('{{ $plugin['id'] }}')" 
                                                    class="btn btn-sm btn-outline-warning rounded-3 px-2 py-1" style="font-size: 0.78rem;">
                                                    <i class="bi bi-key-fill"></i> {{ __('License') }}
                                                </button>
                                            @endif
                                        </div>

                                        <button wire:click="uninstallPlugin('{{ $plugin['id'] }}')"
                                            onclick="confirm('{{ __('Are you sure you want to uninstall and delete :plugin? This action cannot be undone.', ['plugin' => $plugin['name']]) }}') || event.stopImmediatePropagation()"
                                            class="btn btn-sm btn-outline-danger rounded-3 px-2 py-1" style="font-size: 0.78rem;" title="{{ __('Uninstall & Delete Plugin') }}">
                                            <i class="bi bi-trash3-fill"></i> {{ __('Uninstall') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB: PLUGIN MARKETPLACE (1-CLICK INSTALL STORE)                    --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'marketplace')
            {{-- Search & Filter Bar --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-5 col-lg-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-secondary">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       wire:model.live.debounce.300ms="marketplaceSearch" 
                                       class="form-control border-start-0 ps-0 shadow-none" 
                                       placeholder="{{ __('Search plugins by name, keyword, or tag...') }}">
                                @if ($marketplaceSearch)
                                    <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('marketplaceSearch', '')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-lg-4">
                            <select wire:model.live="marketplaceCategory" class="form-select shadow-none">
                                <option value="all">{{ __('All Categories') }}</option>
                                <option value="Hardware & Inventory">{{ __('Hardware & Inventory') }}</option>
                                <option value="Networking & OLT">{{ __('Networking & OLT') }}</option>
                                <option value="Billing & Finance">{{ __('Billing & Finance') }}</option>
                            </select>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2 text-end">
                            <button wire:click="refreshMarketplace" wire:loading.attr="disabled" class="btn btn-outline-secondary w-100 rounded-3 fw-semibold">
                                <span wire:loading.remove wire:target="refreshMarketplace">
                                    <i class="bi bi-arrow-clockwise me-1"></i> {{ __('Refresh') }}
                                </span>
                                <span wire:loading wire:target="refreshMarketplace">
                                    <span class="spinner-border spinner-border-sm me-1"></span>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Marketplace Grid --}}
            @if (empty($this->marketplacePlugins))
                <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                    <div class="card-body py-5">
                        <div class="mb-3">
                            <i class="bi bi-search text-secondary display-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark">{{ __('No plugins found') }}</h5>
                        <p class="text-secondary mb-3">{{ __('Try adjusting your search criteria or refresh the catalog.') }}</p>
                        @if ($marketplaceSearch || $marketplaceCategory !== 'all')
                            <button wire:click="$set('marketplaceSearch', ''); $set('marketplaceCategory', 'all');" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                {{ __('Reset Filters') }}
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="row g-3 g-md-4">
                    @foreach ($this->marketplacePlugins as $item)
                        @php
                            $isInstalled = $item['is_installed'] ?? false;
                            $updateAvailable = $item['update_available'] ?? false;
                            $installedVer = $item['installed_version'] ?? null;
                            $catalogVer = $item['version'] ?? '1.0.0';
                            $slug = $item['id'] ?? '';
                            $isInstallingThis = ($installingPluginId === $slug);
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4 d-flex">
                            <div class="card border-0 shadow-sm rounded-4 w-100 d-flex flex-column transition-all" 
                                 style="transition: transform 0.2s, box-shadow 0.2s;">
                                <div class="card-body p-4 d-flex flex-column">
                                    {{-- Card Header: Icon, Title, Version --}}
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="rounded-3 bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" 
                                             style="width: 52px; height: 52px; font-size: 1.6rem;">
                                            <i class="bi {{ $item['icon'] ?? 'bi-plugin' }}"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate" title="{{ $item['name'] }}">
                                                    {{ $item['name'] }}
                                                </h6>
                                                <span class="badge bg-light text-dark font-monospace border flex-shrink-0">
                                                    v{{ $catalogVer }}
                                                </span>
                                            </div>
                                            <div class="small text-secondary mt-1">
                                                @if (!empty($item['author_url']))
                                                    <a href="{{ $item['author_url'] }}" target="_blank" class="text-decoration-none text-muted">
                                                        <i class="bi bi-person me-1"></i>{{ $item['author'] ?? 'Code Pagol' }}
                                                    </a>
                                                @else
                                                    <span><i class="bi bi-person me-1"></i>{{ $item['author'] ?? 'Code Pagol' }}</span>
                                                @endif
                                                @if (!empty($item['category']))
                                                    <span class="mx-1">•</span>
                                                    <span class="text-primary fw-medium">{{ $item['category'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.5;">
                                        {{ $item['description'] ?? '' }}
                                    </p>

                                    {{-- Tags --}}
                                    @if (!empty($item['tags']) && is_array($item['tags']))
                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                            @foreach (array_slice($item['tags'], 0, 4) as $tag)
                                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                                    #{{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Action Footer --}}
                                    <div class="pt-3 border-top mt-auto d-flex align-items-center justify-content-between gap-2">
                                        <div>
                                            @if ($item['is_free'] ?? true)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="bi bi-check2 me-1"></i>{{ __('Free / Official') }}
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                    {{ __('Premium') }}
                                                </span>
                                            @endif
                                        </div>

                                        <div>
                                            @if ($isInstalled)
                                                @if ($updateAvailable)
                                                    <button wire:click="openPluginChangelog('{{ $slug }}')" 
                                                            class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm fw-semibold">
                                                        <i class="bi bi-arrow-up-circle me-1"></i> {{ __('Update v') }}{{ $catalogVer }}
                                                    </button>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                                        <i class="bi bi-check-circle-fill me-1"></i> {{ __('Installed') }}
                                                    </span>
                                                @endif
                                            @else
                                                <button wire:click="installFromMarketplace('{{ $slug }}')" 
                                                        wire:loading.attr="disabled"
                                                        class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold">
                                                    @if ($isInstallingThis)
                                                        <span class="spinner-border spinner-border-sm me-1"></span> {{ __('Installing...') }}
                                                    @else
                                                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> {{ __('Install Now') }}
                                                    @endif
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: INSTALL PLUGIN (3 METHODS: ZIP, GIT, COMPOSER)             --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'install')
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        {{-- Method Switcher Header --}}
                        <div class="card-header bg-white py-3 px-4 border-0 border-bottom">
                            <h5 class="fw-bold text-dark mb-3">
                                <i class="bi bi-plus-circle-fill text-primary me-2"></i>{{ __('Install New Plugin') }}
                            </h5>
                            
                            {{-- 3 Switcher Pills --}}
                            <div class="btn-group w-100 rounded-3 p-1 bg-light border" role="group">
                                <button type="button" wire:click="$set('installMethod', 'zip')" 
                                    class="btn btn-sm rounded-3 fw-semibold {{ $installMethod === 'zip' ? 'btn-white bg-white text-primary shadow-sm' : 'text-muted' }}">
                                    <i class="bi bi-file-earmark-zip me-1"></i> {{ __('1. Upload ZIP') }}
                                </button>
                                <button type="button" wire:click="$set('installMethod', 'git')" 
                                    class="btn btn-sm rounded-3 fw-semibold {{ $installMethod === 'git' ? 'btn-white bg-white text-dark shadow-sm' : 'text-muted' }}">
                                    <i class="bi bi-github me-1"></i> {{ __('2. GitHub / Git URL') }}
                                </button>
                                <button type="button" wire:click="$set('installMethod', 'composer')" 
                                    class="btn btn-sm rounded-3 fw-semibold {{ $installMethod === 'composer' ? 'btn-white bg-white text-danger shadow-sm' : 'text-muted' }}">
                                    <i class="bi bi-terminal me-1"></i> {{ __('3. Composer Package') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body p-4">

                            {{-- ── 1. METHOD: ZIP UPLOAD ──────────────────────────────── --}}
                            @if ($installMethod === 'zip')
                                <form wire:submit.prevent="installZip">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">{{ __('Plugin ZIP File') }} <span class="text-danger">*</span></label>
                                        <div class="border border-2 border-dashed rounded-4 p-4 text-center bg-light">
                                            <i class="bi bi-file-earmark-zip text-primary" style="font-size: 2.8rem;"></i>
                                            <div class="mt-2">
                                                <input type="file" wire:model="zipFile" id="zipFile" class="form-control" accept=".zip,application/zip,application/x-zip-compressed">
                                            </div>
                                            <div class="text-muted small mt-2">
                                                {{ __('ZIP archive must contain Plugin.php and plugin.json at root. Max size: 20MB.') }}
                                            </div>
                                            @error('zipFile')
                                                <div class="text-danger small mt-2 fw-semibold">{{ $message }}</div>
                                            @enderror

                                            <div wire:loading wire:target="zipFile" class="text-primary small mt-2">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                {{ __('Uploading file...') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold text-dark">
                                            {{ __('License Key (Optional)') }}
                                            <span class="text-muted fw-normal small">({{ __('If provided by author') }})</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-key-fill text-muted"></i></span>
                                            <input type="text" wire:model="uploadLicenseKey" class="form-control" placeholder="e.g. OLT-XXXX-YYYY-ZZZZ">
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                        <button type="button" wire:click="$set('activeTab', 'installed')" class="btn btn-outline-secondary rounded-3 px-3">
                                            {{ __('Cancel') }}
                                        </button>
                                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="installZip">
                                                <i class="bi bi-upload me-1"></i> {{ __('Upload & Install') }}
                                            </span>
                                            <span wire:loading wire:target="installZip">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                {{ __('Extracting & Installing...') }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif

                            {{-- ── 2. METHOD: GIT / GITHUB CLONE ──────────────────────── --}}
                            @if ($installMethod === 'git')
                                <form wire:submit.prevent="installFromGit">
                                    <div class="alert alert-info border-0 rounded-3 py-2 small mb-4">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        {{ __('Directly clone and install from GitHub, GitLab, or any Git URL. The repository must have Plugin.php and plugin.json.') }}
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-dark">
                                            {{ __('Git Repository URL') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-github"></i></span>
                                            <input type="text" wire:model="gitUrl" class="form-control font-monospace" placeholder="https://github.com/author/plugin-name.git" required>
                                        </div>
                                        @error('gitUrl')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted d-block mt-1">
                                            {{ __('Example: https://github.com/rohan9222/isp-olt-plugin.git') }}
                                        </small>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark">
                                                {{ __('Branch / Tag (Optional)') }}
                                            </label>
                                            <input type="text" wire:model="gitBranch" class="form-control font-monospace" placeholder="main (default)">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold text-dark">
                                                {{ __('License Key (Optional)') }}
                                            </label>
                                            <input type="text" wire:model="gitLicenseKey" class="form-control" placeholder="Optional license key...">
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                        <button type="button" wire:click="$set('activeTab', 'installed')" class="btn btn-outline-secondary rounded-3 px-3">
                                            {{ __('Cancel') }}
                                        </button>
                                        <button type="submit" class="btn btn-dark rounded-3 px-4 fw-semibold" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="installFromGit">
                                                <i class="bi bi-git me-1"></i> {{ __('Clone & Install from Git') }}
                                            </span>
                                            <span wire:loading wire:target="installFromGit">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                {{ __('Cloning repository...') }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif

                            {{-- ── 3. METHOD: COMPOSER REQUIRE ────────────────────────── --}}
                            @if ($installMethod === 'composer')
                                <form wire:submit.prevent="installFromComposer">
                                    <div class="alert alert-warning border-0 rounded-3 py-2 small mb-4">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        {{ __('Runs "composer require <package>". This may take 30-90 seconds while Composer resolves dependencies.') }}
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-dark">
                                            {{ __('Composer Package Name') }} <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light font-monospace">composer require</span>
                                            <input type="text" wire:model="composerPackage" class="form-control font-monospace" placeholder="vendor/package-name" required>
                                        </div>
                                        @error('composerPackage')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted d-block mt-1">
                                            {{ __('Example: codepagol/olt-manager or codepagol/olt-manager:^1.0') }}
                                        </small>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                                        <button type="button" wire:click="$set('activeTab', 'installed')" class="btn btn-outline-secondary rounded-3 px-3">
                                            {{ __('Cancel') }}
                                        </button>
                                        <button type="submit" class="btn btn-danger rounded-3 px-4 fw-semibold" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="installFromComposer">
                                                <i class="bi bi-terminal-fill me-1"></i> {{ __('Run Composer Require') }}
                                            </span>
                                            <span wire:loading wire:target="installFromComposer">
                                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                                {{ __('Running Composer Require (please wait)...') }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: DEVELOPER GUIDE                                             --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($activeTab === 'docs')
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-dark mb-3">
                                <i class="bi bi-book-half text-primary me-2"></i>{{ __('How to Create & Distribute Plugins') }}
                            </h5>
                            <p class="text-muted">
                                {{ __('This system follows an extensible architecture similar to WordPress. Developers can create plugins to add payment gateways, OLT management, WhatsApp notifications, or custom dashboard widgets.') }}
                            </p>

                            <hr class="my-4">

                            <h6 class="fw-bold text-dark mb-2">1. Scaffold a New Plugin (CLI)</h6>
                            <div class="bg-dark text-light p-3 rounded-3 font-monospace small mb-3">
                                php artisan plugin:make my-new-plugin --author="Your Name"
                            </div>

                            <h6 class="fw-bold text-dark mb-2">2. Plugin Directory Structure</h6>
                            <div class="bg-light p-3 rounded-3 font-monospace small mb-3 border">
plugins/my-new-plugin/<br>
├── plugin.json &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# Metadata (name, version, author, license requirements)<br>
├── Plugin.php &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# Main entry class extending AbstractPlugin<br>
├── routes/web.php &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# (Optional) Web routes<br>
├── resources/views/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;# (Optional) Blade templates<br>
└── database/migrations/ &nbsp;&nbsp;&nbsp;# (Optional) Database tables
                            </div>

                            <h6 class="fw-bold text-dark mb-2">3. Hooking into Events & Filters</h6>
                            <div class="bg-dark text-light p-3 rounded-3 font-monospace small mb-3">
use App\Support\Hooks;<br><br>
// Listen to events (Actions)<br>
Hooks::action('customer.created', function($customer) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;// Send WhatsApp or SMS via plugin<br>
});<br><br>
// Modify data (Filters)<br>
Hooks::filter('customer.table.columns', function($columns) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;$columns['olt_status'] = 'OLT Signal';<br>
&nbsp;&nbsp;&nbsp;&nbsp;return $columns;<br>
});
                            </div>

                            <h6 class="fw-bold text-dark mb-2">4. Distributing to Users</h6>
                            <ul class="text-muted small mb-0">
                                <li><strong>Option A (ZIP):</strong> Zip the plugin folder and send it to users. They can upload it in the "Upload ZIP" tab.</li>
                                <li><strong>Option B (GitHub):</strong> Push the plugin to a public or private GitHub repository. Users can paste the Git URL in the "GitHub Clone" tab and 1-click install.</li>
                                <li><strong>Option C (Composer):</strong> Publish on Packagist and users can install via "Composer Package".</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- LICENSE MODAL                                                      --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($selectedPluginSlug)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold text-dark">
                                <i class="bi bi-key-fill text-warning me-2"></i>{{ __('License Key Activation') }}
                            </h5>
                            <button type="button" class="btn-close" wire:click="$set('selectedPluginSlug', '')"></button>
                        </div>
                        <div class="modal-body py-4">
                            <p class="text-muted small mb-3">
                                {{ __('Enter the license key for plugin:') }} <strong>{{ $selectedPluginSlug }}</strong>
                            </p>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-shield-lock-fill text-muted"></i></span>
                                <input type="text" wire:model="licenseKeyInput" class="form-control" placeholder="Enter license key...">
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary rounded-3" wire:click="$set('selectedPluginSlug', '')">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button" class="btn btn-primary rounded-3 px-4 fw-semibold" wire:click="saveLicenseKey">
                                <i class="bi bi-check2 me-1"></i> {{ __('Save & Activate') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- CHANGELOG & UPDATE MODAL                                           --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        @if ($showChangelogModal && $activeChangelog)
            <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); z-index: 1055;">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        {{-- Modal Header --}}
                        <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                            <div>
                                <span class="badge bg-primary text-uppercase px-2 py-0.5 mb-1" style="font-size: 10px;">
                                    {{ $activeChangelog['type'] === 'core' ? __('System Core Update') : __('Plugin Update') }}
                                </span>
                                <h5 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-rocket-takeoff-fill text-warning"></i>
                                    {{ $activeChangelog['name'] }}
                                </h5>
                            </div>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeChangelogModal"></button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="modal-body p-4" style="max-height: 65vh; overflow-y: auto;">
                            {{-- Version comparison pill --}}
                            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-4 rounded-3 border" style="background: #f8fafc;">
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 11px;">{{ __('Current Version') }}</small>
                                        <span class="badge badge-subtle-secondary rounded-pill px-2.5 py-1 font-monospace">
                                            v{{ $activeChangelog['current_version'] }}
                                        </span>
                                    </div>
                                    <i class="bi bi-arrow-right text-primary fs-5 mx-1"></i>
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 11px;">{{ __('New Version') }}</small>
                                        <span class="badge badge-subtle-success rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                            v{{ $activeChangelog['latest_version'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block" style="font-size: 11px;">{{ __('Release Date') }}</small>
                                    <span class="fw-semibold text-dark small">{{ $activeChangelog['release_date'] }}</span>
                                </div>
                            </div>

                            {{-- Changelog Content --}}
                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-journal-text text-primary"></i>
                                {{ __('What\'s New in this Release') }}
                            </h6>
                            <div class="p-3 rounded-3 border bg-light text-dark changelog-box" style="font-size: 13.5px; line-height: 1.6;">
                                {!! $activeChangelog['changelog_html'] !!}
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="modal-footer border-top bg-light py-3 px-4 d-flex justify-content-between">
                            @if (!empty($activeChangelog['html_url']))
                                <a href="{{ $activeChangelog['html_url'] }}" target="_blank" class="btn btn-sm btn-link text-muted text-decoration-none">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('View on GitHub') }}
                                </a>
                            @else
                                <div></div>
                            @endif

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" wire:click="closeChangelogModal">
                                    {{ __('Close') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm" wire:click="applyUpdate" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="applyUpdate">
                                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> {{ __('Update Now to v:ver', ['ver' => $activeChangelog['latest_version']]) }}
                                    </span>
                                    <span wire:loading wire:target="applyUpdate">
                                        <span class="spinner-border spinner-border-sm me-1"></span> {{ __('Updating, please wait...') }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
