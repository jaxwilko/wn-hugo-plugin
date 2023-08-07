<?php

namespace JaxWilko\Hugo\Console;

use Cron\CronExpression;
use JaxWilko\Hugo\Models\Group;
use JaxWilko\Hugo\Models\GroupSchedule;
use JaxWilko\Hugo\Models\TestReport;
use Winter\Storm\Console\Command;

class HugoGroupProcess extends Command
{
    /**
     * @var string The console command name.
     */
    protected static $defaultName = 'hugo:process';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'hugo:process';

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
        if (GroupSchedule::where('status', '=', GroupSchedule::STATUS_RUNNING)->first()) {
            $this->error('Already running test, please wait before it to finish');
            return 1;
        }

        $schedule = GroupSchedule::where('status', '=', 'pending')->first();

        if (!$schedule) {
            $this->warn('No scheduled action');
            return 0;
        }

        return TestReport::run($schedule)
            ->notify()
            ->status;
    }
}
