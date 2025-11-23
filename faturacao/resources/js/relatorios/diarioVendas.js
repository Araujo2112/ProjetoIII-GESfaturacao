document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    let chart;

    function toggleCamposPersonalizado() {
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }
    toggleCamposPersonalizado();
    periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function renderChart() {
        const data = window.vendasValores;
        const nomes = window.vendasDatas;

        const name = 'Vendas c/ IVA (€)';
        const yFormatter = value => value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        const tooltipFormatter = value => value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: true }
            },
            dataLabels: { 
                enabled: false 
            },
            series: [{ 
                name,
                data
            }],
            xaxis: {
                categories: nomes,
            },
            yaxis: {
                labels: { formatter: yFormatter }
            },
            tooltip: {
                y: { formatter: tooltipFormatter }
            },
            colors: ['#2980FF'],
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#vendasChart"), options);
        chart.render();
    }

    if (window.vendasValores && window.vendasValores.length > 0) {
        renderChart();
    }
});
