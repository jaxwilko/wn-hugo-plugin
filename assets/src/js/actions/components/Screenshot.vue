<template>
    <div>
        <img :src="imageSrc" :alt="value.label">
    </div>
</template>
<script>
export default {
    name: 'Screenshot',
    props: ['storageUrl', 'value'],
    computed: {
        imageSrc() {
            return `${this.storageUrl}${this.value.path.indexOf('app/') === 0 ? this.value.path.substring(4) : this.value.path}`;
        }
    }
};
</script>
