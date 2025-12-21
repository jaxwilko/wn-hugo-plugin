<?php

namespace JaxWilko\Hugo\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Sites Backend Controller
 */
class Sites extends Controller
{
    public const int RESULTS_PER_PAGE = 10;

    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->bodyClass = 'hugo-app compact-container fancy-layout compact-container breadcrumb-flush breadcrumb-fancy';

        $this->layoutPath = ['$/jaxwilko/hugo/controllers/sites/overrides/form', ...$this->layoutPath];

        $this->addVite([
            'assets/src/css/jaxwilko-hugo.css',
            'assets/src/js/jaxwilko-hugo-sites.js'
        ], 'jaxwilko.hugo');
    }

    public function update($recordId = null, $context = null)
    {
        $this->asExtension('FormController')->update($recordId, $context);
        $this->asExtension('FormController')
            ->formGetWidget()
            ->prependViewPath('$/jaxwilko/hugo/controllers/sites/overrides/form');
    }

    public function onLighthouseData($recordId = null, $context = null)
    {
        $formController = $this->asExtension('FormController');
        $formController->update($recordId, $context);

        $model = $formController->formGetModel();

        $url = $model->urls()
            ->where('id', request()->input('id'))
            ->first();

        $reports = $url?->reports()
            ->orderBy('created_at', 'desc')
            ->paginate(
                static::RESULTS_PER_PAGE,
                request()->input('page')
            )
            ->toArray();

        if (isset($reports['links'])) {
            unset($reports['links']);
        }

        return [
            'url' => $url,
            'allTimeAverages' => $url->getAverages(),
            'sevenDayAverages' => $url->getSevenDayAverages(),
            'chartData' => $url->getChartData(),
            'reports' => $reports,
        ];
    }
}
