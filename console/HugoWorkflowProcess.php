<?php

namespace JaxWilko\Hugo\Console;

use Cron\CronExpression;
use JaxWilko\Hugo\Models\Workflow;
use JaxWilko\Hugo\Models\WorkflowSchedule;
use JaxWilko\Hugo\Models\WorkflowResult;
use Winter\Storm\Console\Command;

class HugoWorkflowProcess extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:process';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:process
        {--d|debug : Enable debug mode}
    ';

    /**
     * @var string The console command description.
     */
    protected $description = 'Run scheduled group actions';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        if (WorkflowSchedule::where('status', '=', WorkflowSchedule::STATUS_RUNNING)->first()) {
            $this->error('Already running test, please wait before it to finish');
            return 1;
        }

        $schedule = WorkflowSchedule::where('status', '=', 'pending')->first();

        if (!$schedule) {
            $this->warn('No scheduled action');
            return 0;
        }

        return WorkflowResult::run($schedule, $this->option('debug'))
            ->notify()
            ->status;
    }
}
