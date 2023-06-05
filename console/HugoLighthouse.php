<?php

namespace JaxWilko\Hugo\Console;

use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\LighthouseUrl;
use Log;
use Winter\Storm\Console\Command;

class HugoLighthouse extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:lighthouse';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:lighthouse';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run lighthouse test against enabled sites';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $sites = Site::where('performance_testing', true)->get();
        $urls = [];

        foreach ($sites as $site) {
            foreach ($site->urls as $url) {
                $urls[] = $url;
            }
        }

        $this->withProgressBar($urls, function ($url) {
            try {
                $report = \JaxWilko\Hugo\Classes\Lighthouse\Lighthouse::report($url);
            } catch (\Throwable $e) {
                Log::error('Lighthouse reporting failed: ' . $e->getMessage());
                return;
            }

            $report->save();
        });

        $this->info(PHP_EOL);
    }
}
