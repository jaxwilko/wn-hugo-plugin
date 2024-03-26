<?php namespace JaxWilko\Hugo\Console;

use JaxWilko\Hugo\Classes\Script\HugoWebDriver;
use JaxWilko\Hugo\Classes\Script\ScriptEngine;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\LighthouseUrl;
use JaxWilko\Hugo\Models\Test;
use Log;
use Winter\Storm\Console\Command;

class HugoScript extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:script';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:script';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run webdriver scripts';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $test = Test::find(1);

        $result = ScriptEngine::init(HugoWebDriver::make(), true)
            ->run($test->target, $test->config);

        // Handle result

        dd($result);
    }
}
