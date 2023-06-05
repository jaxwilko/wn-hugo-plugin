<?php

namespace JaxWilko\Hugo\Console;

use Carbon\Carbon;
use JaxWilko\Hugo\Models\HealthCheck;
use JaxWilko\Hugo\Models\LighthouseReport;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\LighthouseUrl;
use Log;
use Winter\Storm\Console\Command;

class HugoClear extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:clear';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:clear';

    /**
     * @var string The console command description.
     */
    protected $description = 'Clear up old entries and assets';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        // Clear old lighthouse images
        LighthouseReport::whereDate('created_at', '<', Carbon::now()->subMonths(1))
            ->get()
            ->each(fn (LighthouseReport $report) => $report->deleteImages());

        // Clear old health checks
        HealthCheck::whereDate('created_at', '<', Carbon::now()->subMonths(3))->delete();
    }
}
