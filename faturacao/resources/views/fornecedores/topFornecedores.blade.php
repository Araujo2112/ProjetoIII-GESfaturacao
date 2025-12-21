@extends('layout')

@section('title', 'Top 5 Fornecedores')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">

        {{-- TÍTULO + EXPORT (canto direito) --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="text-dark text-center m-0 flex-grow-1">Top 5 Fornecedores</h1>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportFornecedoresPdf()">
                    Exportar PDF
                </button>

                <a id="btnExportCsv"
                   class="btn btn-outline-secondary btn-sm"
                   href="{{ route('fornecedores.top.export.csv') }}">
                    Exportar CSV
                </a>
            </div>
        </div>

        {{-- FILTRO DE PERÍODO --}}
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

            <button type="submit" class="btn btn-primary" style="width:auto; max-width:150px; white-space:nowrap;">
                Aplicar Filtro
            </button>
        </form>

        {{-- CONTEÚDO --}}
        @php
            $semQtd = !isset($top5FornecedoresQtd) || $top5FornecedoresQtd->isEmpty();
            $semEuros = !isset($top5FornecedoresEuros) || $top5FornecedoresEuros->isEmpty();
        @endphp

        @if($semQtd || $semEuros)
            <div class="text-center py-5">
                <h4 class="fw-semibold mt-4 mb-2">Sem dados em Fornecedores</h4>
                <div class="text-muted">
                    Não existem dados disponíveis neste filtro.<br>
                    Ajuste o filtro.
                </div>
            </div>
        @else
            <div class="row mt-4">
                <div class="bg-light px-3 py-2 rounded border d-flex align-items-center justify-content-between">
                    <div>
                        <i class="far fa-calendar-alt me-2"></i>
                        {{ $periodoTexto ?? 'Todos os dados disponíveis' }}
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" id="btnQtd" class="btn btn-outline-primary">Qtd</button>
                        <button type="button" id="btnEuros" class="btn btn-outline-primary">€</button>
                    </div>
                </div>

                <div class="col-12">
                    <div id="topFornecedoresChart" style="height: 350px;"></div>
                </div>
            </div>

            <div class="row d-flex align-items-stretch mt-4">
                <div style="overflow-x:auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fornecedor</th>
                                <th>NIF</th>
                                <th class="text-center">Nº Compras</th>
                                <th class="text-end">Total (€)</th>
                            </tr>
                        </thead>
                        <tbody id="topFornecedoresTableBody"></tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

@if(!$semQtd && !$semEuros)
<script>
    window.topFornecedoresData = {
        qtd: @json($top5FornecedoresQtd),
        euros: @json($top5FornecedoresEuros),
        nomesQtd: @json($top5FornecedoresQtd->pluck('fornecedor')->all()),
        nomesEuros: @json($top5FornecedoresEuros->pluck('fornecedor')->all()),
        valoresQtd: @json($top5FornecedoresQtd->pluck('num_compras')->all()),
        valoresEuros: @json($top5FornecedoresEuros->pluck('total_euros')->all()),
    };

    window.csrfToken = "{{ csrf_token() }}";
</script>
@endif

@endsection

@push('scripts')
    @vite(['resources/js/fornecedores/topFornecedores.js'])
@endpush
