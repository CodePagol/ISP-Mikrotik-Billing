<?php

namespace App\Providers;

use App\Support\PluginManager;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton binding — use app(PluginManager::class) or app('plugins')
        $this->app->singleton(PluginManager::class, fn () => new PluginManager());
        $this->app->alias(PluginManager::class, 'plugins');

        /** @var PluginManager $manager */
        $manager = $this->app->make(PluginManager::class);
        $manager->discover();
        $manager->registerAll($this->app);
    }

    public function boot(): void
    {
        /** @var PluginManager $manager */
        $manager = $this->app->make(PluginManager::class);

        // Boot all plugins (this triggers registerHooks() in each plugin)
        $manager->bootAll($this->app);

        // Register plugin pages/resources/widgets into the Filament panel
        $this->registerFilamentExtensions($manager);
    }

    /**
     * Push plugin Filament components into the active panel.
     */
    protected function registerFilamentExtensions(PluginManager $manager): void
    {
        // Filament panel registration happens via panel provider — we inject here
        // by hooking into Filament's booting lifecycle.
        \Filament\Facades\Filament::serving(function () use ($manager) {
            $panel = \Filament\Facades\Filament::getCurrentPanel();
            if (! $panel) {
                return;
            }

            // Inject plugin pages
            foreach ($manager->getAllFilamentPages() as $page) {
                if (class_exists($page)) {
                    $panel->pages([$page]);
                }
            }

            // Inject plugin resources
            foreach ($manager->getAllFilamentResources() as $resource) {
                if (class_exists($resource)) {
                    $panel->resources([$resource]);
                }
            }

            // Inject plugin widgets
            foreach ($manager->getAllFilamentWidgets() as $widget) {
                if (class_exists($widget)) {
                    $panel->widgets([$widget]);
                }
            }
        });
    }
}
