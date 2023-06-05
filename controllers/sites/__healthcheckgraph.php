<div id="site-health-graph"></div>

<script>
    var chart = new ApexCharts(document.querySelector("#site-health-graph"), {
        series: [
            <?php foreach ($formModel->getHealthCheckData()['statues'] as $status => $colour): ?>
                {
                    name: '<?= $status ?>',
                    data: [
                        <?php foreach ($formModel->getHealthCheckData()['checks'] as $date => $data): ?>
                            [<?= $date ?>, <?= $data[$status] ?? 0 ?>],
                        <?php endforeach; ?>
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
            <?php foreach ($formModel->getHealthCheckData()['statues'] as $status => $colour): ?>
                '<?= $colour ?>',
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
</script>
