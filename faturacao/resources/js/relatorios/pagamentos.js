document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');

    const btnEvolucao = document.getElementById('btnEvolucao');
    const btnTop = document.getElementById('btnTop');

    const evolucaoChartDiv = document.getElementById('evolucaoChart');
    const topChartDiv = document.getElementById('topChart');

    let evolucaoChart = null;
    let topChart = null;

    if (topChartDiv) topChartDiv.style.display = 'none';

    function toggleCamposPersonalizado() {
        if (periodoSelect && periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
            camposPersonalizado.style.display = 'flex';
        } else {
            camposPersonalizado.classList.add('esconder');
            camposPersonalizado.style.display = 'none';
        }
    }
    toggleCamposPersonalizado();
    if (periodoSelect) periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function renderEvolucaoChart() {
        const quantidadePorDia = window.pagamentosQuantidadePorDia || {};
        const datasLabels = window.pagamentosDatas || [];
        const datasKeys = window.pagamentosDatasKeys || [];

        const data = datasKeys.map(key => quantidadePorDia[key] ?? 0);

        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: { show: true }
            },
            dataLabels: { enabled: false },
            series: [{
                name: 'Quantidade de Pagamentos',
                data
            }],
            xaxis: { categories: datasLabels },
            colors: ['#2980FF']
        };

        if (evolucaoChart) evolucaoChart.destroy();
        evolucaoChart = new ApexCharts(evolucaoChartDiv, options);
        evolucaoChart.render();

        window.pagamentosChart = evolucaoChart;
    }

    function renderTopChart() {
        const contagemMetodos = window.contagemMetodosPagamento || {};
        const series = Object.values(contagemMetodos);
        const labels = Object.keys(contagemMetodos);

        const options = {
            chart: { type: 'pie', height: 350 },
            dataLabels: { enabled: true },
            series,
            labels,
            legend: { position: 'bottom' }
        };

        if (topChart) topChart.destroy();
        topChart = new ApexCharts(topChartDiv, options);
        topChart.render();

        window.pagamentosChart = topChart;
    }

    function setActiveChart(chartName) {
        if (!btnEvolucao || !btnTop || !evolucaoChartDiv || !topChartDiv) return;

        if (chartName === 'evolucao') {
            evolucaoChartDiv.style.display = 'block';
            topChartDiv.style.display = 'none';

            btnEvolucao.classList.add('btn-primary');
            btnEvolucao.classList.remove('btn-outline-primary');
            btnTop.classList.add('btn-outline-primary');
            btnTop.classList.remove('btn-primary');

            window.pagamentosModo = 'evolucao';
            renderEvolucaoChart();
        } else {
            evolucaoChartDiv.style.display = 'none';
            topChartDiv.style.display = 'block';

            btnTop.classList.add('btn-primary');
            btnTop.classList.remove('btn-outline-primary');
            btnEvolucao.classList.add('btn-outline-primary');
            btnEvolucao.classList.remove('btn-primary');

            window.pagamentosModo = 'top';
            renderTopChart();
        }
    }

    btnEvolucao.addEventListener('click', () => setActiveChart('evolucao'));
    btnTop.addEventListener('click', () => setActiveChart('top'));

    setActiveChart('evolucao');
});
