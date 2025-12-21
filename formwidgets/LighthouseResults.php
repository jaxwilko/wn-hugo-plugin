<?php

namespace JaxWilko\Hugo\FormWidgets;
use JaxWilko\Hugo\Models\LighthouseReport;
use JaxWilko\Hugo\Models\SiteUrl;

class LighthouseResults extends AbstractRelationPopup
{
    public mixed $list = '$/jaxwilko/hugo/formwidgets/lighthouseresults/config/list.yaml';
    public mixed $form = '$/jaxwilko/hugo/models/lighthousereport/fields.yaml';
    protected ?string $foreignKey = 'url_id';
    protected $defaultAlias = 'lighthouseresults';

    /**
     * Get the currently active activity record
     */
    protected function getPopupModel(): SiteUrl|LighthouseReport|null
    {
        if (($id = post('jaxwilkoHugoReportId')) && ($record = LighthouseReport::find($id))) {
            return $record;
        }

        if (!$this->model instanceof SiteUrl) {
            return null;
        }

        return $this->model;
    }

    /**
     * AJAX handler to view a specific activity item's details
     */
    public function onViewLighthouseReportDetails(): string
    {
        return $this->makePartial('$/jaxwilko/hugo/formwidgets/lighthouseresults/partials/popup.ligthouseresults.php', [
            'form' => $this->getPopupFormWidget()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addJs('js/lighthouseresults.js', 'JaxWilko.Hugo');
    }
}
