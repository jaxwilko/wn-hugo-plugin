<template>
    <div ref="chart"></div>
</template>
<script>
import ApexCharts from 'apexcharts';

export default {
    name: 'ApexChart',
    props: ['options'],
    data: () => {
        return {
            chart: null
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.chart = new ApexCharts(this.$refs.chart, this.options);
            this.chart.render();
        });
    },
    beforeUnmount() {
        this.chart.destroy();
    }
};
</script>
