<template>
    <div class="max-w-[1350px] mx-auto mb-6">
        <div class="flex mb-4">
            <HugoPanel>
                <div class="flex justify-between pb-4">
                    <span class="bg-blue-100/60 rounded-xl p-3 font-bold">Site Global Averages</span>
                    <select v-model="averageMode" class="w-auto">
                        <option value="sevenDay">Seven Day Averages</option>
                        <option value="allTime">All Time Averages</option>
                    </select>
                </div>
                <HeadlineScores :report="averages"></HeadlineScores>
            </HugoPanel>
        </div>

        <div class="flex flex-col xl:flex-row justify-between gap-4">
            <HugoPanel>
                <ApexChart v-if="chartData.chartDetails" :options="chartData.chartDetails"></ApexChart>
            </HugoPanel>
            <HugoPanel>
                <ApexChart v-if="chartData.chartPerformance" :options="chartData.chartPerformance"></ApexChart>
            </HugoPanel>
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
import HugoPanel from '~plugin/assets/src/js/components/HugoPanel.vue';

export default {
    name: 'LighthouseSiteReport',
    props: ['id'],
    components: {HugoPanel, ExceptionReport, ApexChart, Report, HeadlineScores, Score, Pagination},
    data: () => {
        return {
            averageMode: 'sevenDay',
            allTimeAverages: {},
            sevenDayAverages: {},
            chartData: {},
        }
    },
    computed: {
        averages() {
            return this.averageMode === 'sevenDay'
                ? this.sevenDayAverages
                : this.allTimeAverages;
        },
    },
    methods: {
        refresh() {
            this.$request('onLighthouseSiteData', {
                success: (response) => {
                    this.allTimeAverages = response.allTimeAverages;
                    this.sevenDayAverages = response.sevenDayAverages;
                    this.chartData = response.chartData;
                }
            });
        }
    },
    mounted() {
        this.refresh();
    }
};
</script>
