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
                    ->run($action->target, $action->config);

                $workflowResult->results()->save(new ActionResult([
                    'action_id' => $action->id,
                    'status' => $engine->getExit(),
                    'result' => $engine->getLog()
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
        if ($this->status === AutomationEngine::STATUS_OKAY) {
            return $this;
        }

        $string = '<table style="text-align: left;"><thead><tr><th>Test</th><th>Result</th></tr></thead><tbody>';
        foreach ($this->results as $result) {
            $string .= sprintf(
                '<tr><td style="padding-right: 15px;">%s</td><td>%s</td></tr>',
                $result->action->name,
                $result->getStatusLabel()
            );
        }
        $string .= '</tbody></table>';

        $config = [
            'title'     => 'Test Failed',
            'heading'   => 'Test group has reported a failure!',
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
}
