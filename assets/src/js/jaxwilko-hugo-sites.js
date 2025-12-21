import { createApp } from "vue";
import Lighthouse from "./sites/Lighthouse.vue";
import DownReport from "./sites/DownReport.vue";
import Score from '~plugin/assets/src/js/sites/components/Score.vue';
import { winterRequestPlugin } from './utils/winter-request';

const createSiteApp = (target) => {
    const app = createApp({
        components: {Lighthouse, DownReport, Score}
    });

    app.use(winterRequestPlugin);
    app.mount(target);

    return app;
};

// Register the list score elements
createSiteApp("#lighthouse-app");

// Register the down detector graphs
createSiteApp("#down-app");

// Add function for the view button on lighthouse list
window.setupLighthouse = (id) => {
    $.popup({
        content: `
            <div class="modal-header px-6">
                <button type="button" class="close" data-dismiss="popup">&times;</button>
                <h4 class="modal-title">
                    Lighthouse
                </h4>
            </div>
            <div class="px-6">
                <lighthouse :id="${id}"></lighthouse>
            </div>
        `,
        size: 'giant lighthouse-popup',
    });

    // Bind app to current scope
    const app = createSiteApp('.lighthouse-popup');
    // Use JQ events to grab modal exit
    const popup = $('.lighthouse-popup').parent();
    // On modal exit, kill the app
    popup.on('hidden.bs.modal', () => {
        app.unmount();
    });
};
