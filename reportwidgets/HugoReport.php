<?php

namespace JaxWilko\Hugo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;
use JaxWilko\Hugo\Models\Site;
use JaxWilko\Hugo\Models\SiteUrl;
use Winter\Storm\Support\Facades\DB;

/**
 * HealthReport Report Widget
 */
class HugoReport extends ReportWidgetBase
{
    /**
     * @var string The default alias to use for this widget
     */
    protected $defaultAlias = 'HealthReportWidget';

    /**
     * Defines the widget's properties
     * @return array
     */
    public function defineProperties()
    {
        $properties = [
            'title' => [
                'title'             => 'backend::lang.dashboard.widget_title_label',
                'default'           => 'Hugo Report',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error',
            ],
        ];

        Site::all()->each(function (Site $site) use (&$properties) {
            $properties['site_' . $site->id] = [
                'title'             => 'Display "' . $site->name . '"',
                'default'           => true,
                'type'              => 'checkbox',
            ];
        });

        return [
            ...$properties,
            'performance' => [
                'title'             => 'Display Performance',
                'default'           => true,
                'type'              => 'checkbox',
            ],
            'actions' => [
                'title'             => 'Display Actions',
                'default'           => true,
                'type'              => 'checkbox',
            ],
        ];
    }

    /**
     * Adds widget specific asset files. Use $this->addJs() and $this->addCss()
     * to register new assets to include on the page.
     * @return void
     */
    protected function loadAssets()
    {
        $this->addVite([
            'assets/src/css/jaxwilko-hugo.css',
        ], 'jaxwilko.hugo');
    }

    /**
     * Renders the widget's primary contents.
     * @return string HTML markup supplied by this widget.
     */
    public function render()
    {
        try {
            $this->prepareVars();
        } catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('healthreport');
    }

    /**
     * Prepares the report widget view data
     */
    public function prepareVars()
    {
        $this->vars['performance'] = $this->property('performance');

        $this->vars['actions'] = $this->properties;

        $this->vars['sites'] = Site::select([
            'jaxwilko_hugo_sites.id',
            'jaxwilko_hugo_sites.base_url',
            'jaxwilko_hugo_sites.name',
            'jaxwilko_hugo_sites.is_down',
            SiteUrl::getAveragesSelect()
        ])
            ->join('jaxwilko_hugo_site_urls', 'jaxwilko_hugo_sites.id', '=', 'jaxwilko_hugo_site_urls.site_id')
            ->join('jaxwilko_hugo_lighthouse_reports', 'jaxwilko_hugo_site_urls.id', '=', 'jaxwilko_hugo_lighthouse_reports.url_id')
            ->whereIn(
                'jaxwilko_hugo_sites.id',
                array_map(
                    fn ($k) => (int) str_after($k, 'site_'),
                    array_keys(
                        array_filter(
                            $this->properties,
                            fn ($v, $k) => str_starts_with($k, 'site_') && $v,
                            ARRAY_FILTER_USE_BOTH
                        )
                    )
                )
            )
            ->with(['image'])
            ->groupBy('jaxwilko_hugo_sites.id')
            ->get()
            ->toArray();

        if (!$this->property('actions')) {
            return;
        }

        $this->vars['actions'] = Site::select([
            'jaxwilko_hugo_sites.id AS site_id',
            'jaxwilko_hugo_actions.name',
            DB::raw('max(jaxwilko_hugo_action_results.id) AS action_id'),
            'jaxwilko_hugo_action_results.status',
            'jaxwilko_hugo_action_results.workflow_result_id',
            'jaxwilko_hugo_action_results.created_at',
        ])
            ->join('jaxwilko_hugo_actions', 'jaxwilko_hugo_sites.id', '=', 'jaxwilko_hugo_actions.site_id')
            ->join('jaxwilko_hugo_action_results', 'jaxwilko_hugo_actions.id', '=', 'jaxwilko_hugo_action_results.action_id')
            ->whereNotIn(
                'jaxwilko_hugo_sites.id',
                array_filter(array_map(fn ($i) => !is_numeric($i) ? null : (int) $i, $this->property('exclude_sites', []) ?? []))
            )
            ->groupBy('jaxwilko_hugo_action_results.action_id')
            ->orderBy('jaxwilko_hugo_action_results.id', 'DESC')
            ->get()
            ->toArray();
    }
}
