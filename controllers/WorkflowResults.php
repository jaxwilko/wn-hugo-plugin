<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Winter\Storm\Support\Facades\Config;

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

        if (!Config::get('jaxwilko.hugo::collapse_menu', true)) {
            BackendMenu::setContext('Jaxwilko.Hugo', 'hugo.workflows');
        }
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
