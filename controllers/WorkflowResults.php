<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;

class WorkflowResults extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];
}
