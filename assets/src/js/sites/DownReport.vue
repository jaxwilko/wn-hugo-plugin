<template>
    <div>
        <ApexChart :options="chartOptions"></ApexChart>
    </div>
</template>
<script>
import ApexChart from '~plugin/assets/src/js/sites/components/ApexChart.vue';

export default {
    name: 'DownReport',
    props: ['chartOptions'],
    components: {ApexChart}
};
</script>
