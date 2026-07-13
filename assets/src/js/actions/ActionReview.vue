<template>
    <div>
        <input ref="input" type="hidden">
        <div class="flex flex-row justify-between">
            <HugoButton @click="logMode = !logMode" :icon="`${logMode ? 'log' : 'log-detail'}`" :title="`${logMode ? 'View Details' : 'View Log'}`"></HugoButton>
        </div>
        <HugoLoading v-if="loading"></HugoLoading>
        <div v-else-if="logMode" class="flex flex-col gap-4 my-6 pb-6">
            <div v-for="item in log" class="bg-gray-100 outline outline-gray-200 shadow w-full rounded-xl p-4">
                <pre>{{JSON.stringify(item, null, 4)}}</pre>
            </div>
        </div>
        <div v-else class="flex flex-col gap-4 mt-6">
            <ActionOverview :status="status" :startedAt="startedAt" :finishedAt="finishedAt"></ActionOverview>
            <div class="flex flex-col gap-y-6 mt-6 mb-8 border-l-4 ml-4 pl-10 border-blue-800">
                <div v-for="(item, index) in commands"
                     class="flex flex-row relative w-full"
                >
                    <ActionTime :time="(['ifStatement', 'set'].indexOf(item._group) !== -1) ? null : (item?.result?.timestamp ? `${(item?.result?.timestamp - startedAt).toFixed(2)}s` : null)"></ActionTime>
                    <ActionResult
                        :action="item"
                        :storageUrl="storageUrl"
                        :startedAt="startedAt"
                        :finishedAt="commands[index + 1] ? commands[index + 1].result?.timestamp : finishedAt"
                        :screenshots="screenshots"
                    ></ActionResult>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Screenshot from '~plugin/assets/src/js/actions/components/Screenshot.vue';
import ActionResult from '~plugin/assets/src/js/actions/components/ActionResult.vue';
import HugoButton from '~plugin/assets/src/js/components/HugoButton.vue';
import HugoLoading from '~plugin/assets/src/js/components/HugoLoading.vue';
import ActionOverview from '~plugin/assets/src/js/actions/components/ActionOverview.vue';
import ActionTime from '~plugin/assets/src/js/actions/components/ActionTime.vue';
import LightboxImage from '~plugin/assets/src/js/components/LightboxImage.vue';
import {getScreenshots, parseCommands} from '~plugin/assets/src/js/actions/utils/actions';

export default {
    name: 'ActionPreview',
    props: ['id', 'storageUrl'],
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
            return getScreenshots(this.commands, this.startedAt, this.storageUrl);
        }
    },
    mounted() {
        this.$request('onActionReview', {
            form: this.$refs.input.form,
            data: {
                id: this.id
            },
            success: (response) => {
                this.loading = false;

                if (!response.action) {
                    this.errorMode = true;
                    return;
                }

                this.commands = parseCommands(response.action);
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
    }
};
</script>
