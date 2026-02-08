<?php

namespace JaxWilko\Hugo\Console;

use JaxWilko\Hugo\Classes\Health\HealthChecker;
use JaxWilko\Hugo\Classes\Notify;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Traits\HasHugoProgressBar;
use System\Models\EventLog;
use Winter\Storm\Console\Command;

class SiteDownDetector extends Command
{
    use HasHugoProgressBar;

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:down-detector';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run site down detector';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $sites = Site::where('health_testing', true)->get();

        $this->progressBar($sites, 'base_url', function (Site $site) {
            try {
                if (HealthChecker::run($site)) {
                    $site->update([
                        'is_down' => true,
                    ]);

                    $this->components->error('Sending downtime alert for ' . $site->name);
                    Notify::downAlert($site);
                    return;
                }

                if ($site->is_down) {
                    $site->update([
                        'is_down' => false,
                    ]);
                    $this->components->info('Sending uptime alert for ' . $site->name);
                    Notify::upAlert($site);
                }
            } catch (\Throwable $e) {
                EventLog::addException($e);
                return;
            }
        });

        $this->output->newLine();

        return 0;
    }
}
