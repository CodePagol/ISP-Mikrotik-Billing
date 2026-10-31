<?php

namespace Plugins\OltManager;

use App\Support\AbstractPlugin;
use App\Support\Hooks;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Foundation\Application;

/**
 * ─────────────────────────────────────────────────────────────────────────────
 *  OLT Manager Plugin
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *  This plugin demonstrates the FULL capability of the plugin system:
 *
 *  ✅ Adds navigation items to the sidebar
 *  ✅ Registers its own Filament pages & resources
 *  ✅ Hooks into core events (payment, customer save, etc.)
 *  ✅ Adds columns to existing tables via filters
 *  ✅ Adds widgets to the dashboard
 *  ✅ Has its own routes, views, migrations, and config
 *  ✅ Uses license key validation
 */
class Plugin extends AbstractPlugin
{
    // ─── Identity ────────────────────────────────────────────────────────────

    public function getId(): string      { return 'olt-manager'; }
    public function getName(): string    { return 'OLT Manager'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getAuthor(): string  { return 'Your Name'; }
    public function requiresLicense(): bool { return true; }

    public function getDescription(): string
    {
        return 'Manage Huawei & ZTE OLT devices — provision ONUs, check signal, reboot ports.';
    }

    // ─── Filament: Pages ─────────────────────────────────────────────────────

    /**
     * These pages appear as full admin pages in the panel.
     * The plugin adds its own menu items pointing to these pages.
     */
    public function getFilamentPages(): array
    {
        return [
            \Plugins\OltManager\Filament\Pages\OltDashboard::class,
            \Plugins\OltManager\Filament\Pages\OltSettings::class,
        ];
    }

    // ─── Filament: Resources ─────────────────────────────────────────────────

    /**
     * Resources show up as CRUD sections in the admin panel.
     * Here the plugin adds a full OLT device manager.
     */
    public function getFilamentResources(): array
    {
        return [
            \Plugins\OltManager\Filament\Resources\OltDeviceResource::class,
        ];
    }

    // ─── Filament: Widgets ───────────────────────────────────────────────────

    /**
     * Widgets are injected into the dashboard.
     */
    public function getFilamentWidgets(): array
    {
        return [
            \Plugins\OltManager\Filament\Widgets\OltSignalWidget::class,
            \Plugins\OltManager\Filament\Widgets\OnuStatusWidget::class,
        ];
    }

    // ─── Filament: Navigation ─────────────────────────────────────────────────

    /**
     * Add a navigation group + items to the sidebar.
     * These appear alongside the core app's navigation.
     */
    public function getNavigationItems(): array
    {
        return [
            NavigationItem::make('OLT Dashboard')
                ->url(fn () => \Plugins\OltManager\Filament\Pages\OltDashboard::getUrl())
                ->icon('heroicon-o-signal')
                ->group('Network')
                ->sort(10),

            NavigationItem::make('OLT Devices')
                ->url(fn () => \Plugins\OltManager\Filament\Resources\OltDeviceResource::getUrl('index'))
                ->icon('heroicon-o-server-stack')
                ->group('Network')
                ->sort(11),

            NavigationItem::make('OLT Settings')
                ->url(fn () => \Plugins\OltManager\Filament\Pages\OltSettings::getUrl())
                ->icon('heroicon-o-cog-6-tooth')
                ->group('Network')
                ->sort(12),
        ];
    }

    // ─── Routes / Views / Migrations / Config ────────────────────────────────

    public function getMigrationPaths(): array
    {
        return [$this->pluginPath('database/migrations')];
    }

    public function getRouteFiles(): array
    {
        return [$this->pluginPath('routes/web.php')];
    }

    public function getViewNamespaces(): array
    {
        // Blade usage: @include('olt-manager::pages.onu-list')
        return ['olt-manager' => $this->pluginPath('resources/views')];
    }

    public function getConfigFiles(): array
    {
        // Access: config('olt.telnet_timeout')
        return [$this->pluginPath('config/olt.php') => 'olt'];
    }

    // ─── Hooks: The WordPress-like magic ─────────────────────────────────────

    /**
     * This is called automatically during boot().
     * Register all your actions and filters here.
     */
    protected function registerHooks(): void
    {
        // ── 1. After a customer pays → provision/update OLT ─────────────────
        Hooks::action('payment.success', function ($customer, float $amount) {
            // When a customer pays, automatically update their OLT port speed
            // based on their new package.
            app(\Plugins\OltManager\Services\OltProvisioningService::class)
                ->provisionOnPayment($customer);
        });

        // ── 2. Add OLT columns to the customer table ─────────────────────────
        Hooks::filter('customer.table.columns', function (array $columns) {
            // Inject an "OLT Port" column into the existing customer table.
            // The core app fires this filter when building the table.
            $columns[] = \Filament\Tables\Columns\TextColumn::make('olt_port')
                ->label('OLT Port')
                ->badge()
                ->color('info')
                ->sortable()
                ->searchable();

            $columns[] = \Filament\Tables\Columns\TextColumn::make('olt_signal')
                ->label('Signal (dBm)')
                ->badge()
                ->color(fn ($state) => $state > -25 ? 'success' : 'danger');

            return $columns;
        });

        // ── 3. Add OLT fields to the customer create/edit form ───────────────
        Hooks::filter('customer.form.fields', function (array $fields) {
            $fields[] = \Filament\Forms\Components\Section::make('OLT Configuration')
                ->icon('heroicon-o-signal')
                ->collapsible()
                ->schema([
                    \Filament\Forms\Components\TextInput::make('olt_port')
                        ->label('OLT Port')
                        ->placeholder('0/1/1'),

                    \Filament\Forms\Components\Select::make('olt_device_id')
                        ->label('OLT Device')
                        ->options(fn () => \Plugins\OltManager\Models\OltDevice::pluck('name', 'id'))
                        ->searchable(),
                ]);

            return $fields;
        });

        // ── 4. After a new customer is created → auto-provision on OLT ───────
        Hooks::action('customer.created', function ($customer) {
            if ($customer->olt_port) {
                app(\Plugins\OltManager\Services\OltProvisioningService::class)
                    ->provisionNew($customer);
            }
        });

        // ── 5. When a customer is disabled → disable on OLT too ──────────────
        Hooks::action('customer.disabled', function ($customer) {
            app(\Plugins\OltManager\Services\OltProvisioningService::class)
                ->disableOnu($customer);
        });

        // ── 6. Add a tab to the customer detail view ──────────────────────────
        Hooks::filter('customer.detail.tabs', function (array $tabs) {
            $tabs['olt'] = [
                'label' => 'OLT Info',
                'icon'  => 'heroicon-o-signal',
                'view'  => 'olt-manager::partials.customer-olt-tab',
            ];
            return $tabs;
        });

        // ── 7. Add stats to the main dashboard ───────────────────────────────
        Hooks::filter('dashboard.stats', function (array $stats) {
            $stats[] = [
                'label' => 'ONUs Online',
                'value' => \Plugins\OltManager\Models\Onu::where('status', 'online')->count(),
                'icon'  => 'heroicon-o-signal',
                'color' => 'success',
            ];
            return $stats;
        });

        // ── 8. Add items to the customer action menu ──────────────────────────
        Hooks::filter('customer.actions', function (array $actions, $customer) {
            $actions[] = \Filament\Tables\Actions\Action::make('reboot_olt_port')
                ->label('Reboot OLT Port')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function () use ($customer) {
                    app(\Plugins\OltManager\Services\OltProvisioningService::class)
                        ->rebootPort($customer);
                });
            return $actions;
        });
    }

    // ─── Install / Uninstall ─────────────────────────────────────────────────

    public function install(): void
    {
        // Migrations are auto-run by PluginManager.
        // Seed any default config here.
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--path'  => 'plugins/olt-manager/database/migrations',
            '--force' => true,
        ]);
    }

    public function uninstall(): void
    {
        // Optionally drop plugin tables.
        // \Schema::dropIfExists('olt_devices');
        // \Schema::dropIfExists('onus');
    }
}
