document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnQtd = document.getElementById('btnQtd');
    const btnEuros = document.getElementById('btnEuros');

    // Pode não existir se a página estiver sem dados
    if (!periodoSelect || !camposPersonalizado) return;

    function toggleCamposPersonalizado() {
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }
    toggleCamposPersonalizado();
    periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    // Se não há dados, não tenta renderizar gráfico/tabela
    if (!window.topClientesData || !window.topClientesData.vendas || window.topClientesData.vendas.length === 0) {
        return;
    }

    function setActiveButton(type) {
        if (!btnQtd || !btnEuros) return;

        if (type === 'qtd') {
            btnQtd.classList.add('btn-primary');
            btnQtd.classList.remove('btn-outline-primary');
            btnEuros.classList.remove('btn-primary');
            btnEuros.classList.add('btn-outline-primary');
        } else {
            btnEuros.classList.add('btn-primary');
            btnEuros.classList.remove('btn-outline-primary');
            btnQtd.classList.remove('btn-primary');
            btnQtd.classList.add('btn-outline-primary');
        }
    }

    function renderChart(type) {
        const data = type === 'qtd' ? window.topClientesData.vendas : window.topClientesData.euros;
        const nomes = type === 'qtd' ? window.topClientesData.nomesVendas : window.topClientesData.nomesEuros;

        const name = type === 'qtd' ? 'Nº de Vendas' : 'Total (€)';

        const yFormatter = type === 'qtd'
            ? (value) => Number(value).toFixed(0)
            : (value) => `${Number(value).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;

        const tooltipFormatter = type === 'qtd'
            ? (value) => Number(value).toFixed(0) + ' vendas'
            : (value) => `${Number(value).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;

        const options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true }
            },
            series: [{
                name: name,
                data: data
            }],
            xaxis: {
                categories: nomes,
                labels: { style: { fontSize: '13px' } }
            },
            yaxis: {
                labels: { formatter: yFormatter }
            },
            tooltip: {
                y: { formatter: tooltipFormatter }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            colors: ['#2980FF'],
            dataLabels: {
                enabled: true,
                formatter: yFormatter
            }
        };

        // destruir chart antigo
        if (window.topClientesChart) {
            window.topClientesChart.destroy();
        }

        window.topClientesChart = new ApexCharts(document.querySelector("#topClientesVendasChart"), options);
        window.topClientesChart.render();
    }

    function renderTable(type) {
        const data = type === 'qtd' ? window.topClientesData.tableVendas : window.topClientesData.tableEuros;
        const tbody = document.getElementById('topClientesTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        data.forEach((c, idx) => {
            const linha = `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${c.id ?? ''}</td>
                    <td>${c.cliente ?? ''}</td>
                    <td>${c.nif ?? ''}</td>
                    <td class="text-center">${Number(c.num_vendas ?? 0).toLocaleString('pt-PT')}</td>
                    <td class="text-end">${Number(c.total_euros ?? 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', linha);
        });
    }

    function applyMode(type) {
        // guarda modo para os exports
        window.topClientesMode = type;

        // atualiza link CSV (função está na blade)
        if (typeof atualizarLinkCsvTopClientes === 'function') {
            atualizarLinkCsvTopClientes();
        }

        renderChart(type);
        renderTable(type);
        setActiveButton(type);
    }

    // default
    applyMode('qtd');

    if (btnQtd) {
        btnQtd.addEventListener('click', function () {
            applyMode('qtd');
        });
    }

    if (btnEuros) {
        btnEuros.addEventListener('click', function () {
            applyMode('euros');
        });
    }
});
