document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnQtd = document.getElementById('btnQtd');
    const btnEuros = document.getElementById('btnEuros');

    let chart = null;
    let currentMode = 'qtd'; // modo atual (qtd ou euros)

    function toggleCamposPersonalizado() {
        if (!periodoSelect || !camposPersonalizado) return;
        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }

    toggleCamposPersonalizado();
    if (periodoSelect) periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    function renderChart(mode) {
        const data = mode === 'qtd' ? window.topFornecedoresData.valoresQtd : window.topFornecedoresData.valoresEuros;
        const nomes = mode === 'qtd' ? window.topFornecedoresData.nomesQtd : window.topFornecedoresData.nomesEuros;

        const name = mode === 'qtd' ? 'Nº de Compras' : 'Total (€)';

        const yFormatter = mode === 'qtd'
            ? (value) => Number(value).toFixed(0)
            : (value) => `${Number(value).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;

        const tooltipFormatter = mode === 'qtd'
            ? (value) => `${Number(value).toFixed(0)} compras`
            : (value) => `${Number(value).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;

        const options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true }
            },
            series: [{ name: name, data: data }],
            xaxis: {
                categories: nomes,
                labels: { style: { fontSize: '13px' } }
            },
            yaxis: { labels: { formatter: yFormatter } },
            tooltip: { y: { formatter: tooltipFormatter } },
            plotOptions: {
                bar: { horizontal: false, columnWidth: '55%', endingShape: 'rounded' }
            },
            colors: ['#2980FF'],
            dataLabels: { enabled: true, formatter: yFormatter }
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#topFornecedoresChart"), options);
        chart.render();

        // importante: guardar global para export PDF
        window.topFornecedoresChart = chart;
    }

    function renderTable(mode) {
        const dados = mode === 'qtd' ? window.topFornecedoresData.qtd : window.topFornecedoresData.euros;
        const tbody = document.getElementById('topFornecedoresTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        dados.forEach((c, idx) => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${c.fornecedor ?? ''}</td>
                    <td>${c.nif ?? ''}</td>
                    <td class="text-center">${Number(c.num_compras ?? 0).toLocaleString('pt-PT')}</td>
                    <td class="text-end">${Number(c.total_euros ?? 0).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €</td>
                </tr>
            `);
        });
    }

    function setActiveButton(mode) {
        if (!btnQtd || !btnEuros) return;

        if (mode === 'qtd') {
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

    function updateCsvLink(mode) {
        const btnExportCsv = document.getElementById('btnExportCsv');
        if (!btnExportCsv) return;

        const url = new URL(btnExportCsv.href, window.location.origin);

        // manter os query params do filtro do URL atual
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.forEach((value, key) => url.searchParams.set(key, value));

        // mode
        url.searchParams.set('mode', mode);

        btnExportCsv.href = url.toString();
    }

    // função global para export PDF (chamada pelo botão)
    window.exportFornecedoresPdf = async function () {
        try {
            if (!window.topFornecedoresChart) {
                alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                return;
            }

            const { imgURI } = await window.topFornecedoresChart.dataURI();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "/fornecedores/top5/export/pdf";

            // incluir query params do filtro no form (periodo, datas...)
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            });

            // mode
            const modeInput = document.createElement('input');
            modeInput.type = 'hidden';
            modeInput.name = 'mode';
            modeInput.value = currentMode;
            form.appendChild(modeInput);

            // csrf
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = window.csrfToken;
            form.appendChild(tokenInput);

            // imagem
            const imgInput = document.createElement('input');
            imgInput.type = 'hidden';
            imgInput.name = 'chart_img';
            imgInput.value = imgURI;
            form.appendChild(imgInput);

            document.body.appendChild(form);
            form.submit();
        } catch (e) {
            console.error(e);
            alert('Erro ao exportar PDF. Verifica a consola (F12).');
        }
    };

    // Inicialização
    if (window.topFornecedoresData && Array.isArray(window.topFornecedoresData.valoresQtd) && window.topFornecedoresData.valoresQtd.length > 0) {
        currentMode = 'qtd';
        renderChart('qtd');
        renderTable('qtd');
        setActiveButton('qtd');
        updateCsvLink('qtd');
    }

    if (btnQtd) {
        btnQtd.addEventListener('click', function () {
            currentMode = 'qtd';
            renderChart('qtd');
            renderTable('qtd');
            setActiveButton('qtd');
            updateCsvLink('qtd');
        });
    }

    if (btnEuros) {
        btnEuros.addEventListener('click', function () {
            currentMode = 'euros';
            renderChart('euros');
            renderTable('euros');
            setActiveButton('euros');
            updateCsvLink('euros');
        });
    }
});
