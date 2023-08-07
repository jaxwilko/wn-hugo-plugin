<?php

namespace JaxWilko\Hugo\Models;

use Backend\Facades\Backend;
use Backend\Models\User;
use JaxWilko\Hugo\Casts\Serialize;
use JaxWilko\Hugo\Classes\Test\HugoWebDriver;
use JaxWilko\Hugo\Classes\Test\TestEngine;
use Model;
use Winter\Storm\Support\Facades\Mail;

/**
 * TestReport Model
 */
class TestReport extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaxwilko_hugo_test_reports';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'group_id',
        'status',
        'result'
    ];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'result' => Serialize::class
    ];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'results' => [
            \JaxWilko\Hugo\Models\TestResult::class,
            'key' => 'report_id'
        ]
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'test' => [
            \JaxWilko\Hugo\Models\Group::class,
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public static function run(GroupSchedule $schedule): static
    {
        $report = $schedule->group->reports()->save(new static([
            'status' => 0,
            'result' => 'starting ' . date('Y-m-d H:i:s')
        ]));

        $schedule->setStatus(GroupSchedule::STATUS_RUNNING);

        try {
            foreach ($schedule->group->tests as $test) {
                $engine = TestEngine::init(HugoWebDriver::make())
                    ->run($test->target, $test->config);

                $report->results()->save(new TestResult([
                    'test_id' => $test->id,
                    'status' => $engine->getExit(),
                    'result' => $engine->getLog()
                ]));

                $report->status = $engine->getExit() !== 0 ? $engine->getExit() : $report->status;
            }

            $report->save();
        } catch (\Throwable $e) {
            $schedule->setStatus(GroupSchedule::STATUS_FAILED);

            return $report;
        }

        $schedule->setStatus(GroupSchedule::STATUS_FINISHED);

        return $report;
    }

    public function notify(): static
    {
        if ($this->status === TestEngine::STATUS_OKAY) {
            return $this;
        }

        $string = '<table style="text-align: left;"><thead><tr><th>Test</th><th>Result</th></tr></thead><tbody>';
        foreach ($this->results as $result) {
            $string .= sprintf(
                '<tr><td style="padding-right: 15px;">%s</td><td>%s</td></tr>',
                $result->test->name,
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
                    'href' => Backend::url('jaxwilko/hugo/testreports/update/' . $this->id),
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
}
