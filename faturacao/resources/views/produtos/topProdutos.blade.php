@extends('layout')

@section('title', 'Top 5 Artigos')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="text-dark text-center m-0 flex-grow-1">Top 5 Artigos</h1>

                {{-- Export (direita) --}}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportTopProdutosPdf()">
                        Exportar PDF
                    </button>

                    <a id="btnExportCsv" class="btn btn-outline-secondary btn-sm" href="{{ route('artigos.ranking.export.csv', request()->query()) }}">
                        Exportar CSV
                    </a>
                </div>
            </div>

            {{-- Filtro --}}
            <form method="GET" class="d-flex align-items-center mb-4" style="gap:1rem;" id="filtroForm">
                <label class="mb-0 fw-semibold">Período:</label>
                <select name="periodo" id="periodoSelect" class="form-select" style="width:auto;">
                    <option value="semana" {{ request('periodo')=='semana' ? 'selected' : '' }}>Semana Atual</option>
                    <option value="ultima_semana" {{ request('periodo')=='ultima_semana' ? 'selected' : '' }}>Última Semana</option>
                    <option value="mes" {{ request('periodo')=='mes' ? 'selected' : '' }}>Mês Atual</option>
                    <option value="ultimo_mes" {{ request('periodo')=='ultimo_mes' ? 'selected' : '' }}>Último Mês</option>
                    <option value="personalizado" {{ request('periodo')=='personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>

                <div id="camposPersonalizado" class="d-flex align-items-center esconder" style="gap:0.5rem;">
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="form-control" style="width:auto;">
                    <span class="mx-1">a</span>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="form-control" style="width:auto;">
                </div>

                <button type="submit" class="btn btn-primary" style="width:auto; max-width:150px; white-space:nowrap;">Aplicar Filtro</button>
            </form>

            {{-- Conteúdo --}}
            @if(empty($produtos) || count($produtos) == 0)
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Sem dados de vendas para o período selecionado</h4>
                    <div class="text-muted">
                        Tente ajustar o filtro para encontrar dados.
                    </div>
                </div>
            @else
                <div class="row mt-4">
                    <div class="bg-light px-3 py-2 rounded border d-flex align-items-center justify-content-between">
                        <div>
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $periodoTexto ?? 'Semana atual' }}
                        </div>
                        <div class="btn-group" role="group" aria-label="Botões gráfico">
                            <button type="button" id="btnMais" class="btn btn-outline-primary">+ Vendidos</button>
                            <button type="button" id="btnMenos" class="btn btn-outline-primary">- Vendidos</button>
                        </div>
                    </div>

                    <div class="col-12">
                        <div id="maisVendidosChart" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="row d-flex align-items-stretch mt-4">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cód.</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th class="text-end">Qtd Vendida</th>
                                    <th class="text-end">Preço c/IVA (€)</th>
                                </tr>
                            </thead>
                            <tbody id="topProdutosTableBody"></tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if(!empty($produtos) && count($produtos) > 0)
        <script>
            window.topProdutosData = {
                produtos: @json($produtos),
                grafico: @json($graficoDados),
            };
            window.csrfToken = "{{ csrf_token() }}";

            async function exportTopProdutosPdf() {
                try {
                    if (!window.topProdutosChart) {
                        alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                        return;
                    }

                    // modo atual vem do JS (ele vai colocar window.topProdutosModo)
                    const modo = window.topProdutosModo || 'mais';

                    const { imgURI } = await window.topProdutosChart.dataURI();

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('artigos.ranking.export.pdf', request()->query()) }}";

                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${window.csrfToken}">
                        <input type="hidden" name="chart_img" value="${imgURI}">
                        <input type="hidden" name="modo" value="${modo}">
                    `;

                    document.body.appendChild(form);
                    form.submit();
                } catch (e) {
                    console.error(e);
                    alert('Erro ao exportar PDF. Verifica a consola.');
                }
            }
        </script>
    @else
        <script>
            function exportTopProdutosPdf() {
                alert('Sem dados para exportar.');
            }
        </script>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/produtos/topProdutos.js'])
@endpush
