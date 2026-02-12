<?php

namespace JaxWilko\Hugo\Console;

use Illuminate\Support\Facades\Log;
use JaxWilko\Hugo\Classes\Lighthouse\Lighthouse;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Traits\HasHugoProgressBar;
use Winter\Storm\Console\Command;

class LighthouseProcess extends Command
{
    use HasHugoProgressBar;

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:lighthouse
        {--f|filter= : Only run on targets matching the pattern}
    ';

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
                if (!$this->option('filter')) {
                    $urls[] = $url;
                    continue;
                }

                if (
                    $this->option('filter') === $url->target
                    || preg_match('/' . trim($this->option('filter'), '/') . '/', $url->target)
                ) {
                    $urls[] = $url;
                }
            }
        }

        if (empty($urls)) {
            $this->components->warn('No urls found to test.');
            return;
        }

        $this->progressBar($urls, 'target', function ($url) {
            try {
                Lighthouse::make($url)
                    ->generateReport()
                    ->save();
            } catch (\Throwable $e) {
                Log::error('Lighthouse reporting failed: ' . $e->getMessage());
            }
        });
    }
}
