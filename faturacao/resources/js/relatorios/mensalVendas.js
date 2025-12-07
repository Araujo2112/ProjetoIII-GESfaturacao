document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnVendas = document.getElementById('btnVendas');
    const btnLucro = document.getElementById('btnLucro');
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

    function renderChart(type) {
        const isVendas = type === 'vendas';

        const data  = isVendas ? window.vendasValores : window.lucroValores;
        const nomes = isVendas ? window.vendasMeses   : window.lucroMeses;
        const name  = isVendas ? 'Vendas c/ IVA (€)'  : 'Lucro (€)';

        const yFormatter = value =>
            value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';
        const tooltipFormatter = yFormatter;

        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: true },
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            dataLabels: { enabled: false },
            series: [{
                name,
                data
            }],
            xaxis: { categories: nomes },
            yaxis: { labels: { formatter: yFormatter } },
            tooltip: { y: { formatter: tooltipFormatter } },
            colors: ['#2980FF'],
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#vendasChart"), options);
        chart.render();
    }

    if (window.lucroValores && window.lucroValores.length > 0) {
        renderChart('lucro');
        btnLucro.classList.add('btn-primary');
        btnLucro.classList.remove('btn-outline-primary');
        btnVendas.classList.remove('btn-primary');
        btnVendas.classList.add('btn-outline-primary');
    }

    btnVendas.addEventListener('click', function () {
        renderChart('vendas');
        btnVendas.classList.add('btn-primary');
        btnVendas.classList.remove('btn-outline-primary');
        btnLucro.classList.remove('btn-primary');
        btnLucro.classList.add('btn-outline-primary');
    });

    btnLucro.addEventListener('click', function () {
        renderChart('lucro');
        btnLucro.classList.add('btn-primary');
        btnLucro.classList.remove('btn-outline-primary');
        btnVendas.classList.remove('btn-primary');
        btnVendas.classList.add('btn-outline-primary');
    });
});
