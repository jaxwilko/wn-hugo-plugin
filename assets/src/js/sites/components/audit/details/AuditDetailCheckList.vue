<template>
    <ul>
        <li v-for="item in details.items">
            <div class="flex flex-row items-center gap-3">
                <input type="checkbox" disabled :checked="item.value">
                <span class="pt-[2px]">{{item.label}}</span>
            </div>
        </li>
    </ul>
</template>
<script>
export default {
    name: 'AuditDetailCheckList',
    props: ['details'],
};
</script>
