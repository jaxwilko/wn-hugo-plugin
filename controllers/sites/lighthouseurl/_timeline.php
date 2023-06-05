<?php if ($formModel->hasImages()): ?>
    <div style="display: block">
        <div style="display: flex; flex-direction: row; overflow: scroll">
            <?php if (isset($value) && is_array($value)): ?>
                <?php foreach ($value as $timestamp => $img): ?>
                <div>
                    <img src="<?= $img ?>">
                    <span><?= $timestamp ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div style="display: block">
        <span class="text-muted">Images have been deleted</span>
    </div>
<?php endif; ?>
