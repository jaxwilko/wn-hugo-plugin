<div id="down-app">
    <down-report :chart-options='<?= json_encode($formModel->getDownReports()) ?>'></down-report>
</div>
<?= $this->relationRender('downs'); ?>
