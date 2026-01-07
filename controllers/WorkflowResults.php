<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use jaxwilko\hugo\classes\automation\AutomationEngine;
use JaxWilko\Hugo\Classes\Automation\HugoWebDriver;
use JaxWilko\Hugo\Classes\Url;

class WorkflowResults extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->addVite([
            'assets/src/css/jaxwilko-hugo.css',
            'assets/src/js/jaxwilko-hugo-actions.js'
        ], 'jaxwilko.hugo');
    }

    public function onActionReview($recordId = null, $context = null): array
    {
        $formController = $this->asExtension('FormController');
        $formController->update($recordId, $context);

        $model = $formController->formGetModel();

        $id = (int) request()->input('id');

        if (!$model->results->contains($id)) {
            abort(403);
        }

        $record = $model->results->find($id);

        return [
            'action' => $record->result
        ];
    }
}
