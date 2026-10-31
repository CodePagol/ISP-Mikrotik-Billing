<?php

namespace App\Console\Commands;

use App\Support\PluginManager;
use Illuminate\Console\Command;

class PluginListCommand extends Command
{
    protected $signature   = 'plugin:list';
    protected $description = 'List all installed plugins and their status';

    public function handle(): int
    {
        /** @var PluginManager $manager */
        $manager = app(PluginManager::class);
        $plugins = $manager->all();

        if (empty($plugins)) {
            $this->components->warn('No plugins are installed or enabled.');
            $this->line('  Drop a plugin folder into <comment>plugins/</comment> to get started.');
            $this->line('  Or run: <comment>php artisan plugin:make my-plugin</comment>');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($plugins as $plugin) {
            $licenseStatus = $plugin->requiresLicense()
                ? ($manager->validateLicense($plugin) ? '<fg=green>✓ Valid</>' : '<fg=red>✗ Invalid</>')
                : '<fg=gray>Free</>';

            $rows[] = [
                $plugin->getId(),
                $plugin->getName(),
                $plugin->getVersion(),
                $plugin->getAuthor(),
                $licenseStatus,
                count($plugin->getFilamentPages()) . ' pages · ' .
                count($plugin->getFilamentResources()) . ' resources · ' .
                count($plugin->getFilamentWidgets()) . ' widgets',
            ];
        }

        $this->table(
            ['ID', 'Name', 'Version', 'Author', 'License', 'Components'],
            $rows
        );

        $this->line('');
        $this->line('  Total: <comment>' . count($plugins) . '</comment> plugin(s) loaded.');
        $this->line('');

        return self::SUCCESS;
    }
}
