<template>
    <div class="flex flex-col gap-4 p-3">
        <span>{{details.selector}}</span>
        <div class="font-mono bg-gray-300 text-gray-900 p-3">
            {{details.snippet}}
        </div>
    </div>
</template>
<script>
export default {
    name: 'AuditDetailNode',
    props: ['details'],
};
</script>
