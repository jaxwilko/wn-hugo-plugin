<template>
    <div class="flex flex-row justify-between my-5 py-3 border-b border-blue-200">
        <div class="font-bold text-lg">Insights</div>
        <div @click="detailed = !detailed" class="font-bold text-lg cursor-pointer">
            {{detailed ? 'Hide passing' : 'Show all'}}
        </div>
    </div>
    <div class="grid grid-cols-1 gap-4">
        <div v-for="audit in displayAudits" class="flex flex-col gap-6 bg-white rounded-xl shadow-sm p-3">
            <AuditInsightRecord :audit="audit"></AuditInsightRecord>
        </div>
    </div>
</template>
<script>
import AuditIcon from '~plugin/assets/src/js/sites/components/audit/AuditIcon.vue';
import Markdown from '~plugin/assets/src/js/sites/components/Markdown.vue';
import AuditInsightRecord from '~plugin/assets/src/js/sites/components/audit/AuditInsightRecord.vue';

export default {
    name: 'AuditDetails',
    props: ['audits'],
    components: {AuditInsightRecord, Markdown, AuditIcon},
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
