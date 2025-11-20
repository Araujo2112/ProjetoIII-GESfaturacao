document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnQtd = document.getElementById('btnQtd');
    const btnEuros = document.getElementById('btnEuros');
    let chart;

    function toggleCamposPersonalizado() {
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }
    toggleCamposPersonalizado();
    periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function renderChart(type) {
        const data = type === 'qtd' ? window.topClientesData.vendas : window.topClientesData.euros;
        const nomes = type === 'qtd' ? window.topClientesData.nomesVendas : window.topClientesData.nomesEuros;
        const name = type === 'qtd' ? 'Nº de Vendas' : 'Total (€)';
        const yFormatter = type === 'qtd'
            ? (value) => value.toFixed(0)
            : (value) => `${value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
        const tooltipFormatter = type === 'qtd'
            ? (value) => value.toFixed(0) + ' vendas'
            : (value) => `${value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;

        const options = {
            chart: { type: 'bar', height: 350, toolbar: { show: false }},
            series: [{ name: name, data: data }],
            xaxis: {
                categories: nomes,
                labels: { rotate: -45, style: { fontSize: '13px' } }
            },
            yaxis: { labels: { formatter: yFormatter } },
            tooltip: { y: { formatter: tooltipFormatter } },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            colors: ['#2980FF'],
            dataLabels: { enabled: true, formatter: yFormatter }
        };
        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#topClientesVendasChart"), options);
        chart.render();
    }


    function renderTable(type) {
        const data = type === 'qtd' ? window.topClientesData.tableVendas : window.topClientesData.tableEuros;
        const tbody = document.getElementById('topClientesTableBody');
        tbody.innerHTML = '';
        data.forEach((c, idx) => {
            const linha = `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${c.id}</td>
                    <td>${c.cliente}</td>
                    <td>${c.nif}</td>
                    <td class="text-center">${Number(c.num_vendas).toLocaleString('pt-PT')}</td>
                    <td class="text-end">${Number(c.total_euros).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', linha);
        });
    }

    // Inicialização padrão
    if (window.topClientesData && window.topClientesData.vendas.length > 0) {
        renderChart('qtd');
        renderTable('qtd');
        btnQtd.classList.add('btn-primary');
        btnQtd.classList.remove('btn-outline-primary');
        btnEuros.classList.remove('btn-primary');
        btnEuros.classList.add('btn-outline-primary');
    }

    // Listeners
    btnQtd.addEventListener('click', function() {
        renderChart('qtd');
        renderTable('qtd');
        btnQtd.classList.add('btn-primary');
        btnQtd.classList.remove('btn-outline-primary');
        btnEuros.classList.remove('btn-primary');
        btnEuros.classList.add('btn-outline-primary');
    });
    btnEuros.addEventListener('click', function() {
        renderChart('euros');
        renderTable('euros');
        btnEuros.classList.add('btn-primary');
        btnEuros.classList.remove('btn-outline-primary');
        btnQtd.classList.remove('btn-primary');
        btnQtd.classList.add('btn-outline-primary');
    });
});
