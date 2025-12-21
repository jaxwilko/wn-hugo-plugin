<template>
    <div class="flex flex-col">
        <div @click="detailed = !detailed" class="flex flex-row gap-4 cursor-pointer">
            <AuditIcon :score="audit.data.score"></AuditIcon>
            <div class="flex flex-col gap-4">
                <div class="font-bold pt-[1px]">
                    <Markdown class="inline" :content="audit.data.title"></Markdown>
                    <span v-if="audit.data.displayValue" :class="`inline ${scoreColour(audit.data.score)}`" :title="`${audit.data.numericValue} ${audit.data.numericUnit}`">
                        - {{audit.data.displayValue}}
                    </span>
                </div>
            </div>
            <div class="ml-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="`${detailed ? 'rotate-180' : ''} transition-all duration-100 size-6`">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </div>
        </div>
        <div v-if="detailed" class="p-2">
            <Markdown class="my-4" :content="audit.data.description"></Markdown>
            <AuditDetail v-if="audit.data.details" :details="audit.data.details"></AuditDetail>
            <div v-else>
                <span class="text-red-500">This audit has no details</span>
            </div>
        </div>
    </div>
</template>
<script>
import AuditIcon from '~plugin/assets/src/js/sites/components/audit/AuditIcon.vue';
import Markdown from '~plugin/assets/src/js/sites/components/Markdown.vue';
import AuditDetailTable from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailTable.vue';
import AuditDetailCheckList from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailCheckList.vue';
import AuditDetail from '~plugin/assets/src/js/sites/components/audit/AuditDetails.vue';

export default {
    name: 'AuditInsightRecord',
    props: ['audit'],
    components: {AuditDetail, AuditDetailList: AuditDetailCheckList, AuditDetailTable, Markdown, AuditIcon},
    data: () => {
        return {
            detailed: false,
        };
    },
    computed: {
        displayAudits() {
            if (this.detailed) {
                return this.audits;
            }

            return this.audits.filter((audit) => audit.data.score < 1);
        }
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
