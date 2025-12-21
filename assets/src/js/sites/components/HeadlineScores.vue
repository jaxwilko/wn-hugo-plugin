<template>
    <div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5 text-gray-900">
            <div class="flex flex-col w-full">
                <Score :score="report.score_performance" size="large"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">Performance</span>
            </div>
            <div class="flex flex-col w-full">
                <Score :score="report.score_accessibility" size="large"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">Accessibility</span>
            </div>
            <div class="flex flex-col w-full">
                <Score :score="report.score_best_practice" size="large"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">Best Practice</span>
            </div>
            <div class="flex flex-col w-full">
                <Score :score="report.score_seo" size="large"></Score>
                <span class="font-bold text-xl mt-3 mx-auto">SEO</span>
            </div>
        </div>
    </div>
</template>
<script>
import Score from '~plugin/assets/src/js/sites/components/Score.vue';

export default {
    name: 'HeadlineScores',
    components: {Score},
    props: ['report'],
};
</script>
