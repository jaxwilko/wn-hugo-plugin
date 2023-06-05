<?php

namespace JaxWilko\Hugo\FormWidgets;

use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Backend\Widgets\Filter;
use Backend\Widgets\Form;
use Backend\Widgets\Lists;
use Backend\Widgets\Toolbar;
use BackendAuth;
use JaxWilko\Hugo\Models\LighthouseReport;
use JaxWilko\Hugo\Models\LighthouseUrl;
use Model;

class LighthouseResults extends FormWidgetBase
{
    public mixed $list = '$/jaxwilko/hugo/formwidgets/lighthouseresults/config/list.yaml';
    public mixed $form = '$/jaxwilko/hugo/models/lighthousereport/fields.yaml';

    public mixed $subject = 'formModel';
    public mixed $source = false;
    public bool $toolbar = false;
    public bool $filter = true;

    protected $defaultAlias = 'lighthouseresults';
    protected Toolbar $toolbarWidget;
    protected Filter $filterWidget;
    protected Lists $listWidget;
    protected Form $lighthouseReportWidget;

    /**
     * @inheritDoc
     */
    public function init()
    {
        // Populate configuration
        $this->fillFromConfig([
            'list',
            'form',
            'subject',
            'source',
            'toolbar',
            'filter',
        ]);

        if ($this->formField->disabled || $this->formField->readOnly) {
            $this->previewMode = true;
        }

        // Initialize the widgets
        if ($this->getLighthouseUrl()) {
            $this->getListWidget();
            $this->getLighthouseResultsWidget();
        }
    }

    /**
     * Get the subject to filter the list by
     *
     * @return Model|false
     */
    protected function getSubject()
    {
        $subject = false;

        if (is_callable($this->subject)) {
            $subject = call_user_func($this->subject, $this);
        } elseif (is_object($this->subject)) {
            $subject = $this->subject;
        } else {
            switch ($this->subject) {
                case 'formModel':
                    $subject = $this->model;
                    break;
                case false:
                    $subject = false;
                    break;
                default:
                    if (class_exists($this->subject)) {
                        $subject = new $this->subject;
                    }
                    break;
            }
        }

        if ($subject !== false && !($subject instanceof Model)) {
            $subject = false;
        }

        return $subject;
    }

    protected function getToolbarWidget(): ?Toolbar
    {
        return $this->toolbarWidget ?? null;
    }

    protected function getFilterWidget(): Filter
    {
        return $this->filterWidget;
    }

    protected function getListWidget(): Lists
    {
        if (isset($this->listWidget)) {
            return $this->listWidget;
        }

        // Initialize the list widget
        $listConfig = $this->makeConfig($this->list);

        /*
         * Create the model
         */
        $class = $listConfig->modelClass;
        $model = new $class;

        /*
         * Prepare the list widget
         */
        $columnConfig = $this->makeConfig($listConfig->list);
        $columnConfig->model = $model;
        $columnConfig->alias = $this->alias . 'List';

        /*
         * Prepare the columns configuration
         */
        $configFieldsToTransfer = [
            'recordUrl',
            'recordOnClick',
            'recordsPerPage',
            'showPageNumbers',
            'noRecordsMessage',
            'defaultSort',
            'showSorting',
            'showSetup',
            'showCheckboxes',
            'showTree',
            'treeExpanded',
            'customViewPath',
        ];

        foreach ($configFieldsToTransfer as $field) {
            if (isset($listConfig->{$field})) {
                $columnConfig->{$field} = $listConfig->{$field};
            }
        }

        /*
         * List Widget
         */
        $widget = $this->makeWidget('Backend\Widgets\Lists', $columnConfig);

        // Filter activities based on the subject and source properties
        $widget->bindEvent('list.extendQuery', function ($query) {
            $query->where('url_id', $this->getLighthouseUrl()->id);
        });
        $widget->bindToController();

        /*
         * Prepare the toolbar widget (optional)
         */
        if (isset($listConfig->toolbar) && $this->toolbar === true) {
            $toolbarConfig = $this->makeConfig($listConfig->toolbar);
            $toolbarConfig->alias = $widget->alias . 'Toolbar';
            $toolbarWidget = $this->makeWidget('Backend\Widgets\Toolbar', $toolbarConfig);
            $toolbarWidget->bindToController();
            $toolbarWidget->controller->addViewPath($this->viewPath);
            $toolbarWidget->cssClasses[] = 'list-header';

            /*
             * Link the Search Widget to the List Widget
             */
            if ($searchWidget = $toolbarWidget->getSearchWidget()) {
                $searchWidget->bindEvent('search.submit', function () use ($widget, $searchWidget) {
                    $widget->setSearchTerm($searchWidget->getActiveTerm());
                    return $widget->onRefresh();
                });

                $widget->setSearchOptions([
                    'mode' => $searchWidget->mode,
                    'scope' => $searchWidget->scope,
                ]);

                // Find predefined search term
                $widget->setSearchTerm($searchWidget->getActiveTerm());
            }

            $this->toolbarWidget = $toolbarWidget;
        }

        /*
         * Prepare the filter widget (optional)
         */
        if (isset($listConfig->filter) && $this->filter === true) {
            $widget->cssClasses[] = 'list-flush';

            $filterConfig = $this->makeConfig($listConfig->filter);
            $filterConfig->alias = $widget->alias . 'Filter';
            $filterWidget = $this->makeWidget('Backend\Widgets\Filter', $filterConfig);
            $filterWidget->bindToController();

            // Limit the options by the current subject
            $filterWidget->bindEvent('filter.extendScopesBefore', function () use ($filterWidget) {
                foreach ($filterWidget->scopes as &$scope) {
                    if (!empty($scope['modelClass']) && is_string($scope['options']) && class_exists($scope['modelClass'])) {
                        $scope['options'] = (new $scope['modelClass'])->{$scope['options']}($this->getSubject());
                    }
                }
            });

            /*
             * Filter the list when the scopes are changed
             */
            $filterWidget->bindEvent('filter.update', function () use ($widget, $filterWidget) {
                return $widget->onFilter();
            });

            // Apply predefined filter values
            $widget->addFilter([$filterWidget, 'applyAllScopesToQuery']);

            $this->filterWidget = $filterWidget;
        }

        return $this->listWidget = $widget;
    }

    /**
     * Get the Form widget used for the activity record popup
     */
    protected function getLighthouseResultsWidget(): Form
    {
        if (isset($this->lighthouseReportWidget)) {
            return $this->lighthouseReportWidget;
        }

        // Configure the Form widget
        $config = $this->makeConfig($this->form);
        $config->model = $this->getLighthouseUrl();
        $alias = !empty($this->alias) ? $this->alias : basename(str_replace('\\', '/', get_class($this)));
        $config->arrayName = $alias . 'Form';
        $config->isNested = true;

        // Initialize the Form widget
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->previewMode = true;
        $widget->bindToController();

        return $this->lighthouseReportWidget = $widget;
    }

    /**
     * Get the currently active activity record
     */
    protected function getLighthouseUrl(): LighthouseUrl|LighthouseReport|null
    {
        if (($id = post('jaxwilkoHugoReportId')) && ($record = LighthouseReport::find($id))) {
            return $record;
        }

        if (!$this->model instanceof LighthouseUrl) {
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
            'form' => $this->getLighthouseResultsWidget()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addJs('js/lighthouseresults.js', 'JaxWilko.Hugo');
    }

    /**
     * Prepares the formwidget view data
     */
    public function prepareVars()
    {
        if ($this->formField->disabled || $this->formField->readOnly) {
            $this->previewMode = true;
        }

        $this->vars['toolbar'] = $this->getToolbarWidget();
        $this->vars['filter']  = $this->getFilterWidget();
        $this->vars['list']    = $this->getListWidget();
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('default');
    }

    /**
     * Process the postback value for this widget. If the value is omitted from
     * postback data, it will be NULL, otherwise it will be an empty string.
     *
     * @param mixed $value The existing value for this widget.
     * @return string The new value for this widget.
     */
    public function getSaveValue($value)
    {
        return FormField::NO_SAVE_DATA;
    }
}
