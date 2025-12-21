document.addEventListener('DOMContentLoaded', function () {
    if (!window.stockBaixoData || !window.stockBaixoData.diferencas || window.stockBaixoData.diferencas.length === 0) {
        console.log('Sem dados para gráfico de stock baixo');
        return;
    }

    const nomes = window.stockBaixoData.nomes || [];
    const diferencas = window.stockBaixoData.diferencas || [];

    const el = document.querySelector("#stockBaixoChart");
    if (!el) return;

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: true }
        },
        series: [{
            name: 'Falta Repor',
            data: diferencas
        }],
        xaxis: {
            categories: nomes
        },
        yaxis: {
            title: { text: 'Unidades a Repor' },
            labels: {
                formatter: (value) => Number(value).toFixed(1)
            }
        },
        tooltip: {
            y: { formatter: (value) => Number(value).toFixed(2) },
            x: { formatter: (value) => 'Produto: ' + value }
        },
        colors: ['#e74c3c'],
        dataLabels: {
            enabled: true,
            formatter: (value) => Number(value).toFixed(1)
        }
    };

    // destruir antes de criar novo (evita gráficos duplicados)
    if (window.stockBaixoChart) {
        try { window.stockBaixoChart.destroy(); } catch (e) {}
    }

    // ✅ guardar para export PDF
    window.stockBaixoChart = new ApexCharts(el, options);
    window.stockBaixoChart.render();
});
