<template>
    <div class="flex flex-row justify-between my-5 py-5 border-b border-blue-200">
        <div class="font-bold text-lg">Metrics</div>
        <div @click="detailed = !detailed" class="font-bold text-lg cursor-pointer">Expand view</div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="audit in audits" class="flex flex-col gap-6 bg-white rounded-xl shadow-sm p-6">
            <div class="flex flex-row gap-4">
                <AuditIcon :score="audit.data.score"></AuditIcon>
                <div class="flex flex-col gap-4">
                    <div class="font-bold pt-[1px]">{{audit.data.title}}</div>
                    <div :class="`flex flex-row gap-4 items-center ${scoreColour(audit.data.score)} text-3xl`">
                        <Score class="!mx-0" :score="audit.data.score" size="xs"></Score>
                        <div class="border-l border-gray-300">
                            &nbsp;
                        </div>
                        <div :title="`${audit.data.numericValue} ${audit.data.numericUnit}`">
                            {{audit.data.displayValue}}
                        </div>
                    </div>
                    <Markdown v-if="detailed" :content="audit.data.description"></Markdown>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import AuditIcon from '~plugin/assets/src/js/sites/components/audit/AuditIcon.vue';
import Markdown from '~plugin/assets/src/js/sites/components/Markdown.vue';
import Score from '~plugin/assets/src/js/sites/components/Score.vue';

export default {
    name: 'AuditOverview',
    props: ['audits'],
    components: {Score, Markdown, AuditIcon},
    data: () => {
        return {
            detailed: false,
        };
    },
    methods: {
        scoreColour(score) {
            if (score < 0.5) {
                return 'text-red-600';
            }

            if (score < 0.9) {
                return 'text-orange-600';
            }

            return 'text-green-600';
        }
    }
};
</script>
