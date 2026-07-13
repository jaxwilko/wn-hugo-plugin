<?php namespace JaxWilko\Hugo\Console;

use jaxwilko\hugo\classes\automation\AutomationEngine;
use JaxWilko\Hugo\Classes\Script\HugoWebDriver;
use JaxWilko\Hugo\Classes\Script\ScriptEngine;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\SiteUrl;
use JaxWilko\Hugo\Models\Action;
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
        $test = Action::find(1);

        try {
            $engine = AutomationEngine::init($webDriver = \JaxWilko\Hugo\Classes\Automation\HugoWebDriver::make())
                ->run($test->target, $test->config);

            $resultConfig = $engine->getConfig();

            $result = [
                'status' => $engine->getExit(),
                'result' => $resultConfig,
                'log' => $engine->getLog()
            ];
        } catch (\Throwable $e) {
            if (isset($webDriver)) {
                $webDriver->quit();
            }

            throw $e;
        } finally {
            if (isset($webDriver)) {
                $webDriver->quit();
            }
        }

        // Handle result

        var_dump($result);
    }
}
