<?php

namespace JaxWilko\Hugo\Models;

use Backend\Facades\Backend;
use Backend\Models\User;
use JaxWilko\Hugo\Casts\Serialize;
use JaxWilko\Hugo\Classes\automation\HugoWebDriver;
use JaxWilko\Hugo\Classes\automation\AutomationEngine;
use JaxWilko\Hugo\Classes\Notify;
use Model;
use Winter\Storm\Support\Facades\Mail;

class WorkflowResult extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'jaxwilko_hugo_workflow_results';

    protected $guarded = ['*'];

    protected $fillable = [
        'group_id',
        'status',
        'result'
    ];

    public $rules = [];

    protected $casts = [
        'result' => Serialize::class
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $hasMany = [
        'results' => [
            \JaxWilko\Hugo\Models\ActionResult::class,
            'key' => 'workflow_result_id'
        ]
    ];

    public $belongsTo = [
        'workflow' => [
            \JaxWilko\Hugo\Models\Workflow::class,
        ]
    ];

    public static function run(WorkflowSchedule $schedule, bool $debug = false): static
    {
        $workflowResult = $schedule->workflow->results()->save(new static([
            'status' => 0,
            'result' => 'starting ' . date('Y-m-d H:i:s')
        ]));

        $schedule->setStatus(WorkflowSchedule::STATUS_RUNNING);

        try {
            $webDriver = HugoWebDriver::make();

            foreach ($schedule->workflow->actions->sortBy('priority') as $action) {
                $engine = AutomationEngine::init($webDriver, $debug)
                    ->run($action->target, $action->config, $action->auto_screenshot);

                $workflowResult->results()->save(new ActionResult([
                    'action_id' => $action->id,
                    'status' => $engine->getExit(),
                    'result' => [
                        'startedAt' => $engine->getStartedAt(),
                        'finishedAt' => $engine->getFinishedAt(),
                        'status' => $engine->getExit(),
                        'result' => $engine->getConfig(),
                        'log' => $engine->getLog(),
                        'variables' => $engine->getVariables(),
                    ]
                ]));

                $workflowResult->status = $engine->getExit() !== 0 ? $engine->getExit() : $workflowResult->status;
            }

            $workflowResult->save();
        } catch (\Throwable $e) {
            $schedule->setStatus(WorkflowSchedule::STATUS_FAILED);

            if (isset($webDriver)) {
                $webDriver->quit();
            }

            throw $e;
        }

        $schedule->setStatus(WorkflowSchedule::STATUS_FINISHED);
        $webDriver->quit();

        return $workflowResult;
    }

    public function notify(): static
    {
        Notify::workflow($this);
        return $this;
    }

    public function getUpdatedAtHumanAttribute(): string
    {
        return $this->updated_at->diffForHumans();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => 'Okay',
            1 => 'General Error',
            2 => 'Uncaught Error',
            3 => 'Exit Error',
            default => 'Unknown',
        };
    }
}
