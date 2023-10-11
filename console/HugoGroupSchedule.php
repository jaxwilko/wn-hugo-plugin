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
    protected $signature = 'hugo:schedule
        {--g|group= : Group ID to schedule}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Schedule upcoming group execution';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        if ($this->option('group') && $group = Group::find($this->option('group'))) {
            $group->scheduled()->save(new GroupSchedule([
                'status' => 'pending'
            ]));
            return 0;
        }

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

        return 0;
    }
}
