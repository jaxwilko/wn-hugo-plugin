<template>
    <div class="bg-gray-100 outline outline-gray-200 shadow w-full rounded-xl p-4">
        <div class="flex flex-row flex-wrap gap-8">
            <div class="flex flex-col gap-1">
                <span class="uppercase">Status</span>
                <div class="flex flex-row items-center gap-2">
                    <div :class="`p-1 border rounded-xl text-gray-900 ${getStatusClasses(status)}`">
                        <svg v-if="status === 0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <svg v-else-if="status === 1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <svg v-else-if="status === 2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <span :class="`${getStatusTextClasses(status)} text-2xl font-bold`">{{getStatusLabel(status)}}</span>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="uppercase">Duration</span>
                <div class="flex flex-row items-center gap-2">
                    <div class="p-1 border rounded-xl text-gray-900 border-gray-300 bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold">{{`${(finishedAt - startedAt).toFixed(2)}s`}}</span>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <span class="uppercase">Action Start</span>
                <div class="flex flex-row items-center gap-2">
                    <div class="p-1 border rounded-xl text-gray-900 border-gray-300 bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-bold">{{new Date(startedAt * 1000).toUTCString()}}</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {getStatusClasses, getStatusLabel, getStatusTextClasses} from '~plugin/assets/src/js/utils/actions';

export default {
    name: 'ActionOverview',
    props: ['status', 'startedAt', 'finishedAt'],
    methods: {getStatusTextClasses, getStatusClasses, getStatusLabel},
};
</script>
