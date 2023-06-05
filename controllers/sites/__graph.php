<div id="site-lighthouse-graph"></div>

<script>
    var chart = new ApexCharts(document.querySelector("#site-lighthouse-graph"), {
        series: [
            <?php foreach ($formModel->getChartData() as $key => $data): ?>
                {
                    name: '<?= strtoupper($key) ?>',
                    data: [
                        [<?= implode('], [', array_map(fn ($array) => implode(', ', $array), $data['data'])) ?>]
                    ]
                },
            <?php endforeach; ?>
        ],
        chart: {
            type: 'area',
            height: 350,
            stacked: false,
            offsetY: 0
        },
        colors: [
            <?php foreach ($formModel->getChartData() as $key => $data): ?>
                '<?= $data['colour'] ?>',
            <?php endforeach; ?>
        ],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0,
                stops: [0, 90, 100]
            },
        },
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        xaxis: {
            type: 'datetime'
        },
        yaxis: {
            reversed: false,
            show: true,
        }
    });

    chart.render();

    setTimeout(() => {
        <?php foreach ($formModel->getChartData() as $key => $data): ?>
            <?php if (!in_array($key, ['performance', 'fcp', 'lcp', 'fmp', 'cls'])): ?>
                chart.toggleSeries('<?= strtoupper($key) ?>');
            <?php endif; ?>
        <?php endforeach; ?>
    }, 300);
</script>
