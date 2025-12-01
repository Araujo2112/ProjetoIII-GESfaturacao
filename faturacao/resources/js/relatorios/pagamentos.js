document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnEvolucao = document.getElementById('btnEvolucao');
    const btnTop = document.getElementById('btnTop');
    const evolucaoChartDiv = document.getElementById('evolucaoChart');
    const topChartDiv = document.getElementById('topChart');
    let evolucaoChart = null;
    let topChart = null;

    function toggleCamposPersonalizado() {
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }
    toggleCamposPersonalizado();
    periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function converterDataFmtParaYMD(dataFmt) {
        const anoAtual = new Date().getFullYear();
        const [dia, mes] = dataFmt.split('-');
        const diaPadded = dia.padStart(2, '0');
        const mesPadded = mes.padStart(2, '0');
        return `${anoAtual}-${mesPadded}-${diaPadded}`;
    }

    function renderEvolucaoChart() {
        const quantidadePorDia = window.pagamentosQuantidadePorDia || {};
        const datasFormatadas = window.pagamentosDatas || [];
        const name = 'Quantidade de Pagamentos';

        const data = datasFormatadas.map(dataFmt => {
            const dataYMD = converterDataFmtParaYMD(dataFmt);
            return quantidadePorDia[dataYMD] ?? 0;
        });

        const options = {
            chart: {
                type: 'line',
                height: 350,
                toolbar: {
                    show: true
                }
            },
            dataLabels: {
                enabled: false
            },
            series: [{ 
                name,
                data
            }],
            xaxis: {
                categories: datasFormatadas
            },
            colors: [
                '#2980FF'
            ]
        };

        if (evolucaoChart) evolucaoChart.destroy();
        evolucaoChart = new ApexCharts(evolucaoChartDiv, options);
        evolucaoChart.render();
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
            legend: { position: 'bottom' },
        };

        if (topChart) topChart.destroy();
        topChart = new ApexCharts(topChartDiv, options);
        topChart.render();
    }

    function setActiveChart(chartName) {
        if (chartName === 'evolucao') {
            evolucaoChartDiv.style.display = 'block';
            topChartDiv.style.display = 'none';
            btnEvolucao.classList.add('active');
            btnTop.classList.remove('active');
            renderEvolucaoChart();
        } else if (chartName === 'top'){
            evolucaoChartDiv.style.display = 'none';
            topChartDiv.style.display = 'block';
            btnEvolucao.classList.remove('active');
            btnTop.classList.add('active');
            renderTopChart();
        }
    }

    btnEvolucao.addEventListener('click', () => setActiveChart('evolucao'));
    btnTop.addEventListener('click', () => setActiveChart('top'));

    setActiveChart('evolucao');
});
