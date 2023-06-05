<?php
$averages = isset($column)
    ? $listRecord->{'get' . ucfirst(ltrim($column->columnName, '_'))}()
    : $formModel->{'get' . ucfirst(ltrim($field->fieldName, '_'))}();
?>

<?php if ($averages): ?>
    <div class="container-fluid">
        <div class="row averages-panel-<?= isset($column) ? 'column' : 'field' ?>">
            <?php if (empty($averages)): ?>
                <strong>No data available at this time</strong>
            <?php else: ?>
                <?php foreach ($averages as $key => $value): ?>
                    <?php if ($key === 'count') continue; ?>
                    <div class="average text-center" style="font-size: 10px; margin-bottom: 10px;">
                        <label><?= strtoupper($key) ?></label>
                        <?= $this->makePartial('$/jaxwilko/hugo/controllers/sites/lighthouseurl/_score.php', [
                            'value' => round($value, 2)
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <p class="help-block">Calculated from <?= $averages['count'] ?> reports</p>

    <style>
        .averages-panel-field {
            position: relative;
            min-height: 30px;
            margin-top: 5px;
            padding: 1em 1.25em 0 1.25em !important;
            background: #f5f5f5;
            border: 1px solid #d1d6d9;
            border-radius: 3px;
            box-shadow: inset 0 1px 0 rgba(209,214,217,0.25),0 1px 0 rgba(255,255,255,.5);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-content: flex-end;
            align-items: center;
            flex-direction: row;
        }
        .averages-panel-column {
            position: relative;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-content: flex-end;
            align-items: center;
            flex-direction: row;
        }
    </style>
<?php else: ?>
    <?= isset($column) ? '' : '<br>' ?>
    <span class="text-muted">No data available</span>
<?php endif; ?>
