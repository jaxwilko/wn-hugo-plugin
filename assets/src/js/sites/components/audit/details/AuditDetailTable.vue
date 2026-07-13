<template>
    <table v-if="details.items.length" class="w-full">
        <thead>
            <tr>
                <th v-for="header in details.headings">{{header.label}}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in details.items">
                <td v-for="header in details.headings">
                    <AuditDetailNode v-if="typeof item[header.key] === 'object' && item[header.key].type === 'node'"
                        :details="item[header.key]"
                    ></AuditDetailNode>
                    <span v-else-if="typeof item[header.key] === 'object' && item[header.key].type === 'numeric'">{{item[header.key].value}}</span>
                    <span v-else>{{item[header.key]}}</span>
                </td>
            </tr>
        </tbody>
    </table>
</template>
<script>
import AuditDetailNode from '~plugin/assets/src/js/sites/components/audit/details/AuditDetailNode.vue';

export default {
    name: 'AuditDetailTable',
    components: {AuditDetailNode},
    props: ['details'],
};
</script>
