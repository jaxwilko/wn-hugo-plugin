<template>
    <div v-html="displayContent"></div>
</template>
<script>
export default {
    name: 'Markdown',
    props: ['content'],
    computed: {
        displayContent() {
            return this.parseMarks(this.parseMarkdownUrls(this.escapeTags(this.content)));
        }
    },
    methods: {
        escapeTags(str) {
            return str.replace(/[\u00A0-\u9999<>\&]/g, i => '&#'+i.charCodeAt(0)+';');
        },
        parseMarkdownUrls(str) {
            str.matchAll(/\[(.*?)\]\((.*?)\)/g).forEach((match) => {
                str = str.replace(match[0], `<a href="${match[2]}" target="_blank" class="text-blue-700">${match[1]}</a>`);
            });

            return str;
        },
        parseMarks(str) {
            str.matchAll(/`(.*?)`/g).forEach((match) => {
                str = str.replace(match[0], `<mark class="bg-blue-200 rounded-sm py-0.5 px-1">${match[1]}</mark>`);
            });
            return str;
        },
    }
};
</script>
