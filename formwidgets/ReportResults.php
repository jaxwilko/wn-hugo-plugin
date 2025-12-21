<?php

namespace JaxWilko\Hugo\FormWidgets;

use JaxWilko\Hugo\Models\WorkflowResult;
use JaxWilko\Hugo\Models\ActionResult;

class ReportResults extends AbstractRelationPopup
{
    public mixed $list = '$/jaxwilko/hugo/formwidgets/reportresults/config/list.yaml';
    public mixed $form = '$/jaxwilko/hugo/models/actionresult/fields.yaml';
    protected $defaultAlias = 'reportResults';

    /**
     * Get the currently active activity record
     */
    protected function getPopupModel(): WorkflowResult|ActionResult|null
    {
        if (($id = post('jaxwilkoHugoReportId')) && ($record = ActionResult::find($id))) {
            return $record;
        }

        if (!$this->model instanceof WorkflowResult) {
            return null;
        }

        return $this->model;
    }

    /**
     * AJAX handler to view a specific activity item's details
     */
    public function onViewReportReportDetails(): string
    {
        return $this->makePartial('$/jaxwilko/hugo/formwidgets/reportresults/partials/popup.reportresults.php', [
            'form' => $this->getPopupModel()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addJs('js/reportresults.js', 'JaxWilko.Hugo');
    }
}
