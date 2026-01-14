<?php

namespace JaxWilko\Hugo\Models;

use Backend\Facades\Backend;
use Backend\Models\User;
use JaxWilko\Hugo\Casts\Serialize;
use JaxWilko\Hugo\Classes\automation\HugoWebDriver;
use JaxWilko\Hugo\Classes\automation\AutomationEngine;
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
        $report = [];
        foreach ($this->results as $result) {
            if ($this->status === 0 && $result->action->notification === 'okay') {
                $report[$result->id] = $result;
            }
            if ($this->status > 0 && $result->action->notification === 'fail') {
                $report[$result->id] = $result;
            }
        }

        if (empty($report)) {
            return $this;
        }

        $string = '<table style="text-align: left;"><thead><tr><th>Action</th><th>Result</th></tr></thead><tbody>';
        foreach ($this->results as $result) {
            $string .= sprintf(
                '<tr><td style="padding-right: 15px;">%s</td><td style="color: %s;">%s</td></tr>',
                $result->action->name,
                $result->getStatusColour(),
                $result->getStatusLabel()
            );
            if (isset($report[$result->id]) && ($message = $report[$result->id]->getNotificationMessage())) {
                $string .= sprintf('
                    <tr><th colspan="2">Message</th></tr>
                    <tr><td colspan="2" class="code" style="padding: 10px; background: #cecece">%s</td></tr>
                ', $message);
            }
        }
        $string .= '</tbody></table>';

        $config = [
            'title'     => 'Workflow ' . ($this->status > 0 ? 'Failed' : 'Passed'),
            'heading'   => 'Workflow has reported a ' . ($this->status > 0 ? 'failure' : 'success') . '!',
            'text'      => $string,
            'footer'    => 'Use the following links to find out more:',
            'buttons'   => [
                [
                    'text' => 'Hugo',
                    'href' => config('app.url'),
                ],
                [
                    'text' => 'Report',
                    'href' => Backend::url('jaxwilko/hugo/workflowresults/update/' . $this->id),
                    'colour' => '#E91E63'
                ]
            ]
        ];

        Mail::send('jaxwilko.hugo::mail.notification', $config, function ($message) use ($config) {
            foreach (User::all() as $user) {
                $message->to($user->email, $user->full_name);
            }
            $message->subject($config['title']);
        });

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
