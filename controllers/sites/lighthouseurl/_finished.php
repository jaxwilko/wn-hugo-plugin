<?php if ($formModel->hasImages()): ?>
    <div style="display: block">
        <img src="<?= $value ?>">
    </div>
<?php else: ?>
    <div style="display: block">
        <span class="text-muted">Images have been deleted</span>
    </div>
<?php endif; ?>
