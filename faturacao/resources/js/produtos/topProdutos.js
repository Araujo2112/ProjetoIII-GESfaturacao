document.addEventListener('DOMContentLoaded', function () {
    const periodoSelect = document.getElementById('periodoSelect');
    const camposPersonalizado = document.getElementById('camposPersonalizado');
    const btnMais = document.getElementById('btnMais');
    const btnMenos = document.getElementById('btnMenos');
    let chart;
    let modo = 'mais';

    function toggleCamposPersonalizado() {
        if (periodoSelect && camposPersonalizado) {
            if (periodoSelect.value === 'personalizado') {
                camposPersonalizado.classList.remove('esconder');
            } else {
                camposPersonalizado.classList.add('esconder');
            }
        }
    }

    toggleCamposPersonalizado();
    if (periodoSelect) {
        periodoSelect.addEventListener('change', toggleCamposPersonalizado);
    }

    function atualizarBotoes() {
        if (!btnMais || !btnMenos) return;

        btnMais.classList.remove('active');
        btnMenos.classList.remove('active');

        if (modo === 'mais') {
            btnMais.classList.add('active');
        } else {
            btnMenos.classList.add('active');
        }
    }

    function renderChart() {
        const dados = window.maisVendidosData || { nomes: [], qtds: [] };
        let nomes = dados.nomes || [];
        let qtds = dados.qtds || [];

        if (modo === 'menos') {
            const combinado = nomes.map((n, i) => ({ nome: n, qtd: qtds[i] }));
            combinado.sort((a, b) => a.qtd - b.qtd);
            nomes = combinado.map(i => i.nome);
            qtds = combinado.map(i => i.qtd);
        }

        const options = {
            chart: {
                type: 'bar',
                height: 350,
                toolbar: { 
                    show: true 
                },
            },
            series: [{
                name: 'Qtd vendida',
                data: qtds
            }],
            xaxis: {
                categories: nomes,
            },
            dataLabels: {
                enabled: true
            },
            tooltip: {
                y: {
                    formatter: value => value.toLocaleString('pt-PT')
                }
            },
            colors: ['#2980FF'],
        };

        if (chart) chart.destroy();
        const el = document.querySelector("#maisVendidosChart");
        if (!el) return;
        chart = new ApexCharts(el, options);
        chart.render();

        atualizarBotoes();
    }

    if (btnMais) {
        btnMais.addEventListener('click', function () {
            modo = 'mais';
            renderChart();
        });
    }

    if (btnMenos) {
        btnMenos.addEventListener('click', function () {
            modo = 'menos';
            renderChart();
        });
    }

    if (window.maisVendidosData && window.maisVendidosData.qtds && window.maisVendidosData.qtds.length > 0) {
        renderChart();
    } else {
        atualizarBotoes();
    }
});
