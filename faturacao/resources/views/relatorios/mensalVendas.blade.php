@extends('layout')

@section('title', 'Relatório - Mensal')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="text-dark text-center m-0 flex-grow-1">Relatório - Mensal</h1>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportMensalPdf()">
                        Exportar PDF
                    </button>

                    <a class="btn btn-outline-secondary btn-sm"
                       href="{{ route('relatorios.mensal.export.csv', request()->query()) }}">
                        Exportar CSV
                    </a>
                </div>
            </div>

            <form method="GET" class="d-flex align-items-center mb-4" style="gap:1rem;" id="filtroForm">
                <label class="mb-0 fw-semibold">Período:</label>
                <select name="periodo" id="periodoSelect" class="form-select" style="width:auto;">
                    <option value="ano_atual" {{ request('periodo','ano_atual')=='ano_atual' ? 'selected' : '' }}>Ano Atual</option>
                    <option value="ano_anterior" {{ request('periodo')=='ano_anterior' ? 'selected' : '' }}>Ano Anterior</option>
                </select>
                <button type="submit" class="btn btn-primary" style="width:auto; max-width:150px; white-space:nowrap;">Aplicar Filtro</button>
            </form>

            @if($vendasPorMes->isEmpty())
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
                            {{ $periodoTexto ?? 'Ano atual' }}
                        </div>
                        <div class="btn-group" role="group" aria-label="Botões gráfico">
                            <button type="button" id="btnLucro" class="btn btn-outline-primary">Lucro</button>
                            <button type="button" id="btnVendas" class="btn btn-outline-primary">Vendas</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="vendasChart" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="row d-flex align-items-stretch mt-4">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Vendas c/IVA</th>
                                    <th>Vendas s/IVA</th>
                                    <th>Custos</th>
                                    <th>Lucro</th>
                                    <th>Quantidade</th>
                                    <th>Nº Vendas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendasPorMes as $dados)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $dados['mes'])->format('m/Y') }}</td>
                                        <td>€ {{ number_format((float)($dados['vendas_com_iva'] ?? 0), 2, ',', '.') }}</td>
                                        <td>€ {{ number_format((float)($dados['vendas_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                                        <td>€ {{ number_format((float)($dados['custos'] ?? 0), 2, ',', '.') }}</td>
                                        <td>€ {{ number_format((float)($dados['lucro'] ?? 0), 2, ',', '.') }}</td>
                                        <td>{{ $dados['quantidade'] ?? 0 }}</td>
                                        <td>{{ $dados['num_vendas'] ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">Sem dados para o período selecionado</td></tr>
                                @endforelse

                                <tr>
                                    <th>TOTAL</th>
                                    <th>€ {{ number_format((float)$total_vendas_iva, 2, ',', '.') }}</th>
                                    <th>€ {{ number_format((float)$total_vendas, 2, ',', '.') }}</th>
                                    <th>€ {{ number_format((float)$total_custos, 2, ',', '.') }}</th>
                                    <th>€ {{ number_format((float)$total_lucro, 2, ',', '.') }}</th>
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
        window.vendasMeses   = @json($mesesFormatados);
        window.vendasValores = @json($valoresPorMes);

        window.lucroMeses   = @json($mesesFormatados);
        window.lucroValores = @json($lucroPorMes);

        window.csrfToken = @json(csrf_token());

        async function exportMensalPdf() {
            try {
                if (!window.mensalChart) {
                    alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                    return;
                }

                const { imgURI } = await window.mensalChart.dataURI();
                const modo = window.mensalModo || 'lucro';

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = @json(route('relatorios.mensal.export.pdf'));

                const t = document.createElement('input');
                t.type = 'hidden';
                t.name = '_token';
                t.value = window.csrfToken;
                form.appendChild(t);

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
    @vite(['resources/js/relatorios/mensalVendas.js'])
@endpush
