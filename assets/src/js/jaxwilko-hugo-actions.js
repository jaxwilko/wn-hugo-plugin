import { createApp } from "vue";
import ActionPreview from "./actions/ActionPreview.vue";
import ActionReview from "./actions/ActionReview.vue";
import HugoButton from '~plugin/assets/src/js/components/HugoButton.vue';
import { winterRequestPlugin } from './utils/winter-request';

const createActionApp = (target) => {
    const app = createApp({
        components: {ActionPreview, ActionReview, HugoButton}
    });

    app.use(winterRequestPlugin);
    app.mount(target);

    return app;
};

// If the actions app is available, bind it
if (document.querySelector('#actions-app')) {
    createActionApp("#actions-app");
}

// If the class is available, bind it too
if (document.querySelector('.actions-app')) {
    createActionApp(".actions-app");
}

// Add function for the view button on lighthouse list
window.setupActionPopup = (id, storageUrl) => {
    $.popup({
        content: `
            <div class="modal-header px-6">
                <button type="button" class="close" data-dismiss="popup">&times;</button>
                <h4 class="modal-title">
                    Action
                </h4>
            </div>
            <div class="hugo-app px-6">
                <action-review id="${id}" storage-url="${storageUrl}"></action-review>
            </div>
        `,
        size: 'giant action-popup',
    });

    // Bind app to current scope
    const app = createActionApp('.action-popup');
    // Use JQ events to grab modal exit
    const popup = $('.action-popup').parent();
    // On modal exit, kill the app
    popup.on('hidden.bs.modal', () => {
        app.unmount();
    });
};
