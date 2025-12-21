<template>
    <div>
        <HeadlineScores :report="lighthouse"></HeadlineScores>
        <div class="my-8 border-t border-blue-200"></div>
        <Exception :report="lighthouse.report"></Exception>
    </div>
</template>
<script>
import Score from '~plugin/assets/src/js/sites/components/Score.vue';
import HeadlineScores from '~plugin/assets/src/js/sites/components/HeadlineScores.vue';
import Exception from '~plugin/assets/src/js/sites/components/Exception.vue';

export default {
    name: 'ExceptionReport',
    props: ['lighthouse'],
    components: {Exception, HeadlineScores, Score},
    data: () => {
        return {
            performanceDetailed: false,
        };
    },
};
</script>
