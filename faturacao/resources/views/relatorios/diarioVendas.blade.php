@extends('layout')

@section('title', 'Relatório - Diário')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Relatório - Diário</h1>

            {{-- Filtro --}}
            <form method="GET" class="d-flex align-items-center mb-4" style="gap:1rem;" id="filtroForm">
                <label class="mb-0 fw-semibold">Período:</label>
                <select name="periodo" id="periodoSelect" class="form-select" style="width:auto;">
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
            @if($vendasPorDia->isEmpty())
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
                    </div>
                    <div class="col-12">
                        <div id="vendasChart" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="row d-flex align-items-stretch mt-4">
                    <div style="overflow-x:auto;">

                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Dia</th>
                                    <th>Vendas c/IVA</th>
                                    <th>Vendas</th>
                                    <th>Custos</th>
                                    <th>Lucro</th>
                                    <th>Quantidade</th>
                                    <th>Nº Vendas</th>
                                </tr>
                            </thead>
                                <tbody>
                                    @forelse($vendasPorDia as $dados)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($dados['dia'])->format('d/m/Y') }}</td>
                                            <td>€ {{ number_format($dados['vendas_com_iva'], 2, ',', '.') }}</td>
                                            <td>€ {{ number_format($dados['vendas_sem_iva'], 2, ',', '.') }}</td>
                                            <td>€ {{ number_format($dados['custos'], 2, ',', '.') }}</td>
                                            <td>€ {{ number_format($dados['lucro'], 2, ',', '.') }}</td>
                                            <td>{{ $dados['quantidade'] }}</td>
                                            <td>{{ $dados['num_vendas'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center">Sem dados para o período selecionado</td></tr>
                                    @endforelse

                                    <tr>
                                        <th>TOTAL</th>
                                        <th>€ {{ number_format($total_vendas_iva, 2, ',', '.') }}</th>
                                        <th>€ {{ number_format($total_vendas, 2, ',', '.') }}</th>
                                        <th>€ {{ number_format($total_custos, 2, ',', '.') }}</th>
                                        <th>€ {{ number_format($total_lucro, 2, ',', '.') }}</th>
                                        <th>{{ $total_quantidade }}</th>
                                        <th>{{ $total_num_vendas }}</th>
                                    </tr>
                                </tbody>

                        </table>

                    </div>
                </div>
            @endif

        </div>
    </div>

    <script>
        window.vendasDatas = @json($datasFormatadas);
        window.vendasValores = @json($valoresPorDia);
    </script>

@endsection

@push('scripts')
    @vite(['resources/js/relatorios/diarioVendas.js'])
@endpush