<?php

namespace JaxWilko\Hugo\Console;

use Cron\CronExpression;
use JaxWilko\Hugo\Models\Group;
use JaxWilko\Hugo\Models\GroupSchedule;
use Winter\Storm\Console\Command;

class HugoGroupSchedule extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:schedule';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:schedule';

    /**
     * @var string The console command description.
     */
    protected $description = 'Schedule upcoming group execution';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        foreach (Group::all() as $group) {
            switch ($group->strategy) {
                case 'cron':
                    $cron = new CronExpression($group->cron);
                    if ($cron->isDue()) {
                        $group->scheduled()->save(new GroupSchedule([
                            'status' => 'pending'
                        ]));
                    }
                    break;
                case 'manual':
                case 'webhook':
                default:
                    break;
            }
        }
    }
}
