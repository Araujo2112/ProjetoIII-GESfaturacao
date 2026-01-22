//quando a página carrega
document.addEventListener('DOMContentLoaded', function () {
    //dados de linha nos últimos 7 dias
    const lineData =
        window.dashboardData &&
        Array.isArray(window.dashboardData.totais) &&
        window.dashboardData.totais.some(val => val > 0);

    //valores por categoria
    const categoriasValores =
        window.dashboardCategorias &&
        typeof window.dashboardCategorias === 'object' &&
        window.dashboardCategorias.valores &&
        Object.values(window.dashboardCategorias.valores).some(val => val > 0);

    //quantiades por dia
    const categoriasQtd =
        window.dashboardCategorias &&
        window.dashboardCategorias.quantidadesPorDia &&
        Object.values(window.dashboardCategorias.quantidadesPorDia).some(obj =>
            obj && Object.values(obj).some(val => val > 0)
        );

    //se estiver tudo bem, desenha o gráfico dos últimos 7 dias
    if (lineData && categoriasValores && categoriasQtd) {
        const areaOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: {
                    show: true,
                    autoSelected: 'zoom'
                },
                zoom: {
                    enabled: true,
                    type: 'x',
                    autoScaleYaxis: true
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            series: [{
                name: 'Faturação',
                data: window.dashboardData.totais
            }],
            xaxis: {
                categories: window.dashboardData.datas
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return '€ ' + value;
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return '€ ' + value;
                    }
                }
            },
            colors: ['#2980FF'],
            grid: {
                borderColor: "#e4e7ed"
            }
        };

        //renderiza
        const areaChart = new ApexCharts(document.querySelector("#faturacaoChart"), areaOptions);
        areaChart.render();

        //pie char € por categoria
        const valoresCategorias = window.dashboardCategorias.valores;
        const categoriasOrdenadas = Object.keys(valoresCategorias).sort();

        const pieOptions = {
            chart: {
                type: 'pie',
                height: 350
            },
            dataLabels: {
                enabled: true
            },
            series: categoriasOrdenadas.map(cat => valoresCategorias[cat]),
            labels: categoriasOrdenadas,
            colors: ['#2980FF',
                    '#FF6B00',
                    '#FFCC00',
                    '#00C853',
                    '#10f1d3ff',
                    '#D500F9',
                    '#FF1744',
                    '#FF4081',
                    '#8D6E63',
                    '#455A64',
                    '#F5F5F5',
                    '#263238',
                    '#FFD180',
                    '#B3E5FC',
                    '#C8E6C9'
            ],
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px'
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
        };

        const pieChart = new ApexCharts(
            document.querySelector("#categorias€Chart"),
            pieOptions
        );
        pieChart.render();

        const qtdPorDia = window.dashboardCategorias.quantidadesPorDia;
        const categoriasLabels = Object.keys(qtdPorDia).sort();

        const categoriasSet = new Set();
        categoriasLabels.forEach(dia => {
            const catsNoDia = qtdPorDia[dia] || {};
            Object.keys(catsNoDia).forEach(cat => categoriasSet.add(cat));
        });
        const categoriasLista = Array.from(categoriasSet).sort();

        const series = categoriasLista.map(categoria => ({
            name: categoria,
            data: categoriasLabels.map(dia => {
                const val = qtdPorDia[dia]?.[categoria];
                return val === undefined ? null : val;
            })
        }));

        const categoriasLabelsFormatadas = categoriasLabels.map(dataStr => {
            const [ano, mes, dia] = dataStr.split('-');
            return `${dia}/${mes}`;
        });

        const options = {
            chart: {
                type: 'bar',
                height: 400,
                stacked: true
            },
            dataLabels: { enabled: false },
            series: series,
            xaxis: {
                categories: categoriasLabelsFormatadas,
                title: { text: 'Dia' }
            },
            yaxis: {
                title: { text: 'Quantidade' }
            },
            tooltip: {
                shared: true,
                intersect: false
            },
                        colors: ['#2980FF',
                    '#FF6B00',
                    '#FFCC00',
                    '#00C853',
                    '#10f1d3ff',
                    '#D500F9',
                    '#FF1744',
                    '#FF4081',
                    '#8D6E63',
                    '#455A64',
                    '#F5F5F5',
                    '#263238',
                    '#FFD180',
                    '#B3E5FC',
                    '#C8E6C9'
            ],
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontSize: '14px'
            },
        };

        const chart = new ApexCharts(document.querySelector("#categoriasQtdChart"), options);
        chart.render();

    } else {

    }


    // Bar Chart - Vendas
    if (
        window.dashboardData.mesesLabels &&
        window.dashboardData.faturacaoAnoAtual &&
        window.dashboardData.faturacaoAnoAnterior
    ) {
        const barOptions = {
            chart: {
                type: 'bar',
                height: 300,
            },
            dataLabels: {
                enabled: false
            },
            series: [
                {
                    name: 'Ano Atual',
                    data: window.dashboardData.faturacaoAnoAtual
                },
                {
                    name: 'Ano Anterior',
                    data: window.dashboardData.faturacaoAnoAnterior
                }
            ],
            xaxis: {
                categories: window.dashboardData.mesesLabels
            },
            yaxis: {
                    labels: {
                    formatter: function (value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            colors: ['#2980FF', '#A0AEC0'],
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                offsetY: 0,
                fontSize: '14px',
                markers: {
                    radius: 12,
                    width: 12,
                    height: 12
                }
            },
            grid: {
                borderColor: "#e4e7ed"
            },
            plotOptions: {
                bar: {
                    columnWidth: '50%'
                }
            }
        };

        const barChart = new ApexCharts(document.querySelector("#faturacaoMesChart"), barOptions);
        barChart.render();
    }

    // Bar Chart - Compras
    if (window.dashboardCompras && window.dashboardCompras.mesesLabels) {
        const comprasBarOptions = {
            chart: {
                type: 'bar',
                height: 300,
            },
            dataLabels: {
                enabled: false
            },
            series: [
                {
                    name: 'Ano Atual',
                    data: window.dashboardCompras.comprasAnoAtual
                },
                {
                    name: 'Ano Anterior',
                    data: window.dashboardCompras.comprasAnoAnterior
                }
            ],
            xaxis: {
                categories: window.dashboardCompras.mesesLabels
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return '€ ' + value.toFixed(2);
                    }
                }
            },
            colors: ['#f1811fff', '#da0f0fff'],
            plotOptions: {
            bar: {
                    columnWidth: '50%'
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center'
            },
            grid: {
                borderColor: "#e4e7ed"
            }
        };

        const comprasBarChart = new ApexCharts(document.querySelector("#comprasMesChart"), comprasBarOptions);
        comprasBarChart.render();
    }
});
