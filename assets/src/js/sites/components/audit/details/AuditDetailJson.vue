<template>
    <pre class="json" v-html="json"></pre>
</template>
<script>
export default {
    name: 'AuditDetailJson',
    props: ['details'],
    computed: {
        json() {
            const json = JSON.stringify(this.details, null, 4).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                let c = 'number';
                if (/^"/.test(match)) {
                    c = (/:$/.test(match)) ? 'key' : 'string';
                } else if (/true|false/.test(match)) {
                    c = 'boolean';
                } else if (/null/.test(match)) {
                    c = 'null';
                }
                return c === 'key'
                    ? `<span class="${c}">${match.replace(':', '')}</span>:`
                    : `<span class="${c}">${match}</span>`;
            });
        }
    }
};
</script>
