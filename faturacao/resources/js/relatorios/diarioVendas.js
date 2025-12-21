document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnVendas = document.getElementById('btnVendas');
    const btnLucro = document.getElementById('btnLucro');
    let chart;

    function toggleCamposPersonalizado() {
        if (!periodoSelect || !camposPersonalizado) return;
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.style.display = '';
        } else {
            camposPersonalizado.style.display = 'none';
        }
    }

    toggleCamposPersonalizado();
    if (periodoSelect) periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function setButtons(active) {
        if (!btnVendas || !btnLucro) return;

        if (active === 'vendas') {
            btnVendas.classList.add('btn-primary');
            btnVendas.classList.remove('btn-outline-primary');
            btnLucro.classList.remove('btn-primary');
            btnLucro.classList.add('btn-outline-primary');
        } else {
            btnLucro.classList.add('btn-primary');
            btnLucro.classList.remove('btn-outline-primary');
            btnVendas.classList.remove('btn-primary');
            btnVendas.classList.add('btn-outline-primary');
        }
    }

    function renderChart(type) {
        const isVendas = type === 'vendas';

        const data  = isVendas ? (window.vendasValores || []) : (window.lucroValores || []);
        const nomes = isVendas ? (window.vendasDatas || [])   : (window.lucroDatas || []);
        const name  = isVendas ? 'Vendas c/ IVA (€)' : 'Lucro (€)';

        const yFormatter = (value) =>
            Number(value).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' €';

        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: true },
                zoom: { enabled: true, type: 'x', autoScaleYaxis: true }
            },
            dataLabels: { enabled: false },
            series: [{ name, data }],
            xaxis: { categories: nomes },
            yaxis: { labels: { formatter: yFormatter } },
            tooltip: { y: { formatter: yFormatter } },
        };

        const el = document.querySelector("#vendasChart");
        if (!el) return;

        if (chart) chart.destroy();
        chart = new ApexCharts(el, options);
        chart.render();

        // PARA EXPORT
        window.diarioVendasChart = chart;
        window.diarioModo = isVendas ? 'vendas' : 'lucro';

        setButtons(window.diarioModo);
    }

    // default
    if (window.lucroValores && window.lucroValores.length > 0) {
        renderChart('lucro');
    } else if (window.vendasValores && window.vendasValores.length > 0) {
        renderChart('vendas');
    }

    if (btnVendas) btnVendas.addEventListener('click', () => renderChart('vendas'));
    if (btnLucro) btnLucro.addEventListener('click', () => renderChart('lucro'));
});
