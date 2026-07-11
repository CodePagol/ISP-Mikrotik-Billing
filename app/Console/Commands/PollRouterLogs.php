<?php

namespace App\Console\Commands;

use App\Http\Controllers\MikrotikController;
use App\Models\MainSiteData;
use App\Models\RouterList;
use Illuminate\Console\Command;

class PollRouterLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The console command description.
     *
     * @var string
     */
    protected $signature = 'app:poll-router-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and store logs from connected MikroTik routers in the background.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $enabled = (bool) MainSiteData::getValue('log_server_enabled', false);
        if (! $enabled) {
            $this->info('Log server is disabled in settings.');

            return;
        }

        $enabledRouters = MainSiteData::getValue('log_server_routers', []);
        if (empty($enabledRouters)) {
            $this->warn('No routers selected for logging.');

            return;
        }

        $routers = RouterList::where('action', 'connected')
            ->whereIn('router_name', $enabledRouters)
            ->get();

        if ($routers->isEmpty()) {
            $this->warn('No connected routers found among the selected ones.');

            return;
        }

        $this->info('Polling logs for '.$routers->count().' routers...');

        $ctrl = app(MikrotikController::class);
        $totalInserted = 0;

        foreach ($routers as $router) {
            try {
                $this->comment("Fetching logs from: {$router->router_name}");
                $logs = $ctrl->getRouterLogs($router->router_name, 150);

                if (! empty($logs)) {
                    $inserted = $ctrl->storeRouterLogs($router->router_name, $logs);
                    $totalInserted += $inserted;
                    $this->info("  - Inserted {$inserted} new logs.");
                } else {
                    $this->warn('  - No logs retrieved.');
                }
            } catch (\Exception $e) {
                $this->error('  - Failed: '.$e->getMessage());
            }
        }

        $this->info("Log polling complete. Total new logs inserted: {$totalInserted}");
    }
}
