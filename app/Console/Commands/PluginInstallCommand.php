<?php

namespace App\Console\Commands;

use App\Support\PluginManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PluginInstallCommand extends Command
{
    protected $signature   = 'plugin:install {slug : The plugin slug to install}';
    protected $description = 'Run a plugin\'s migrations and finalize installation';

    public function handle(): int
    {
        $slug    = $this->argument('slug');
        $manager = app(PluginManager::class);

        if (! $manager->has($slug)) {
            $this->components->error("Plugin [{$slug}] is not loaded. Check that:");
            $this->line("  - The folder <comment>plugins/{$slug}/</comment> exists");
            $this->line("  - <comment>plugins/{$slug}/Plugin.php</comment> exists");
            $this->line("  - [{$slug}] is listed in <comment>config/plugins.php → enabled</comment>");
            return self::FAILURE;
        }

        $plugin = $manager->get($slug);

        $this->line('');
        $this->line("  🔌 Installing <comment>{$plugin->getName()}</comment> v{$plugin->getVersion()}...");
        $this->line('');

        // Run migrations
        $migrationPaths = $plugin->getMigrationPaths();
        if (! empty($migrationPaths)) {
            foreach ($migrationPaths as $path) {
                $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                $this->line("  Running migrations: <comment>{$relative}</comment>");
                Artisan::call('migrate', ['--path' => $relative, '--force' => true]);
                $this->line(Artisan::output());
            }
        } else {
            $this->line('  <fg=gray>No migrations found.</>\n');
        }

        // Call plugin's install() method
        try {
            $plugin->install();
        } catch (\Throwable $e) {
            $this->components->error("Plugin install() failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->components->info("Plugin <comment>{$slug}</comment> installed successfully!");
        $this->line('');

        return self::SUCCESS;
    }
}
