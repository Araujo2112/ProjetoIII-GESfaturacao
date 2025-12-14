document.addEventListener('DOMContentLoaded', function() {

// === GRÁFICO CASHFLOW ===
const cashflowChartElement = document.getElementById('cashflowChart');
if (cashflowChartElement && window.faturasDatas && window.vendasTotais && window.comprasTotais) {

    const cashflowData = window.vendasTotais.map((vendas, index) => 
        vendas - (window.comprasTotais[index] || 0)
    );

    const cashflowOptions = {
        chart: {
            type: 'line',
            height: 350
        },
        series: [
            {
                name: 'Vendas a Receber (€)',
                type: 'column',
                data: window.vendasTotais,
                color: '#00A86B'
            },
            {
                name: 'Compras a Pagar (€)',
                type: 'column',
                data: window.comprasTotais,
                color: '#FF5733'
            },
            {
                name: 'Cashflow Líquido (€)',
                type: 'line',
                data: cashflowData,
                color: '#FFD700'
            }
        ],
        xaxis: {
            categories: window.faturasDatas,
            title: { text: 'Dias' }
        },
        yaxis: [
            {
                seriesName: ['Vendas a Receber (€)', 'Compras a Pagar (€)'],
                title: { text: 'Valores (€)' },
                labels: {
                    formatter: function(value) {
                        return '€ ' + value.toLocaleString('pt-PT', {minimumFractionDigits: 0});
                    }
                }
            },
            {
                seriesName: ['Cashflow Líquido (€)'],
                opposite: true,
                title: { text: 'Cashflow (€)' },
                labels: {
                    formatter: function(value) {
                        return '€ ' + value.toLocaleString('pt-PT', {minimumFractionDigits: 0});
                    }
                }
            }
        ],
        stroke: {
            width: [0, 0, 3]
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(value) {
                    return '€ ' + value.toLocaleString('pt-PT', {minimumFractionDigits: 2});
                }
            }
        },
        dataLabels: {
            enabled: false
        }
    };

    const cashflowChart = new ApexCharts(cashflowChartElement, cashflowOptions);
    cashflowChart.render();
}


    // === GRÁFICO VENDAS ===
    const vendasChartElement = document.getElementById('vendasChart');
    if (vendasChartElement && window.faturasDatas && window.vendasTotais && window.vendasTotais.length > 0) {
        const vendasOptions = {
            chart: {
                type: 'line',
                height: 350,
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            series: [{
                name: 'Faturas a vencer',
                data: window.vendasTotais
            }],
            xaxis: {
                categories: window.faturasDatas,
                title: {
                    text: 'Dias'
                }
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return '€ ' + value.toFixed(2);
                    }
                },
                title: {
                    text: 'Valor (€)'
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            }
        };

        const vendasChart = new ApexCharts(vendasChartElement, vendasOptions);
        vendasChart.render();
    } else {
        console.log('Sem dados para gráfico de vendas');
    }


    // === GRÁFICO COMPRAS ===
    const comprasChartElement = document.getElementById('comprasChart');
    if (comprasChartElement && window.faturasDatas && window.comprasTotais && window.comprasTotais.length > 0) {
        const comprasOptions = {
            chart: {
                type: 'line',
                height: 350,
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            series: [{
                name: 'Compras a vencer',
                data: window.comprasTotais
            }],
            xaxis: {
                categories: window.faturasDatas,
                title: {
                    text: 'Dias'
                }
            },
            yaxis: {
                labels: {
                    formatter: function(value) {
                        return '€ ' + value.toFixed(2);
                    }
                },
                title: {
                    text: 'Valor (€)'
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            }
        };

        const comprasChart = new ApexCharts(comprasChartElement, comprasOptions);
        comprasChart.render();
    } else {
        console.log('Sem dados para gráfico de compras');
    }
});
