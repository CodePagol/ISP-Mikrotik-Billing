<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ── Tab Bar ────────────────────────────────────────────────────── --}}
        <div class="flex gap-2 border-b border-gray-200 dark:border-gray-700">
            <button
                wire:click="$set('activeTab', 'installed')"
                @class([
                    'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'installed',
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' => $activeTab !== 'installed',
                ])
            >
                <x-heroicon-o-puzzle-piece class="inline w-4 h-4 mr-1" />
                Installed Plugins
                <span class="ml-1 text-xs bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">
                    {{ count($installedPlugins) }}
                </span>
            </button>

            <button
                wire:click="$set('activeTab', 'upload')"
                @class([
                    'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'upload',
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' => $activeTab !== 'upload',
                ])
            >
                <x-heroicon-o-arrow-up-tray class="inline w-4 h-4 mr-1" />
                Upload ZIP
            </button>

            <button
                wire:click="$set('activeTab', 'license')"
                @class([
                    'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'license',
                    'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400' => $activeTab !== 'license',
                ])
            >
                <x-heroicon-o-key class="inline w-4 h-4 mr-1" />
                Activate License Key
            </button>
        </div>

        {{-- ── Tab: Installed Plugins ─────────────────────────────────────── --}}
        @if($activeTab === 'installed')
            @if(empty($installedPlugins))
                <div class="text-center py-16 text-gray-400">
                    <x-heroicon-o-puzzle-piece class="mx-auto w-12 h-12 mb-4 opacity-30" />
                    <p class="text-lg font-medium">No plugins installed yet</p>
                    <p class="text-sm mt-1">Upload a ZIP or activate a license key to get started.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($installedPlugins as $plugin)
                        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                            {{-- Card Header --}}
                            <div class="p-4 flex items-start gap-3">
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center">
                                    <x-heroicon-o-puzzle-piece class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $plugin['name'] }}
                                        </h3>
                                        <span class="text-xs text-gray-400">v{{ $plugin['version'] }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">by {{ $plugin['author'] }}</p>
                                </div>

                                {{-- Status badge --}}
                                @if($plugin['requires_license'])
                                    @if($plugin['license_valid'])
                                        <span class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">
                                            <x-heroicon-m-check-circle class="w-3 h-3" /> Licensed
                                        </span>
                                    @else
                                        <span class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded-full">
                                            <x-heroicon-m-x-circle class="w-3 h-3" /> No License
                                        </span>
                                    @endif
                                @else
                                    <span class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                                        Free
                                    </span>
                                @endif
                            </div>

                            {{-- Description --}}
                            @if($plugin['description'])
                                <p class="px-4 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $plugin['description'] }}
                                </p>
                            @endif

                            {{-- Footer Actions --}}
                            <div class="px-4 py-3 mt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-2">
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    wire:click="runMigrations('{{ $plugin['id'] }}')"
                                    icon="heroicon-m-arrow-path"
                                >
                                    Run Migrations
                                </x-filament::button>

                                <span class="text-xs text-gray-400 ml-auto">
                                    ID: <code class="font-mono">{{ $plugin['id'] }}</code>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        {{-- ── Tab: Upload ZIP ─────────────────────────────────────────────── --}}
        @elseif($activeTab === 'upload')
            <div class="max-w-xl">
                <x-filament::section
                    heading="Install Plugin from ZIP"
                    description="Upload a plugin package (.zip) to install it. The ZIP must contain Plugin.php and plugin.json at its root."
                    icon="heroicon-o-arrow-up-tray"
                >
                    <form wire:submit="installFromZip" class="space-y-4">
                        {{ $this->zipForm }}

                        <x-filament::button type="submit" icon="heroicon-o-arrow-up-tray">
                            Upload & Install
                        </x-filament::button>
                    </form>
                </x-filament::section>

                <div class="mt-4 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-sm text-blue-700 dark:text-blue-300">
                    <strong>Note:</strong> After installation, the page will refresh automatically and run migrations.
                    You may need to run <code class="font-mono bg-blue-100 dark:bg-blue-800 px-1 rounded">composer dump-autoload</code>
                    if the plugin doesn't appear.
                </div>
            </div>

        {{-- ── Tab: License Key ────────────────────────────────────────────── --}}
        @elseif($activeTab === 'license')
            <div class="max-w-xl">
                <x-filament::section
                    heading="Activate via License Key"
                    description="Enter your license key to download and install a premium plugin automatically."
                    icon="heroicon-o-key"
                >
                    <form wire:submit="installFromLicenseKey" class="space-y-4">
                        {{ $this->licenseForm }}

                        <x-filament::button type="submit" icon="heroicon-o-cloud-arrow-down">
                            Validate & Install
                        </x-filament::button>
                    </form>
                </x-filament::section>

                <div class="mt-4 p-4 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-sm text-amber-700 dark:text-amber-300">
                    <strong>How it works:</strong>
                    <ol class="mt-2 space-y-1 list-decimal list-inside">
                        <li>Purchase the plugin from the developer's website</li>
                        <li>You'll receive a license key after payment</li>
                        <li>Enter the key here — the system validates and downloads automatically</li>
                        <li>Plugin installs with one click, no developer needed</li>
                    </ol>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
