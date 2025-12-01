@extends('layout')

@section('title', 'Top 5 Produtos')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Top 5 Produtos</h1>

            {{-- Filtro --}}
            <form method="GET" class="d-flex align-items-center mb-4" style="gap:1rem;" id="filtroForm">
                <label class="mb-0 fw-semibold">Período:</label>
                <select name="periodo" id="periodoSelect" class="form-select" style="width:auto;">
                    <option value="geral" {{ request('periodo')=='geral' ? 'selected' : '' }}>Geral</option>
                    <option value="hoje" {{ request('periodo')=='hoje' ? 'selected' : '' }}>Hoje</option>
                    <option value="ontem" {{ request('periodo')=='ontem' ? 'selected' : '' }}>Ontem</option>
                    <option value="mes" {{ request('periodo')=='mes' ? 'selected' : '' }}>Mês</option>
                    <option value="ultimo_mes" {{ request('periodo')=='ultimo_mes' ? 'selected' : '' }}>Último Mês</option>
                    <option value="ano" {{ request('periodo')=='ano' ? 'selected' : '' }}>Ano</option>
                    <option value="ultimo_ano" {{ request('periodo')=='ultimo_ano' ? 'selected' : '' }}>Último Ano</option>
                    <option value="personalizado" {{ request('periodo')=='personalizado' ? 'selected' : '' }}>Personalizado</option>
                </select>
                <div id="camposPersonalizado" class="d-flex align-items-center" style="gap:0.5rem;">
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="form-control" style="width:auto;">
                    <span class="mx-1">a</span>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="form-control" style="width:auto;">
                </div>

                <button type="submit" class="btn btn-primary" style="width:auto; max-width:150px; white-space:nowrap;">Aplicar Filtro</button>
            </form>

            {{-- Gráfico/Tabela --}}
            @if(empty($top5ProdutosQtd) || count($top5ProdutosQtd) == 0)
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Sem dados em Produtos</h4>
                    <div class="text-muted">
                        Não existem dados disponíveis neste filtro.<br>
                        Ajuste o filtro.
                    </div>
                </div>
            @else

            <div class="row mt-5">
                <div class="bg-light px-3 py-2 rounded border d-flex align-items-center justify-content-between">
                    <div>
                        <i class="far fa-calendar-alt me-2"></i>
                        {{ $periodoTexto ?? 'Todos os dados disponíveis' }}
                    </div>
                    <div class="btn-group" role="group" aria-label="Botões gráfico">
                        <button type="button" id="btn+Vendido" class="btn btn-outline-primary active">+ Vendidos</button>
                        <button type="button" id="btn-Vendido" class="btn btn-outline-primary">- Vendidos</button>
                        <button type="button" id="btnLucro" class="btn btn-outline-primary">% Lucro</button>
                        <button type="button" id="btnStock" class="btn btn-outline-primary">Stock Baixo</button>
                    </div>
                </div>
                <div class="col-12">
                    <div id="topProdutosChart" style="height: 350px;"></div>
                </div>
            </div>

            <div class="row d-flex align-items-stretch mt-4">
                <div style="overflow-x:auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead id="topProdutosTableHead"></thead>
                        <tbody id="topProdutosTableBody"></tbody>
                    </table>
                </div>
            </div>

            @endif
        </div>
    </div>

    @if(!empty($top5ProdutosQtd) && !empty($top5ProdutosQtdBaixo) && !empty($top5ProdutosLucro) && !empty($produtosStockBaixo))
        <script>
        window.topProdutosData = {
            maisVendidos: @json($top5ProdutosQtd),
            menosVendidos: @json($top5ProdutosQtdBaixo),
            maiorLucro: @json($top5ProdutosLucro),
            stockBaixo: @json($produtosStockBaixo),
            
            qtdMaisVendidos: @json($top5ProdutosQtd->pluck('qtd')->all()),
            qtdMenosVendidos: @json($top5ProdutosQtdBaixo->pluck('qtd')->all()),
            lucroMaisVendidos: @json($top5ProdutosLucro->pluck('lucro')->all()),
            stockAtualBaixo: @json($produtosStockBaixo->pluck('stock_atual')->all()),
            
            nomesMaisVendidos: @json($top5ProdutosQtd->pluck('nome')->all()),
            nomesMenosVendidos: @json($top5ProdutosQtdBaixo->pluck('nome')->all()),
            nomesLucro: @json($top5ProdutosLucro->pluck('nome')->all()),
            nomesStockBaixo: @json($produtosStockBaixo->pluck('nome')->all())
        }
        </script>
    @endif

    
@endsection

@push('scripts')
    @vite(['resources/js/produtos/topProdutos.js'])
@endpush
