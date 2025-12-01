document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnMaisVendidos = document.getElementById('btn+Vendido');
    const btnMenosVendidos = document.getElementById('btn-Vendido');
    const btnLucro = document.getElementById('btnLucro');
    const btnStock = document.getElementById('btnStock');
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

    // Configurações por tipo
    const configPorTipo = {
        'maisVendidos': {
            data: () => window.topProdutosData.maisVendidos,
            graficoData: () => window.topProdutosData.qtdMaisVendidos,
            graficoNomes: () => window.topProdutosData.nomesMaisVendidos,
            graficoName: 'Quantidade Vendida',
            yFormatter: (value) => value.toFixed(0),
            tooltipFormatter: (value) => value.toFixed(0) + ' und',
            tableHeaders: ['#', 'Cód.', 'Nome', 'Categoria', 'Preço c/IVA', 'Preço s/IVA', 'Qtd'],
            renderRow: (item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.cod}</td>
                    <td>${item.nome}</td>
                    <td>${item.categoria}</td>
                    <td class="text-end">${Number(item.preco_c_iva).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.preco_s_iva).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.qtd).toLocaleString('pt-PT')}</td>
                </tr>
            `
        },
        'menosVendidos': {
            data: () => window.topProdutosData.menosVendidos,
            graficoData: () => window.topProdutosData.qtdMenosVendidos,
            graficoNomes: () => window.topProdutosData.nomesMenosVendidos,
            graficoName: 'Quantidade Vendida',
            yFormatter: (value) => value.toFixed(0),
            tooltipFormatter: (value) => value.toFixed(0) + ' und',
            tableHeaders: ['#', 'Cód.', 'Nome', 'Categoria', 'Preço c/IVA', 'Preço s/IVA', 'Qtd'],
            renderRow: (item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.cod}</td>
                    <td>${item.nome}</td>
                    <td>${item.categoria}</td>
                    <td class="text-end">${Number(item.preco_c_iva).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.preco_s_iva).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.qtd).toLocaleString('pt-PT')}</td>
                </tr>
            `
        },
        'lucro': {
            data: () => window.topProdutosData.maiorLucro,
            graficoData: () => window.topProdutosData.lucroMaisVendidos,
            graficoNomes: () => window.topProdutosData.nomesLucro,
            graficoName: 'Lucro (€)',
            yFormatter: (value) => `${value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`,
            tooltipFormatter: (value) => `${value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`,
            tableHeaders: ['#', 'Cód.', 'Nome', 'Categoria', 'Preço s/IVA', 'Custo', 'Lucro'],
            renderRow: (item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.cod}</td>
                    <td>${item.nome}</td>
                    <td>${item.categoria}</td>
                    <td class="text-end">${Number(item.preco_s_iva).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.custo).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                    <td class="text-end">${Number(item.lucro).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}€</td>
                </tr>
            `
        },
        'stock': {
            data: () => window.topProdutosData.stockBaixo,
            graficoData: () => window.topProdutosData.stockBaixo.map(p => Number(p.stock_atual)),
            graficoNomes: () => window.topProdutosData.stockBaixo.map(p => p.nome),
            graficoName: 'Stock Atual',
            yFormatter: (value) => value.toFixed(0),
            tooltipFormatter: (value) => value.toFixed(0) + ' und',
            tableHeaders: ['#', 'Cód.', 'Nome', 'Categoria', 'Stock Atual', 'Stock Mínimo'],
            renderRow: (item, idx) => `
                <tr>
                    <td>${idx + 1}</td>
                    <td>${item.cod}</td>
                    <td>${item.nome}</td>
                    <td>${item.categoria}</td>
                    <td class="text-end">${Number(item.stock_atual).toLocaleString('pt-PT')}</td>
                    <td class="text-end">${Number(item.stock_minimo).toLocaleString('pt-PT')}</td>
                </tr>
            `
        }
    };

    function renderChart(tipo) {
        const config = configPorTipo[tipo];
        if (!config) return;

        const data = config.graficoData();
        const nomes = config.graficoNomes();
        const name = config.graficoName;
        const yFormatter = config.yFormatter;
        const tooltipFormatter = config.tooltipFormatter;

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
                labels: { 
                    style: { fontSize: '13px' } 
                }
            },
            yaxis: { 
                labels: { 
                    formatter: yFormatter 
                } 
            },
            tooltip: { 
                y: { 
                    formatter: tooltipFormatter 
                } 
            },
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
        chart = new ApexCharts(document.querySelector("#topProdutosChart"), options);
        chart.render();
    }

    function renderTable(tipo) {
        const config = configPorTipo[tipo];
        if (!config) return;

        const data = config.data();
        const tbody = document.getElementById('topProdutosTableBody');
        const thead = document.getElementById('topProdutosTableHead');
        
        // Atualizar cabeçalho
        thead.innerHTML = `<tr>${config.tableHeaders.map(h => `<th>${h}</th>`).join('')}</tr>`;
        
        // Limpar e popular corpo
        tbody.innerHTML = '';
        data.forEach((item, idx) => {
            tbody.insertAdjacentHTML('beforeend', config.renderRow(item, idx));
        });
    }

    function setActiveButton(btnAtivo) {
        // Remove active de todos
        [btnMaisVendidos, btnMenosVendidos, btnLucro, btnStock].forEach(btn => {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-outline-primary');
        });
        // Ativa o botão atual
        btnAtivo.classList.add('active', 'btn-primary');
        btnAtivo.classList.remove('btn-outline-primary');
    }

    // Inicialização
    if (window.topProdutosData && window.topProdutosData.maisVendidos.length > 0) {
        renderChart('maisVendidos');
        renderTable('maisVendidos');
        setActiveButton(btnMaisVendidos);
    }

    // Event listeners dos botões
    btnMaisVendidos.addEventListener('click', () => {
        renderChart('maisVendidos');
        renderTable('maisVendidos');
        setActiveButton(btnMaisVendidos);
    });

    btnMenosVendidos.addEventListener('click', () => {
        renderChart('menosVendidos');
        renderTable('menosVendidos');
        setActiveButton(btnMenosVendidos);
    });

    btnLucro.addEventListener('click', () => {
        renderChart('lucro');
        renderTable('lucro');
        setActiveButton(btnLucro);
    });

    btnStock.addEventListener('click', () => {
        renderChart('stock');
        renderTable('stock');
        setActiveButton(btnStock);
    });
});
