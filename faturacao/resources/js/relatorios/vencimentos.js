document.addEventListener('DOMContentLoaded', function () {

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
                    data: window.vendasTotais
                },
                {
                    name: 'Compras a Pagar (€)',
                    type: 'column',
                    data: window.comprasTotais
                },
                {
                    name: 'Cashflow Líquido (€)',
                    type: 'line',
                    data: cashflowData
                }
            ],
            xaxis: {
                categories: window.faturasDatas,
                title: { text: 'Dias' }
            },
            yaxis: [
                {
                    title: { text: 'Valores (€)' },
                    labels: {
                        formatter: function (value) {
                            return '€ ' + Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 0 });
                        }
                    }
                },
                {
                    opposite: true,
                    title: { text: 'Cashflow (€)' },
                    labels: {
                        formatter: function (value) {
                            return '€ ' + Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 0 });
                        }
                    }
                }
            ],
            stroke: { width: [0, 0, 3] },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (value) {
                        return '€ ' + Number(value || 0).toLocaleString('pt-PT', { minimumFractionDigits: 2 });
                    }
                }
            },
            dataLabels: { enabled: false }
        };

        window.cashflowChartInstance = new ApexCharts(cashflowChartElement, cashflowOptions);
        window.cashflowChartInstance.render();
    }

    // === VENDAS ===
    const vendasChartElement = document.getElementById('vendasChart');
    if (vendasChartElement && window.faturasDatas && window.vendasTotais && window.vendasTotais.length > 0) {

        const vendasOptions = {
            chart: {
                type: 'line',
                height: 350,
                zoom: { enabled: true, type: 'x', autoScaleYaxis: true }
            },
            series: [{
                name: 'Faturas a vencer',
                data: window.vendasTotais
            }],
            xaxis: {
                categories: window.faturasDatas,
                title: { text: 'Dias' }
            },
            yaxis: {
                labels: { formatter: (value) => '€ ' + Number(value || 0).toFixed(2) },
                title: { text: 'Valor (€)' }
            },
            tooltip: {
                y: { formatter: (value) => '€ ' + Number(value || 0).toFixed(2) }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' }
        };

        window.vendasChartInstance = new ApexCharts(vendasChartElement, vendasOptions);
        window.vendasChartInstance.render();
    }

    const comprasChartElement = document.getElementById('comprasChart');
    if (comprasChartElement && window.faturasDatas && window.comprasTotais && window.comprasTotais.length > 0) {

        const comprasOptions = {
            chart: {
                type: 'line',
                height: 350,
                zoom: { enabled: true, type: 'x', autoScaleYaxis: true }
            },
            series: [{
                name: 'Compras a vencer',
                data: window.comprasTotais
            }],
            xaxis: {
                categories: window.faturasDatas,
                title: { text: 'Dias' }
            },
            yaxis: {
                labels: { formatter: (value) => '€ ' + Number(value || 0).toFixed(2) },
                title: { text: 'Valor (€)' }
            },
            tooltip: {
                y: { formatter: (value) => '€ ' + Number(value || 0).toFixed(2) }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' }
        };

        window.comprasChartInstance = new ApexCharts(comprasChartElement, comprasOptions);
        window.comprasChartInstance.render();
    }
});
