<?php
$averages = isset($column)
    ? $listRecord->{'get' . ucfirst(ltrim($column->columnName, '_'))}()
    : $formModel->{'get' . ucfirst(ltrim($field->fieldName, '_'))}();
?>

<?php if (!empty($averages)): ?>
    <div class="rounded-xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4">
            <?php foreach (['performance', 'accessibility', 'best_practice', 'seo'] as $key): ?>
                <div class="flex flex-col py-3 text-center">
                    <score score="<?= round($averages['score_' . $key] ?? 0, 2) ?>" size="xs"></score>
                    <div class="text-gray-900 text-xs mx-auto font-bold"><?= $key === 'seo' ? 'SEO' : ucwords(str_replace('_', ' ', $key)) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <?= isset($column) ? '' : '<br>' ?>
    <span class="text-muted">No data available</span>
<?php endif; ?>
