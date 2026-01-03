<div class="layout responsive-sidebar">
    <div class="layout-cell">

        <div class="layout">
            <?php if ($breadcrumbContent = Block::placeholder('breadcrumb')): ?>
                <!-- Breadcrumb -->
                <div class="control-breadcrumb breadcrumb-flush">
                    <?= $breadcrumbContent ?>
                </div>
            <?php endif ?>

            <!-- Content -->
            <div class="layout-row">
                <div class="layout">
                    <?= Block::placeholder('form-contents') ?>
                </div>
            </div>
        </div>

    </div>
    <div class="w-full tw-hidden lg:table-cell lg:!w-[300px] h-[300px] lg:h-auto form-sidebar control-scrollpanel">
        <div class="layout-relative lg:min-h-[93vh]">
            <div class="layout-absolute">
                <div class="control-scrollbar" data-control="scrollbar">
                    <div class="padded-container">
                        <?= Block::placeholder('form-sidebar') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
