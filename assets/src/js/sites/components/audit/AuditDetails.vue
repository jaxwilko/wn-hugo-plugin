<template>
    <div v-if="details" class="max-w-full overflow-x-auto">
        <AuditDetailTable v-if="details.type === 'table' || details.type === 'opportunity'" :details="details"></AuditDetailTable>
        <AuditDetailCheckList v-else-if="details.type === 'checklist'" :details="details"></AuditDetailCheckList>
        <AuditDetailNode v-else-if="details.type === 'node'" :details="details"></AuditDetailNode>
        <ul v-else-if="details.type === 'list'">
            <li v-for="item in details.items">
                <AuditDetail :details="item"></AuditDetail>
            </li>
        </ul>
        <div v-else-if="details.type === 'list-section'">
            <div v-if="details.title" class="flex flex-col gap-4">
                <div class="font-bold mt-5">{{details.title}}</div>
                <div><Markdown class="-mt-2" :content="details.description"></Markdown></div>
            </div>
            <div v-if="details.value" class="mt-2">
                <AuditDetailJson :details="details.value"></AuditDetailJson>
            </div>
        </div>
        <div v-else>
            <span class="text-red-500">TODO: {{details.type}}</span>
        </div>
    </div>
</template>
<script>
import AuditDetailTable from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailTable.vue';
import AuditDetailCheckList from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailCheckList.vue';
import AuditDetailNode from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailNode.vue';
import AuditDetailJson from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailJson.vue';
import Markdown from '~plugin/assets/src/js/sites/components/Markdown.vue';

export default {
    name: 'AuditDetail',
    props: ['details'],
    components: {Markdown, AuditDetailJson, AuditDetailNode, AuditDetailCheckList, AuditDetailTable, AuditDetail: () => import('./AuditDetails.vue')},
};
</script>
