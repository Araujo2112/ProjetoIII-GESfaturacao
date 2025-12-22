document.addEventListener('DOMContentLoaded', function () {
    const btnVendas = document.getElementById('btnVendas');
    const btnLucro = document.getElementById('btnLucro');

    // modo atual (para export)
    window.mensalModo = 'lucro';

    function setActiveButton(modo) {
        if (!btnVendas || !btnLucro) return;

        if (modo === 'vendas') {
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

    function renderChart(modo) {
        window.mensalModo = modo;

        const isVendas = modo === 'vendas';
        const data  = isVendas ? (window.vendasValores || []) : (window.lucroValores || []);
        const nomes = isVendas ? (window.vendasMeses || [])   : (window.lucroMeses || []);
        const name  = isVendas ? 'Vendas c/ IVA (€)'          : 'Lucro (€)';

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
            colors: ['#2980FF'],
        };

        if (window.mensalChart) {
            try { window.mensalChart.destroy(); } catch (e) {}
        }

        const el = document.querySelector("#vendasChart");
        if (!el) return;

        window.mensalChart = new ApexCharts(el, options);
        window.mensalChart.render();

        setActiveButton(modo);
    }

    // default: lucro
    if (window.lucroValores && window.lucroValores.length > 0) {
        renderChart('lucro');
    } else {
        setActiveButton('lucro');
    }

    if (btnVendas) btnVendas.addEventListener('click', () => renderChart('vendas'));
    if (btnLucro) btnLucro.addEventListener('click', () => renderChart('lucro'));
});
