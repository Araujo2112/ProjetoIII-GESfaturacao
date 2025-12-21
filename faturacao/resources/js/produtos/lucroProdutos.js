document.addEventListener('DOMContentLoaded', function() {
    if (!window.lucroProdutosData || window.lucroProdutosData.lucros.length === 0) {
        console.log('Sem dados para gráfico de lucro');
        return;
    }

    const nomes = window.lucroProdutosData.nomes;
    const lucrosPercent = window.lucroProdutosData.lucros;

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: true
            }
        },
        series: [{
            name: 'Margem de Lucro (%)',
            data: lucrosPercent
        }],
        xaxis: {
            categories: nomes,
        },
        yaxis: {
            labels: {
                formatter: (value) => value.toFixed(1) + '%'
            },
            title: {
                text: 'Margem (%)'
            }
        },
        tooltip: {
            y: {
                formatter: (value) => value.toFixed(2) + '%'
            }
        },
        colors: ['#27ae60'],
        dataLabels: {
            enabled: true,
            formatter: (value) => value.toFixed(1) + '%',
        },
    };

    window.lucroProdutosChart = new ApexCharts(
        document.querySelector("#lucroProdutosChart"),
        options
    );
    window.lucroProdutosChart.render();
});
