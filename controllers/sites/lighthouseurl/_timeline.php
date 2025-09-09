<?php if ($formModel->hasImages()): ?>
    <div style="width: 100%; height: fit-content; white-space: nowrap; overflow-x: scroll; overflow-y: hidden;">
        <?php if (isset($value) && is_array($value)): ?>
            <?php foreach ($value as $timestamp => $img): ?>
                <div style="width: 250px; height: 525px; float: none; display: inline-block;">
                    <img src="<?= $img ?>">
                    <span><?= $timestamp ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div style="display: block">
        <span class="text-muted">Images have been deleted</span>
    </div>
<?php endif; ?>

