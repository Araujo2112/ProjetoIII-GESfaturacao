@extends('layout')

@section('title', 'Relatório - Pagamentos')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Relatório - Pagamentos</h1>

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

            @if($pagamentos->isEmpty())
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Sem dados de pagamentos para o período selecionado</h4>
                    <div class="text-muted">Tente ajustar o filtro para encontrar dados.</div>
                </div>
            @else
                <div class="row mt-5">
                    <div class="bg-light px-3 py-2 rounded border d-flex align-items-center justify-content-between">
                        <div>
                            <i class="far fa-calendar-alt me-2"></i>
                            {{ $periodoTexto ?? 'Período' }}
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="btn-group" role="group" aria-label="Botões gráfico">
                                <button type="button" id="btnEvolucao" class="btn btn-outline-primary">Evolução</button>
                                <button type="button" id="btnTop" class="btn btn-outline-primary">Top</button>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportPagamentosPdf()">
                                Exportar PDF
                            </button>

                            <a class="btn btn-outline-secondary btn-sm"
                               href="{{ route('relatorios.pagamentos.export.csv', request()->query()) }}">
                                Exportar CSV
                            </a>
                        </div>
                    </div>

                    <div class="col-12">
                        <div id="evolucaoChart"></div>
                        <div id="topChart"></div>
                    </div>
                </div>

                <div class="row d-flex align-items-stretch mt-4">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover">
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
                                        <td colspan="5" class="text-center">Sem dados para o período selecionado</td>
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
        window.pagamentosDatas = @json($datasFormatadas ?? []);
        window.pagamentosDatasKeys = @json($datasYMD ?? []);
        window.pagamentosQuantidadePorDia = @json($contagemPagamentosPorDia ?? []);
        window.contagemMetodosPagamento = @json($contagemMetodosPagamento ?? []);

        window.csrfToken = @json(csrf_token());
        window.pagamentosModo = 'evolucao';
        window.pagamentosChart = null;

        async function exportPagamentosPdf() {
            try {
                if (!window.pagamentosChart) {
                    alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                    return;
                }

                const { imgURI } = await window.pagamentosChart.dataURI();
                const modo = window.pagamentosModo || 'evolucao';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('relatorios.pagamentos.export.pdf'));

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = window.csrfToken;
                form.appendChild(csrf);

                const ci = document.createElement('input');
                ci.type = 'hidden';
                ci.name = 'chart_img';
                ci.value = imgURI;
                form.appendChild(ci);

                const mo = document.createElement('input');
                mo.type = 'hidden';
                mo.name = 'modo';
                mo.value = modo;
                form.appendChild(mo);

                const params = new URLSearchParams(window.location.search);
                for (const [k, v] of params.entries()) {
                    const i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = k;
                    i.value = v;
                    form.appendChild(i);
                }

                document.body.appendChild(form);
                form.submit();
            } catch (e) {
                console.error(e);
                alert('Erro ao exportar PDF. Verifica a consola.');
            }
        }
    </script>
@endsection

@push('scripts')
    @vite(['resources/js/relatorios/pagamentos.js'])
@endpush
