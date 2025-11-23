document.addEventListener('DOMContentLoaded', function () {
    if (window.dashboardData) {
        // Area chart
        const areaOptions = {
            chart: {
                type: 'area',
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
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.8,
                    stops: [0, 100]
                }
            },
            grid: {
                borderColor: "#e4e7ed"
            }
        };

        const areaChart = new ApexCharts(document.querySelector("#faturacaoChart"), areaOptions);
        areaChart.render();

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
                    toolbar: {
                        show: true
                    }
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
                    toolbar: { show: true }
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
                colors: ['#E67E22', '#D35400'],
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
    }
});
