<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use JaxWilko\Hugo\Classes\Automation\AutomationEngine;
use JaxWilko\Hugo\Classes\Automation\HugoWebDriver;
use JaxWilko\Hugo\Classes\Url;
use JaxWilko\Hugo\Models\ActionResult;
use JaxWilko\Hugo\Models\WorkflowSchedule;
use Winter\Storm\Exception\ApplicationException;

class Actions extends Controller
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

    public function update($recordId = null, $context = null)
    {
        $this->asExtension('FormController')->update($recordId, $context);
        $this->asExtension('FormController')
            ->formGetWidget()
            ->prependViewPath('$/jaxwilko/hugo/controllers/actions/overrides/form');
    }

    /**
     * @throws \Throwable
     * @throws ApplicationException
     */
    public function onActionPreview($recordId = null, $context = null): array
    {
        return [
            'action' => json_decode(file_get_contents(base_path('action.json'), JSON_OBJECT_AS_ARRAY))
        ];

        $formController = $this->asExtension('FormController');
        $formController->update($recordId, $context);

        $model = $formController->formGetModel();
        $formData = $formController->formGetWidget()->getSaveData();

        if (!$formData || !$formData['config'] || !$formData['url']) {
            return [];
        }

        $url = Url::make($model->site->base_url, $formData['url']);

        try {
            $engine = AutomationEngine::init($webDriver = HugoWebDriver::make())
                ->run($url, $formData['config'], autoScreenshot: true);

            $resultConfig = $engine->getConfig();

            $result = [
                'startedAt' => $engine->getStartedAt(),
                'finishedAt' => $engine->getFinishedAt(),
                'status' => $engine->getExit(),
                'result' => $resultConfig,
                'log' => $engine->getLog()
            ];
        } catch (\Throwable $e) {
            $webDriver->quit();
            throw $e;
        } finally {
            $webDriver->quit();
        }

        file_put_contents(base_path('action.json'), json_encode($result, JSON_PRETTY_PRINT));

        return [
            'action' => $result
        ];
    }
}
