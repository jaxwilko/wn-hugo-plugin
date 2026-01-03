<template>
    <div>
        <input ref="input" type="hidden">
        <div class="flex flex-row justify-between">
            <HugoButton @click="logMode = !logMode" :icon="`${logMode ? 'log' : 'log-detail'}`" :title="`${logMode ? 'View Details' : 'View Log'}`"></HugoButton>
            <HugoButton @click="close" icon="cross" title="Close"></HugoButton>
        </div>
        <HugoLoading v-if="loading"></HugoLoading>
        <div v-else-if="logMode" class="flex flex-col gap-4 my-6">
            <div v-for="item in log" class="bg-gray-100 outline outline-gray-200 shadow w-full rounded-xl p-4">
                {{item}}
            </div>
        </div>
        <div v-else class="flex flex-col gap-4 mt-6">
            <ActionOverview :status="status" :startedAt="startedAt" :finishedAt="finishedAt"></ActionOverview>
            <div class="flex flex-col gap-y-6 my-8 border-l-4 ml-4 pl-10 border-blue-800">
                <div v-for="(item, index) in commands"
                     class="flex flex-row relative w-full"
                     @mouseover="highlight(item, true)"
                     @mouseleave="highlight(item, false)"
                >
                    <ActionTime :time="(item._group === 'ifStatement') ? null : (item?.result?.timestamp ? `${(item?.result?.timestamp - startedAt).toFixed(2)}s` : null)"></ActionTime>
                    <ActionResult
                        :action="item"
                        :storageUrl="storageUrl"
                        :startedAt="startedAt"
                        :finishedAt="commands[index + 1] ? commands[index + 1].result?.timestamp : finishedAt"
                        :screenshots="screenshots"
                        @scrollToItem="scrollToItem(item)"
                    ></ActionResult>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Screenshot from '~plugin/assets/src/js/actions/components/Screenshot.vue';
import {scrollToTarget} from '~plugin/assets/src/js/utils/scroll';
import actionConfig from '~plugin/models/action/action-commands.yaml';
import ActionResult from '~plugin/assets/src/js/actions/components/ActionResult.vue';
import HugoButton from '~plugin/assets/src/js/components/HugoButton.vue';
import HugoLoading from '~plugin/assets/src/js/components/HugoMark.vue';
import ActionOverview from '~plugin/assets/src/js/actions/components/ActionOverview.vue';
import ActionTime from '~plugin/assets/src/js/actions/components/ActionTime.vue';
import LightboxImage from '~plugin/assets/src/js/components/LightboxImage.vue';

export default {
    name: 'ActionPreview',
    props: ['storageUrl'],
    components: {LightboxImage, ActionTime, ActionOverview, HugoLoading, HugoButton, ActionResult, Screenshot},
    data: () => {
        return {
            mappings: {},
            loading: true,
            logMode: false,
            startedAt: null,
            finishedAt: null,
            commands: null,
            log: null,
            result: null,
            status: null,
        }
    },
    computed: {
        screenshots() {
            const screenshots = [];
            this.commands.forEach((action) => {
                const src = action._group === 'screenshot'
                    ? action?.result.value?.path.substring(4)
                    : action.screenshot?.result?.value?.path.substring(4)

                if (!src) {
                    return;
                }

                screenshots.push({
                    src: `${this.storageUrl}${src}`,
                    alt: `${actionConfig[action._group].name} at ${action?.result?.timestamp ? `${(action?.result?.timestamp - this.startedAt).toFixed(2)}s` : null}`
                })
            });
            return screenshots
        }
    },
    methods: {
        open() {
            document.querySelector('#actions-app').classList.remove('hidden');
        },
        close() {
            document.querySelector('#actions-app').classList.add('hidden');
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

                    const commands = [];
                    let current;
                    for (let i = 0; i < response.action.result.length; i++) {
                        current = response.action.result[i];
                        if (
                            response.action.result[i + 1]
                            && response.action.result[i + 1]._group === 'screenshot'
                            && !response.action.result[i + 1].hasOwnProperty('original_index')
                        ) {
                            current.screenshot = response.action.result[i + 1];
                            i++;
                        }
                        commands.push(current);

                        // Detected compound result object
                        if (current?.result?.results) {
                            let subcurrent;
                            for (let j = 0; j < current.result.results.length; j++) {
                                subcurrent = current.result.results[j];
                                subcurrent.original_index = `${current.original_index}-${subcurrent.condition ? 'condition' : current.result.value}-${subcurrent.original_index}`;
                                subcurrent.nested = true;
                                if (
                                    current.result.results[j + 1]
                                    && current.result.results[j + 1]._group === 'screenshot'
                                    && !current.result.results[j + 1].hasOwnProperty('original_index')
                                ) {
                                    subcurrent.screenshot = current.result.results[j + 1];
                                    j++;
                                }
                                commands.push(subcurrent);
                            }
                        }
                    }

                    console.log(response);

                    this.commands = commands;
                    this.startedAt = response.action.startedAt;
                    this.finishedAt = response.action.finishedAt;
                    this.result = response.action.result;
                    this.status = response.action.status;
                    this.log = response.action.log;
                },
                error: () => {
                    this.loading = false;
                    this.errorMode = true;
                }
            });
        },
        highlight(item, state) {
            const config = this.identify(item);

            if (!config) {
                return;
            }

            config.style.outline = state ? '#0751bd 4px solid' : '#0751bd 0 solid';
        },
        scrollToItem(item) {
            const config = this.identify(item);

            if (!config) {
                return;
            }

            config.style.boxShadow = '#1266de 0 0 0 0';
            config.style.boxShadow = '#1266de 0px 1px 13px 1px';
            scrollToTarget(config);
            setTimeout(() => {
                config.style.boxShadow = '#1266de 0 0 0 0';
            }, 1000);
        },
        identify(item) {
            if (typeof item.original_index === 'undefined') {
                return;
            }

            if (typeof item.original_index === 'string' && item.original_index.includes('-')) {
                const parts = item.original_index.split('-');
                return this.mappings.actions.children[parts[0]]
                    .querySelector(`[id$="-${parts[1]}-group"] ul.field-repeater-items`)
                    .children[parts[2]]
            }

            return this.mappings.actions.children[item.original_index];
        },
    },
    mounted() {
        window.openActionPreview = () => {
            if (!this.mappings.length) {
                document.querySelectorAll('.layout-row.min-size ul.nav.nav-tabs li a').forEach((a) => {
                    this.mappings[a.title.toLowerCase()] = document.querySelector(a.dataset.target);
                });

                this.mappings.actions = document.querySelector('#Repeater-formConfig-items-config');
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
