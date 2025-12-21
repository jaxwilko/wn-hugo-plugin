<?php

namespace JaxWilko\Hugo\Controllers;

use Backend\Classes\Controller;
use JaxWilko\Hugo\Classes\Automation\AutomationEngine;
use JaxWilko\Hugo\Classes\Automation\HugoWebDriver;
use JaxWilko\Hugo\Classes\Url;
use JaxWilko\Hugo\Models\ActionResult;
use JaxWilko\Hugo\Models\WorkflowSchedule;

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
        $config = [
            [
                'url' => $url,
                '_group' => 'nav'
            ],
            [
                'label' => 'After nav Screenshot',
                '_group' => 'screenshot'
            ],
        ];

        foreach ($formData['config'] as $index => $item) {
            $item['original_index'] = $index;
            $config[] = $item;
            if ($item['_group'] !== 'screenshot') {
                $config[] = [
                    'label' => 'After ' . $item['_group'] . ' Screenshot',
                    '_group' => 'screenshot'
                ];
            }
        }

        try {
            $engine = AutomationEngine::init($webDriver = HugoWebDriver::make())
                ->run($url, $config, false);

            $resultConfig = $engine->getConfig();

            $result = [
                'status' => $engine->getExit(),
                'result' => $resultConfig,
                'log' => $engine->getLog()
            ];
        } catch (\Throwable $e) {
            if (isset($webDriver)) {
                $webDriver->quit();
            }

            throw $e;
        } finally {
            if (isset($webDriver)) {
                $webDriver->quit();
            }
        }

        return [
            'action' => $result
        ];
    }
}
