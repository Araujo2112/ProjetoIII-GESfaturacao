document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnMais = document.getElementById('btnMais');
    const btnMenos = document.getElementById('btnMenos');
    const btnExportCsv = document.getElementById('btnExportCsv');

    let chart = null;
    let modo = 'mais'; // 'mais' | 'menos'

    function toggleCamposPersonalizado() {
        if (!periodoSelect || !camposPersonalizado) return;

        if (periodoSelect.value === 'personalizado') {
            camposPersonalizado.classList.remove('esconder');
        } else {
            camposPersonalizado.classList.add('esconder');
        }
    }

    function setActiveButtons() {
        if (!btnMais || !btnMenos) return;

        if (modo === 'mais') {
            btnMais.classList.add('btn-primary');
            btnMais.classList.remove('btn-outline-primary');

            btnMenos.classList.remove('btn-primary');
            btnMenos.classList.add('btn-outline-primary');
        } else {
            btnMenos.classList.add('btn-primary');
            btnMenos.classList.remove('btn-outline-primary');

            btnMais.classList.remove('btn-primary');
            btnMais.classList.add('btn-outline-primary');
        }
    }

    function updateExportCsvHref() {
        if (!btnExportCsv) return;

        const url = new URL(btnExportCsv.href, window.location.origin);
        url.searchParams.set('modo', modo);
        btnExportCsv.href = url.toString();
    }

    function getOrderedProdutos() {
        const produtos = (window.topProdutosData && window.topProdutosData.produtos)
            ? [...window.topProdutosData.produtos]
            : [];

        if (modo === 'menos') {
            produtos.sort((a, b) => Number(a.qtd) - Number(b.qtd));
        } else {
            produtos.sort((a, b) => Number(b.qtd) - Number(a.qtd));
        }
        return produtos;
    }

    function renderTable() {
        const tbody = document.getElementById('topProdutosTableBody');
        if (!tbody) return;

        const produtos = getOrderedProdutos();
        tbody.innerHTML = '';

        produtos.forEach((p, idx) => {
            const qtd = Number(p.qtd ?? 0);
            const preco = Number(p.preco_c_iva ?? 0);

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${p.cod ?? ''}</td>
                    <td>${p.nome ?? ''}</td>
                    <td>${p.categoria ?? 'Sem Categoria'}</td>
                    <td class="text-end fw-semibold">${qtd.toLocaleString('pt-PT')}</td>
                    <td class="text-end">${preco.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                </tr>
            `);
        });
    }

    function renderChart() {
        const el = document.querySelector("#maisVendidosChart");
        if (!el) return;

        const produtos = getOrderedProdutos();
        const nomes = produtos.map(p => p.nome);
        const qtds = produtos.map(p => Number(p.qtd ?? 0));

        const options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { show: true },
            },
            series: [{
                name: 'Qtd vendida',
                data: qtds
            }],
            xaxis: {
                categories: nomes
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => Number(val).toLocaleString('pt-PT')
            },
            tooltip: {
                y: {
                    formatter: (value) => Number(value).toLocaleString('pt-PT')
                }
            },
            colors: ['#2980FF'],
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(el, options);
        chart.render();

        // Para o PDF (dataURI)
        window.topProdutosChart = chart;
    }

    function aplicarModo(novoModo) {
        modo = novoModo;
        window.topProdutosModo = modo;     // <- importante para o PDF
        setActiveButtons();
        updateExportCsvHref();
        renderChart();
        renderTable();
    }

    // init
    toggleCamposPersonalizado();
    if (periodoSelect) periodoSelect.addEventListener('change', toggleCamposPersonalizado);

    if (window.topProdutosData && window.topProdutosData.produtos && window.topProdutosData.produtos.length > 0) {
        aplicarModo('mais');
    } else {
        window.topProdutosModo = modo;
        setActiveButtons();
        updateExportCsvHref();
    }

    if (btnMais) btnMais.addEventListener('click', () => aplicarModo('mais'));
    if (btnMenos) btnMenos.addEventListener('click', () => aplicarModo('menos'));
});
