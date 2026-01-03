<template>
    <div
        :class="`
            ${action.hasOwnProperty('original_index') ? 'bg-blue-100/20 outline-blue-200' : 'bg-gray-100 outline-gray-200'}
            ${action.hasOwnProperty('nested') ? 'ml-8' : ''}
            w-full overflow-hidden rounded-xl outline shadow relative
         `"
    >
        <div class="flex flex-col md:flex-row w-full">
            <div :class="`flex flex-col ${action.screenshot?.result?.value?.path || action?.value?.path ? 'w-full md:w-1/2' : 'w-full'} gap-4 p-3`">
                <div class="flex flex-row items-center gap-4">
                    <div class="flex items-center justify-center p-3 bg-blue-200 rounded-xl size-10">
                        <i :class="config.icon"></i>
                    </div>
                    <div class="uppercase font-bold align-middle">{{config.name}}</div>
                    <div v-if="action.hasOwnProperty('original_index')"
                         @click="$emit('scrollToItem')"
                         class="flex cursor-pointer text-gray-900 select-none bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center justify-center ml-auto p-1"
                         title="Inspect"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                        </svg>
                    </div>
                </div>
                <div>
                    {{config.description}}
                </div>
                <div class="flex flex-row gap-4 border-t border-blue-100 pt-4">
                    <div class="flex flex-row items-center gap-2">
                        <div :class="`p-1 border rounded-xl ${getStatusClasses(action?.result?.status)}`">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs">Status</span>
                            <span class="font-bold text-lg -mt-[7px]">{{ getStatusLabel(action?.result?.status) }}</span>
                        </div>
                    </div>
                    <div v-if="action._group !== 'ifStatement'" class="flex flex-row items-center gap-2">
                        <div class="p-1 rounded-xl bg-gray-200 border border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs">Duration</span>
                            <span class="font-bold text-lg -mt-[7px]">{{ duration }}s</span>
                        </div>
                    </div>
                </div>
                <div>
                    <div v-if="action?.result?.value" class="flex flex-row items-center gap-2 border-t border-blue-100 pt-4">
                        <div class="flex flex-col w-full">
                            <span class="text-xs mb-2">Result Value</span>
                            <div class="p-2 bg-gray-100 border border-gray-200 rounded-lg w-full font-mono text-lg">
                                {{ action?.result?.value }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="action.screenshot?.result?.value?.path || action?.result.value?.path" class="w-full md:w-1/2 bg-cover bg-center min-h-[175px]" :style="`background-image: url('${this.storageUrl}${action._group === 'screenshot' ? action?.result.value?.path.substring(4) : action.screenshot?.result?.value?.path.substring(4)}')`">
                <LightboxImage :invisible="true"
                               :src="`${this.storageUrl}${action._group === 'screenshot' ? action?.result.value?.path.substring(4) : action.screenshot?.result?.value?.path.substring(4)}`"
                               :alt="`${config.name} at ${action?.result?.timestamp ? `${(action?.result?.timestamp - startedAt).toFixed(2)}s` : null}`"
                               :group="screenshots"
                ></LightboxImage>
            </div>
        </div>
    </div>
</template>
<script>
import Screenshot from '~plugin/assets/src/js/actions/components/Screenshot.vue';
import actionConfig from '~plugin/models/action/action-commands.yaml'
import {getStatusLabel, getStatusClasses} from '~plugin/assets/src/js/utils/actions';
import LightboxImage from '~plugin/assets/src/js/components/LightboxImage.vue';

export default {
    name: 'ActionResult',
    components: {LightboxImage, Screenshot},
    props: ['action', 'startedAt', 'finishedAt', 'screenshots', 'storageUrl'],
    computed: {
        config() {
            return actionConfig[this.action._group] || {};
        },
        duration() {
            return (this.finishedAt - this.action.result.timestamp).toFixed(2);
        },
    },
    methods: {getStatusLabel, getStatusClasses},
};
</script>
