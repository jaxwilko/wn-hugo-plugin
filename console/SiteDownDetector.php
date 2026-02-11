<?php

namespace JaxWilko\Hugo\Console;

use Carbon\Carbon;
use JaxWilko\Hugo\Classes\Health\HealthChecker;
use JaxWilko\Hugo\Classes\Notify;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\SiteDown;
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
                $info = HealthChecker::run($site);

                if ($info && !$site->is_down) {
                    $site->downs()->save(new SiteDown($info));
                    $site->update([
                        'is_down' => true,
                    ]);
                    $this->components->error(PHP_EOL . 'Sending down alert for ' . $site->name);
                    Notify::downAlert($site);
                    return;
                }

                if (!$info && $site->is_down) {
                    $site->downs()->whereNull('up_at')->get()->each(function (SiteDown $down) {
                        $down->update([
                            'up_at' => Carbon::now()
                        ]);
                    });

                    $site->update([
                        'is_down' => false,
                    ]);

                    $this->components->info(PHP_EOL . 'Sending up alert for ' . $site->name);
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
