<?php

namespace App\Contracts;

interface PluginContract
{
    /**
     * Get the unique slug/identifier for this plugin.
     * e.g., 'payment-gateway', 'olt-manager'
     */
    public function getId(): string;

    /**
     * Get the human-readable name of the plugin.
     */
    public function getName(): string;

    /**
     * Get the plugin version (semver).
     */
    public function getVersion(): string;

    /**
     * Get the plugin description.
     */
    public function getDescription(): string;

    /**
     * Get the author name or organization.
     */
    public function getAuthor(): string;

    /**
     * Get the minimum required core app version.
     * Returns null if no minimum is required.
     */
    public function getRequiredCoreVersion(): ?string;

    /**
     * Register any services, bindings, or config for the plugin.
     * Called during application registration phase.
     */
    public function register(\Illuminate\Foundation\Application $app): void;

    /**
     * Bootstrap the plugin after all services are registered.
     * Called during application boot phase.
     */
    public function boot(\Illuminate\Foundation\Application $app): void;

    /**
     * Return Filament panel pages to register.
     * Return an empty array if none.
     *
     * @return array<class-string>
     */
    public function getFilamentPages(): array;

    /**
     * Return Filament resources to register.
     *
     * @return array<class-string>
     */
    public function getFilamentResources(): array;

    /**
     * Return Filament widgets to register.
     *
     * @return array<class-string>
     */
    public function getFilamentWidgets(): array;

    /**
     * Return navigation items to inject into the Filament sidebar.
     *
     * @return array<\Filament\Navigation\NavigationItem>
     */
    public function getNavigationItems(): array;

    /**
     * Paths to migration files that should be run on plugin install.
     *
     * @return array<string> Absolute paths to migration directories
     */
    public function getMigrationPaths(): array;

    /**
     * Return route files to load.
     *
     * @return array<string> Absolute paths to route files
     */
    public function getRouteFiles(): array;

    /**
     * Return view namespaces to register.
     * Format: ['namespace' => '/path/to/views']
     *
     * @return array<string, string>
     */
    public function getViewNamespaces(): array;

    /**
     * Return config files to merge.
     * Format: ['/path/to/config.php' => 'config-key']
     *
     * @return array<string, string>
     */
    public function getConfigFiles(): array;

    /**
     * Return the license key for this plugin (if premium).
     * Return null for free/open-source plugins.
     */
    public function getLicenseKey(): ?string;

    /**
     * Check if this plugin requires a valid license.
     */
    public function requiresLicense(): bool;

    /**
     * Called when the plugin is first installed (e.g., run migrations).
     */
    public function install(): void;

    /**
     * Called when the plugin is uninstalled.
     */
    public function uninstall(): void;
}
