<?php

namespace JaxWilko\Hugo\Models;

use JaxWilko\Hugo\Casts\Serialize;
use JaxWilko\Hugo\Classes\automation\AutomationEngine;
use Winter\Storm\Database\Model;

class ActionResult extends Model
{
    public $table = 'jaxwilko_hugo_action_results';

    protected $guarded = ['*'];

    protected $fillable = [
        'action_id',
        'status',
        'result'
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'result' => Serialize::class
    ];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public $belongsTo = [
        'action' => [
            \JaxWilko\Hugo\Models\Action::class,
        ]
    ];

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            AutomationEngine::STATUS_OKAY => 'Okay',
            AutomationEngine::STATUS_GENERAL_ERROR => 'General Error',
            AutomationEngine::STATUS_UNCAUGHT_ERROR => 'Uncaught Error',
            AutomationEngine::STATUS_NO_EXIT_ERROR => 'Exit Error',
        };
    }
}
