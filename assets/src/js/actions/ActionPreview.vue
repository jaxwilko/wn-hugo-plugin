<template>
    <div>
        <input ref="input" type="hidden">
        <div class="flex flex-row justify-between">
            <div @click="logMode = !logMode" class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center flex justify-center mr-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 0 0 4.5 9.75v7.5a2.25 2.25 0 0 0 2.25 2.25h7.5a2.25 2.25 0 0 0 2.25-2.25v-7.5a2.25 2.25 0 0 0-2.25-2.25h-.75m-6 3.75 3 3m0 0 3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 0 1 2.25 2.25v7.5a2.25 2.25 0 0 1-2.25 2.25h-7.5a2.25 2.25 0 0 1-2.25-2.25v-.75" />
                </svg>
            </div>
            <div @click="close" class="cursor-pointer select-none size-12 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center flex justify-center ml-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
            </div>
        </div>
        <div v-if="loading" class="flex justify-center">
            <div class="select-none mx-auto size-48 p-6 my-8 transition-all duration-300 rounded-2xl items-center flex justify-center ml-auto">
                <svg class="text-blue-100 animate-spin" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M32 3C35.8083 3 39.5794 3.75011 43.0978 5.20749C46.6163 6.66488 49.8132 8.80101 52.5061 11.4939C55.199 14.1868 57.3351 17.3837 58.7925 20.9022C60.2499 24.4206 61 28.1917 61 32C61 35.8083 60.2499 39.5794 58.7925 43.0978C57.3351 46.6163 55.199 49.8132 52.5061 52.5061C49.8132 55.199 46.6163 57.3351 43.0978 58.7925C39.5794 60.2499 35.8083 61 32 61C28.1917 61 24.4206 60.2499 20.9022 58.7925C17.3837 57.3351 14.1868 55.199 11.4939 52.5061C8.801 49.8132 6.66487 46.6163 5.20749 43.0978C3.7501 39.5794 3 35.8083 3 32C3 28.1917 3.75011 24.4206 5.2075 20.9022C6.66489 17.3837 8.80101 14.1868 11.4939 11.4939C14.1868 8.80099 17.3838 6.66487 20.9022 5.20749C24.4206 3.7501 28.1917 3 32 3L32 3Z"
                        stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path
                        d="M32 3C36.5778 3 41.0906 4.08374 45.1692 6.16256C49.2477 8.24138 52.7762 11.2562 55.466 14.9605C58.1558 18.6647 59.9304 22.9531 60.6448 27.4748C61.3591 31.9965 60.9928 36.6232 59.5759 40.9762"
                        stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500">
                    </path>
                </svg>
            </div>
        </div>
        <div v-else class="flex flex-col gap-y-4 my-8">
            <div v-for="(item, index) in viewResult"
                 @mouseover="highlight(item, true)"
                 @mouseleave="highlight(item, false)"
                 :class="`
                    ${item.hasOwnProperty('original_index') ? 'bg-blue-100/20 border-blue-200' : 'bg-gray-100 border-gray-200'}
                    px-4 py-3 rounded-xl border shadow
                 `"
            >
                <div class="flex flex-row justify-between mb-3">
                    <div class="uppercase font-bold align-middle">{{item._group}}</div>
                    <div v-if="item.hasOwnProperty('original_index')" @click="scrollToItem(item)" class="cursor-pointer text-gray-900 select-none size-6 bg-blue-100 hover:bg-blue-200 transition-all duration-300 rounded-2xl items-center flex justify-center ml-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" />
                        </svg>
                    </div>
                </div>
                <Screenshot v-if="item._group === 'screenshot'" :storageUrl="storageUrl" :value="item.result.value"></Screenshot>
                <div v-else class="flex flex-row gap-4 text-gray-900">
                    <div class="w-1/2">
                        <table class="border-separate border-spacing-y-2">
                            <tbody>
                                <tr>
                                    <th class="bg-white p-3 border border-blue-200 rounded-l-xl">Status</th>
                                    <td class="bg-white/70 p-3 border border-blue-200 border-l-0 rounded-r-xl text-right">
                                        {{ getStatusLabel(item?.result?.status) }}
                                    </td>
                                </tr>
                                <tr v-if="item?.result?.value">
                                    <th class="bg-white p-3 border border-blue-200 rounded-l-xl">Value</th>
                                    <td class="bg-white/70 p-3 border border-blue-200 border-l-0 rounded-r-xl text-right">
                                        {{ item?.result?.value }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="w-1/2">
                        <Screenshot v-if="item.screenshot" :storageUrl="storageUrl" :value="item.screenshot.result.value"></Screenshot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Screenshot from '~plugin/assets/src/js/actions/components/Screenshot.vue';
import {scrollToTarget} from '~plugin/assets/src/js/utils/scroll';

export default {
    name: 'ActionPreview',
    props: ['storageUrl'],
    components: {Screenshot},
    data: () => {
        return {
            mappings: {},
            loading: true,
            logMode: false,
            result: null,
            status: null,
        }
    },
    computed: {
        viewResult() {
            const commands = [];
            for (let i = 0; i < this.result.length; i++) {
                let current = this.result[i];
                if (
                    this.result[i + 1]
                    && this.result[i + 1]._group === 'screenshot'
                    && !this.result[i + 1].hasOwnProperty('original_index')
                ) {
                    current.screenshot = this.result[i + 1];
                    i++;
                }
                commands.push(current);
            }

            return commands
        }
    },
    methods: {
        open() {
            this.mappings.manage.style.display = 'inline-block';
            this.mappings.manage.style.width = '50%';
            this.mappings.preview.style.display = 'inline-block';
            this.mappings.preview.style.width = '50%';
        },
        close() {
            this.mappings.manage.style.display = null;
            this.mappings.manage.style.width = null;
            this.mappings.preview.style.display = null;
            this.mappings.preview.style.width = null;
        },
        preview() {
            this.loading = true;
            this.$request('onActionPreview', {
                form: this.$refs.input.form,
                success: (response) => {
                    this.loading = false;
                    if (!response.action) {
                        this.errorMode = true;
                        return;
                    }

                    this.result = response.action.result;
                    this.status = response.action.status;
                },
                error: () => {
                    this.loading = false;
                    this.errorMode = true;
                }
            });
        },
        highlight(item, state) {
            if (typeof item.original_index === 'undefined') {
                return;
            }

            this.mappings.actions.children[item.original_index].style.outline = state ? '#6be0ff 4px solid' : null;
        },
        scrollToItem(item) {
            if (typeof item.original_index === 'undefined') {
                return;
            }

            const config = this.mappings.actions.children[item.original_index];
            config.style.boxShadow = '#3739f5 0 0 0 0';
            config.style.boxShadow = '#3739f5 0px 1px 17px 1px';
            scrollToTarget(config);
            setTimeout(() => {
                config.style.boxShadow = '#3739f5 0 0 0 0';
            }, 1000);
        },
        getStatusLabel(value) {
            return {
                '0': 'Okay',
                '1': 'Fail',
                '2': 'Error',
            }[value] || 'Unknown';
        },
    },
    mounted() {
        window.openActionPreview = () => {
            if (!this.mappings.lenght) {
                document.querySelectorAll('.layout-row.min-size ul.nav.nav-tabs li a').forEach((a) => {
                    this.mappings[a.title.toLowerCase()] = document.querySelector(a.dataset.target);
                });

                this.mappings.actions = document.querySelector('#Repeater-formConfig-items-config');
                Array.from(this.mappings.actions.children).forEach((e) => e.style.transition = 'box-shadow 0.3s ease-out, outline 0.1s ease-out');
            }

            this.open();
            this.preview();
        };

        this.$nextTick(() => {
            setTimeout(() => {
                document.querySelector('#Form-field-Action-toolbar-group > div > a.btn.btn-primary.wn-icon-crosshairs').click();
            }, 100);
        });
    }
};
</script>
