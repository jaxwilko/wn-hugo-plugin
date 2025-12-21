import { createApp } from "vue";
import ActionPreview from "./actions/ActionPreview.vue";
import { winterRequestPlugin } from './utils/winter-request';

const app = createApp({
    components: {ActionPreview}
});

app.use(winterRequestPlugin);

app.mount("#actions-app");
