<?php

namespace JaxWilko\Hugo\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Carbon\Carbon;
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

        Site::all()->each(function (Site $site) use (&$properties) {
            $properties['site_' . $site->id] = [
                'title'             => 'Enable site "' . $site->name . '"',
                'default'           => true,
                'type'              => 'checkbox',
            ];
        });

        return $properties;
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

        $activeSites = array_map(
            fn ($k) => (int) str_after($k, 'site_'),
            array_keys(
                array_filter(
                    $this->properties,
                    fn ($v, $k) => str_starts_with($k, 'site_') && $v,
                    ARRAY_FILTER_USE_BOTH
                )
            )
        );

        $this->vars['sites'] = Site::select([
            'jaxwilko_hugo_sites.id',
            'jaxwilko_hugo_sites.base_url',
            'jaxwilko_hugo_sites.name',
            'jaxwilko_hugo_sites.is_down',
            SiteUrl::getAveragesSelect()
        ])
            ->join('jaxwilko_hugo_site_urls', 'jaxwilko_hugo_sites.id', '=', 'jaxwilko_hugo_site_urls.site_id')
            ->join('jaxwilko_hugo_lighthouse_reports', function ($join) {
                $join->on('jaxwilko_hugo_site_urls.id', '=', 'jaxwilko_hugo_lighthouse_reports.url_id')
                    ->whereDate('jaxwilko_hugo_lighthouse_reports.created_at', '>', Carbon::now()->subDays(7));
            })
            ->whereIn('jaxwilko_hugo_sites.id', $activeSites)
            ->with(['image'])
            ->groupBy(
                'jaxwilko_hugo_sites.id',
                'jaxwilko_hugo_sites.base_url',
                'jaxwilko_hugo_sites.name',
                'jaxwilko_hugo_sites.is_down'
            )
            ->get()
            ->toArray();

        if (!$this->property('actions')) {
            return;
        }

        $latestResults = DB::table('jaxwilko_hugo_action_results')
            ->select('action_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('action_id');

        $this->vars['actions'] = Site::query()
            ->join('jaxwilko_hugo_actions', 'jaxwilko_hugo_sites.id', '=', 'jaxwilko_hugo_actions.site_id')
            ->joinSub($latestResults, 'latest', function ($join) {
                $join->on('jaxwilko_hugo_actions.id', '=', 'latest.action_id');
            })
            ->join('jaxwilko_hugo_action_results', 'jaxwilko_hugo_action_results.id', '=', 'latest.max_id')
            ->select([
                'jaxwilko_hugo_sites.id as site_id',
                'jaxwilko_hugo_actions.name',
                'jaxwilko_hugo_action_results.id as action_id',
                'jaxwilko_hugo_action_results.status',
                'jaxwilko_hugo_action_results.workflow_result_id',
                'jaxwilko_hugo_action_results.created_at',
            ])
            ->whereIn('jaxwilko_hugo_sites.id', $activeSites)
            ->orderBy('jaxwilko_hugo_action_results.id', 'DESC')
            ->get()
            ->toArray();
    }
}
