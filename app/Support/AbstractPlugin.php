<?php

namespace App\Support;

use App\Contracts\PluginContract;
use Illuminate\Foundation\Application;

/**
 * ─────────────────────────────────────────────────────────────────────────────
 *  AbstractPlugin — Base class for all plugins
 * ─────────────────────────────────────────────────────────────────────────────
 *
 *  Extend this class in your Plugin.php instead of implementing
 *  PluginContract directly. Override only the methods you need.
 *
 *  MINIMUM REQUIRED  (must override these 3):
 *    - getId()      → unique slug, e.g. 'my-plugin'
 *    - getName()    → human name, e.g. 'My Plugin'
 *    - getVersion() → semver, e.g. '1.0.0'
 *
 *  TO ADD NAVIGATION / PAGES / WIDGETS:
 *    → Override getFilamentPages(), getFilamentResources(), getFilamentWidgets()
 *
 *  TO HOOK INTO EXISTING APP BEHAVIOUR:
 *    → Override registerHooks() and use Hooks::action() / Hooks::filter()
 *
 *  EXAMPLE:
 *  ┌──────────────────────────────────────────────────────────────────┐
 *  │  protected function registerHooks(): void                        │
 *  │  {                                                               │
 *  │      // Add a column to the customer table                       │
 *  │      Hooks::filter('customer.table.columns', function($columns) {│
 *  │          $columns[] = TextColumn::make('olt_port')->label('Port');│
 *  │          return $columns;                                        │
 *  │      });                                                         │
 *  │                                                                  │
 *  │      // Run code after every payment                             │
 *  │      Hooks::action('payment.success', function($customer, $amt) {│
 *  │          // send WhatsApp, update OLT, etc.                      │
 *  │      });                                                         │
 *  │  }                                                               │
 *  └──────────────────────────────────────────────────────────────────┘
 *
 *  See PLUGIN_DEVELOPMENT.md for all available hook names.
 */
abstract class AbstractPlugin implements PluginContract
{
    protected Application $app;

    public function __construct()
    {
        $this->app = app();
    }

    // ─── REQUIRED: Subclass MUST override these ───────────────────────────────

    abstract public function getId(): string;

    abstract public function getName(): string;

    abstract public function getVersion(): string;

    // ─── OPTIONAL: Override as needed ────────────────────────────────────────

    public function getDescription(): string { return ''; }
    public function getAuthor(): string      { return 'Unknown'; }
    public function getRequiredCoreVersion(): ?string { return null; }

    // Called during app registration phase
    public function register(Application $app): void {}

    // Called during app boot phase — hooks are registered here automatically
    public function boot(Application $app): void
    {
        $this->registerHooks();
    }

    /**
     * Override this method to register your action/filter hooks.
     * This is the main extension point — like WordPress's plugin init.
     *
     * Available hooks: see PLUGIN_DEVELOPMENT.md
     */
    protected function registerHooks(): void {}

    // ─── Filament Integration ─────────────────────────────────────────────────

    /**
     * Return Filament Page class names to register in the panel.
     *
     * @return array<class-string<\Filament\Pages\Page>>
     *
     * Example:
     *   return [MyPluginPage::class, MySettingsPage::class];
     */
    public function getFilamentPages(): array { return []; }

    /**
     * Return Filament Resource class names to register.
     *
     * @return array<class-string<\Filament\Resources\Resource>>
     */
    public function getFilamentResources(): array { return []; }

    /**
     * Return Filament Widget class names to register on the dashboard.
     *
     * @return array<class-string<\Filament\Widgets\Widget>>
     */
    public function getFilamentWidgets(): array { return []; }

    /**
     * Return extra Filament NavigationItem objects to add to the sidebar.
     *
     * @return array<\Filament\Navigation\NavigationItem>
     *
     * Example:
     *   return [
     *       NavigationItem::make('OLT Manager')
     *           ->url('/admin/olt')
     *           ->icon('heroicon-o-signal')
     *           ->group('Network'),
     *   ];
     */
    public function getNavigationItems(): array { return []; }

    // ─── Asset / Config / Routes ──────────────────────────────────────────────

    /**
     * Absolute paths to migration directories.
     *
     * Example:
     *   return [__DIR__ . '/database/migrations'];
     */
    public function getMigrationPaths(): array { return []; }

    /**
     * Absolute paths to route files.
     *
     * Example:
     *   return [__DIR__ . '/routes/web.php'];
     */
    public function getRouteFiles(): array { return []; }

    /**
     * View namespaces to register.
     *
     * Example:
     *   return ['olt-manager' => __DIR__ . '/resources/views'];
     *
     * Then in Blade: @include('olt-manager::pages.dashboard')
     */
    public function getViewNamespaces(): array { return []; }

    /**
     * Config files to merge.
     *
     * Example:
     *   return [__DIR__ . '/config/olt.php' => 'olt'];
     */
    public function getConfigFiles(): array { return []; }

    // ─── Licensing (optional for premium plugins) ────────────────────────────

    /**
     * License key for this plugin — read from .env by default.
     * Key format: PLUGIN_LICENSE_{SLUG_UPPERCASE}
     *
     * Example: PLUGIN_LICENSE_OLT_MANAGER=XXXX-XXXX-XXXX
     */
    public function getLicenseKey(): ?string
    {
        $envKey = 'PLUGIN_LICENSE_' . strtoupper(str_replace('-', '_', $this->getId()));
        return env($envKey, config('plugins.licenses.' . $this->getId()));
    }

    /**
     * Set to true if this is a premium plugin that requires a valid license.
     */
    public function requiresLicense(): bool { return false; }

    // ─── Install / Uninstall ─────────────────────────────────────────────────

    /**
     * Called once when the plugin is first installed.
     * Override to seed settings, create default config, etc.
     */
    public function install(): void {}

    /**
     * Called when the plugin is uninstalled.
     * Override to clean up plugin-specific data.
     */
    public function uninstall(): void {}

    // ─── Helpers for subclasses ───────────────────────────────────────────────

    /**
     * Get the absolute path to this plugin's root directory.
     *
     * Example usage:  $this->pluginPath('resources/views')
     */
    protected function pluginPath(string $append = ''): string
    {
        $base = dirname((new \ReflectionClass(static::class))->getFileName());
        return $append ? $base . DIRECTORY_SEPARATOR . $append : $base;
    }
}
