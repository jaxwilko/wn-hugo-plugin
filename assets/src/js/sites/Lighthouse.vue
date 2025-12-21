<template>
    <div>
        <div v-if="errorMode">
            <span class="text-red-600">Could not load lighthouse</span>
        </div>
        <div v-else-if="viewReport">
            <div class="flex justify-between">
                <div class="flex gap-5">
                    <span class="bg-blue-100/60 rounded-xl p-3"><span class="font-bold">Target:</span> {{url.target}}</span>
                    <span class="bg-blue-100/60 rounded-xl p-3"><span class="font-bold">Report:</span> {{viewReport.human_created_at}}</span>
                </div>
                <div @click="selected = null" class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center flex justify-center ml-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                </div>
            </div>
            <div class="bg-blue-100/30 border border-blue-200 shadow-sm rounded-xl p-5 my-6">
                <ExceptionReport v-if="viewReport.report.hasOwnProperty('logVersion') && viewReport.report.logVersion === 2" :lighthouse="viewReport"></ExceptionReport>
                <Report v-else :lighthouse="viewReport"></Report>
            </div>
        </div>
        <div v-else-if="Object.keys(reports).length" class="flex flex-col">
            <div class="flex justify-between">
                <span class="bg-blue-100/60 rounded-xl p-3"><span class="font-bold">Target:</span> {{url.target}}</span>
                <select v-model="averageMode" class="w-auto">
                    <option value="sevenDay">Seven Day Averages</option>
                    <option value="allTime">All Time Averages</option>
                </select>
            </div>

            <div class="bg-blue-100/30 border border-blue-200 shadow-sm rounded-xl p-5 pb-8 mt-6">
                <HeadlineScores :report="averages"></HeadlineScores>
            </div>
            <div class="flex flex-row w-full mx-auto mt-4 mb-2 gap-4">
                <div class="w-full bg-blue-100/30 border border-blue-200 shadow-sm rounded-xl p-4">
                    <div class="font-bold">Score Chart</div>
                    <ApexChart :options="chartData.chartDetails"></ApexChart>
                    <div ref="chartDetails"></div>
                </div>
                <div class="w-full bg-blue-100/30 border border-blue-200 shadow-sm rounded-xl p-4">
                    <div class="font-bold">Performance Chart</div>
                    <ApexChart :options="chartData.chartPerformance"></ApexChart>
                </div>
            </div>
            <div class="bg-blue-100/30 border border-blue-200 shadow-sm rounded-xl p-5 mt-3 mb-6">
                <div class="-mt-4 mb-3 overflow-x-auto">
                    <table class="table table-responsive max-w-full border-separate border-spacing-y-3">
                        <thead>
                            <tr>
                                <th class="border-none pb-2">Timestamp</th>
                                <th class="text-center border-none pb-2">Performance</th>
                                <th class="text-center border-none pb-2">Accessibility</th>
                                <th class="text-center border-none pb-2">Best Practice</th>
                                <th class="text-center border-none pb-2 pr-3">SEO</th>
                                <td class="text-center border-l border-blue-100 pb-2 pl-3">FCP</td>
                                <td class="text-center pb-2">LCP</td>
                                <td class="text-center pb-2">CLS</td>
                                <td class="text-center pb-2">SI</td>
                                <td class="border-none pb-2 pl-2"></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(report, index) in reports.data" class="bg-white">
                                <td class="align-middle p-2 rounded-l-xl border-none">{{report.human_created_at}}</td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.score_performance" size="small"></Score></td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.score_accessibility" size="small"></Score></td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.score_best_practice" size="small"></Score></td>
                                <td class="text-center p-2 border-none pr-3" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.score_seo" size="small"></Score></td>
                                <td class="text-center p-2 border-blue-100 border-t-0 border-l pl-3" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.performance_first_contentful_paint" size="small"></Score></td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.performance_largest_contentful_paint" size="small"></Score></td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.performance_cumulative_layout_shift" size="small"></Score></td>
                                <td class="text-center p-2 border-none" :class="`${!index ? 'pt-3' : ''}`"><Score :score="report.performance_speed_index" size="small"></Score></td>
                                <td class="text-right p-2 pl-2 border-none rounded-r-xl" :class="`${!index ? 'pt-3' : ''}`">
                                    <div @click="selected = report.id" class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center flex justify-center ml-auto">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex mt-5">
                    <div class="bg-white rounded-xl w-auto mx-auto p-6">
                        <Pagination v-model:page="page" :items="this.reports.total" :perPage="this.reports.per_page" elementCount="10"></Pagination>
                        <div class="mt-2 text-center text-sm text-blue-900/50">Showing page {{reports.current_page}} of {{reports.last_page}} ({{reports.total}} items)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Pagination from '~plugin/assets/src/js/sites/components/Pagination.vue';
import Score from '~plugin/assets/src/js/sites/components/Score.vue';
import HeadlineScores from '~plugin/assets/src/js/sites/components/HeadlineScores.vue';
import Report from '~plugin/assets/src/js/sites/components/Report.vue';
import ApexChart from '~plugin/assets/src/js/sites/components/ApexChart.vue';
import ExceptionReport from '~plugin/assets/src/js/sites/components/ExceptionReport.vue';

export default {
    name: 'Lighthouse',
    props: ['id'],
    components: {ExceptionReport, ApexChart, Report, HeadlineScores, Score, Pagination},
    data: () => {
        return {
            errorMode: false,
            page: 1,
            averageMode: 'sevenDay',
            allTimeAverages: {},
            sevenDayAverages: {},
            url: {},
            reports: {},
            chartData: {},
            chartDetails: null,
            chartPerformance: null,
            selected: null,
        }
    },
    computed: {
        averages() {
            return this.averageMode === 'sevenDay'
                ? this.sevenDayAverages
                : this.allTimeAverages;
        },
        viewReport() {
            if (!this.selected) {
                return null;
            }
            return this.reports.data.filter(i => i.id === this.selected)[0] || null;
        }
    },
    watch: {
        id(value) {
            if (!value) {
                this.errorMode = true;
                return;
            }

            this.errorMode = false;
            this.refresh();
        },
        page(value) {
            this.refresh();
        }
    },
    methods: {
        refresh() {
            this.$request('onLighthouseData', {
                data: {
                    id: this.id,
                    page: this.page
                },
                success: (response) => {
                    if (!response.url || !response.reports) {
                        this.errorMode = true;
                        return;
                    }

                    this.url = response.url;
                    this.perPage = response.perPage;
                    this.reports = response.reports;
                    this.chartData = response.chartData;
                    this.allTimeAverages = response.allTimeAverages;
                    this.sevenDayAverages = response.sevenDayAverages;
                },
                error: () => {
                    this.errorMode = true;
                }
            });
        }
    },
    mounted() {
        this.refresh();
    }
};
</script>
