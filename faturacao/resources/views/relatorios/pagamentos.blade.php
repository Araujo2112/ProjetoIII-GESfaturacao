@extends('layout')

@section('title', 'Relatório - Pagamentos')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Relatório - Pagamentos</h1>

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
                <div id="camposPersonalizado" class="d-flex align-items-center" style="gap:0.5rem; {{ request('periodo') != 'personalizado' ? 'display:none;' : '' }}">
                    <input type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="form-control" style="width:auto;">
                    <span class="mx-1">a</span>
                    <input type="date" name="data_fim" value="{{ request('data_fim') }}" class="form-control" style="width:auto;">
                </div>
                <button type="submit" class="btn btn-primary" style="width:auto; max-width:150px; white-space:nowrap;">Aplicar Filtro</button>
            </form>


            {{-- Gráfico/Tabela --}}
            @if($pagamentos->isEmpty())
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Sem dados de vendas para o período selecionado</h4>
                    <div class="text-muted">
                        Tente ajustar o filtro para encontrar dados.
                    </div>
                </div>
            @else

                <div class="row mt-5">
                    <div class="bg-light px-3 py-2 rounded border d-flex align-items-center justify-content-between">
                        <div>
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $periodoTexto ?? 'Mês atual' }}
                        </div>
                        <div class="btn-group" role="group" aria-label="Botões gráfico">
                            <button id="btnEvolucao" class="btn btn-outline-primary active">Evolução</button>
                            <button id="btnTop" class="btn btn-outline-primary">Top</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="evolucaoChart"></div>
                        <div id="topChart"></div>
                    </div>
                </div>

                <div class="row d-flex align-items-stretch mt-4">
                    <div style="overflow-x:auto;">

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Número Recibo</th>
                                    <th>Tipo de Pagamento</th>
                                    <th>Preço c/IVA</th>
                                    <th>Preço s/IVA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagamentos as $dados)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($dados['data'])->format('d/m/Y') }}</td>
                                        <td>{{ $dados['numero_recibo'] }}</td>
                                        <td>{{ $dados['metodo_pagamento'] }}</td>
                                        <td>€ {{ number_format($dados['preco_com_iva'], 2, ',', '.') }}</td>
                                        <td>€ {{ number_format($dados['preco_sem_iva'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Sem dados para o período selecionado</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        window.pagamentosDatas = @json($datasFormatadas);
        window.pagamentosQuantidadePorDia = @json($contagemPagamentosPorDia);
        window.contagemMetodosPagamento = @json($contagemMetodosPagamento);
    </script>

@endsection

@push('scripts')
    @vite(['resources/js/relatorios/pagamentos.js'])
@endpush