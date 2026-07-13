<template>
    <div>
        <HugoLoading v-if="loading"></HugoLoading>
        <div ref="chart" :class="loading ? 'invisible' : ''"></div>
    </div>
</template>
<script>
import ApexCharts from 'apexcharts';
import HugoLoading from '~plugin/assets/src/js/components/HugoLoading.vue';

export default {
    name: 'ApexChart',
    components: {HugoLoading},
    props: ['options'],
    data: () => {
        return {
            loading: true,
            chart: null
        }
    },
    mounted() {
        setTimeout(() => {
            this.$nextTick(() => {
                const options = this.options;
                options.chart.events = {
                    beforeMount: (chartContext) => {
                        const resizeHandler = chartContext.parentResizeHandler;
                        chartContext.parentResizeHandler = () => {
                            if (this.$refs.chart.children[0].style.width === '0px') {
                                resizeHandler(...arguments);
                            }
                        };

                        this.loading = false;
                    }
                };

                this.chart = new ApexCharts(this.$refs.chart, options);
                this.chart.render();
            });
        }, 200);
    },
    beforeUnmount() {
        this.chart.destroy();
    }
};
</script>
