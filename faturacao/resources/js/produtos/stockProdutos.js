// resources/js/produtos/stockBaixo.js
document.addEventListener('DOMContentLoaded', function() {
    // Verifica se tem dados
    if (!window.stockBaixoData || window.stockBaixoData.diferencas.length === 0) {
        console.log('Sem dados para gráfico de stock baixo');
        return;
    }

    const nomes = window.stockBaixoData.nomes;
    const diferencas = window.stockBaixoData.diferencas;

    const options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { 
                show: true,
            }
        },
        series: [{
            name: 'Falta Repor',
            data: diferencas
        }],
        xaxis: {
            categories: nomes,
        },
        yaxis: {
            title: {
                text: 'Unidades a Repor'
            },
            labels: {
                formatter: function (value) {
                    return value.toFixed(1);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return value.toFixed(2);
                }
            },
            x: {
                formatter: function(value) {
                    return 'Produto: ' + value;
                }
            }
        },
        colors: ['#e74c3c'],
        dataLabels: {
            enabled: true,
            formatter: function (value) {
                return value.toFixed(1);
            }
        },
    };

    const chart = new ApexCharts(document.querySelector("#stockBaixoChart"), options);
    chart.render();
});
