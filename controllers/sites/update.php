<?php Block::put('breadcrumb') ?>
    <?= $this->makeLayoutPartial('breadcrumb') ?>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>
    <?php Block::put('form-contents') ?>
    <style>
        .fancy-layout *:not(.nested-form):not(.modal-body)>.form-widget>.layout-row>.control-tabs.secondary-tabs.has-tabs>div.tab-content {
            background: transparent;
        }
        .fancy-layout *:not(.nested-form):not(.modal-body)>.form-widget>.layout-row>.control-tabs.primary-tabs>div>ul.nav-tabs, *:not(.nested-form):not(.modal-body)>.form-widget>.layout-row>.control-tabs.fancy-layout.primary-tabs>div>ul.nav-tabs {
            background: #2da7c7;
        }
        #Form-primaryTabs ul.nav.nav-tabs > li > a[title="Settings"] {
            display: none;
        }
        @media(max-width: 1024px) {
            #Form-primaryTabs ul.nav.nav-tabs > li > a[title="Settings"] {
                display: block;
            }
        }
    </style>
        <div class="layout fancy-layout">
            <div class="layout-row">
                <?= $this->formRenderOutsideFields() ?>
                <?= $this->formRenderPrimaryTabs() ?>
            </div>
        </div>
    <?php Block::endPut() ?>

    <?php Block::put('form-sidebar') ?>
        <div class="hide-tabs"><?= $this->formRenderSecondaryTabs() ?></div>
    <?php Block::endPut() ?>

    <?php Block::put('body') ?>
        <div class="hugo-app">
            <?= Form::open([
                'id' => $this->formGetId(),
                'class' => 'layout stretch fancy flex flex-col lg:flex-row',
                'data-change-monitor' => 'true',
                'data-window-close-confirm' => 'true',
            ]) ?>
                <?= $this->makeLayout('form-with-sidebar') ?>
            <?= Form::close() ?>
        </div>
    <?php Block::endPut() ?>
<?php else: ?>
    <div class="control-breadcrumb">
        <?= Block::placeholder('breadcrumb') ?>
    </div>
    <div class="padded-container">
        <p class="flash-message static error"><?= e(trans($this->fatalError)) ?></p>
        <p><a href="<?= isset($formConfig) ? Backend::url($formConfig->defaultRedirect) : 'javascript:history.back()' ?>" class="btn btn-default"><?= e(trans('backend::lang.form.return_to_list')); ?></a></p>
    </div>
<?php endif ?>
