<?php

namespace JaxWilko\Hugo\Console;

use Cron\CronExpression;
use JaxWilko\Hugo\Models\Workflow;
use JaxWilko\Hugo\Models\WorkflowSchedule as WorkflowScheduleModel;
use Winter\Storm\Console\Command;

class WorkflowSchedule extends Command
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
        {--c|clear : Clear the schedule}
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
        if ($this->option('clear')) {
            WorkflowScheduleModel::where('status', '=', WorkflowScheduleModel::STATUS_RUNNING)
                ->update(['status' => WorkflowScheduleModel::STATUS_FINISHED]);
            return 0;
        }

        if ($this->option('group') && $group = Workflow::find($this->option('group'))) {
            $group->scheduled()->save(new WorkflowSchedule([
                'status' => 'pending'
            ]));
            return 0;
        }

        foreach (Workflow::all() as $group) {
            switch ($group->strategy) {
                case 'cron':
                    $cron = new CronExpression($group->cron);
                    if ($cron->isDue()) {
                        $group->scheduled()->save(new WorkflowSchedule([
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
