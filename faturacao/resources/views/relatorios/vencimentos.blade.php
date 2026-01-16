@extends('layout')

@section('title', 'Faturas a Vencer - 30 Dias')

@section('content')
<div class="bg-dark-subtle min-vh-100 pt-5">

    {{-- CASHFLOW --}}
    <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1 class="text-dark text-center m-0">Cashflow (Faturas vs Compras) - Próximos 30 Dias</h1>

            <div class="d-flex gap-2">
                <button id="btnExportCashflowPdf" class="btn btn-outline-danger btn-sm">Exportar PDF</button>
                <a href="{{ route('relatorios.vencimento.export.csv') }}" class="btn btn-outline-success btn-sm">Exportar CSV</a>
            </div>
        </div>

        @php
            $totalVendas = array_sum($dados['vendas']['monetario']);
            $totalCompras = array_sum($dados['compras']['monetario']);
        @endphp

        @if($totalVendas == 0 && $totalCompras == 0)
            <div class="text-center py-5">
                <h4 class="fw-semibold mt-4 mb-2">Sem faturas a vencer nos próximos 30 dias</h4>
                <div class="text-muted">Todas as faturas estão em dia ou já vencidas.</div>
            </div>
        @else
            <div class="row mt-4">
                <div class="col-12">
                    <div id="cashflowChart" style="height: 350px;"></div>
                </div>
            </div>
        @endif
    </section>

    {{-- VENDAS --}}
    <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
        <h1 class="text-dark text-center">Faturas a Vencer - Próximos 30 Dias</h1>

        @if(array_sum($dados['vendas']['monetario']) == 0)
            <div class="text-center py-5">
                <h4 class="fw-semibold mt-4 mb-2">Sem faturas de vendas a vencer</h4>
            </div>
        @else
            <div class="row mt-5">
                <div class="col-12">
                    <div id="vendasChart" style="height: 350px;"></div>
                </div>
            </div>

            <div class="row d-flex align-items-stretch mt-4">
                <div style="overflow-x:auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Nome do Cliente</th>
                            <th>NIF</th>
                            <th>Data</th>
                            <th>Data de Vencimento</th>
                            <th>Dívida</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($dados['vendas']['faturas'] as $fatura)
                            <tr>
                                <td>{{ $fatura['number'] ?? 'N/D' }}</td>
                                <td>{{ $fatura['client']['name'] ?? 'N/D' }}</td>
                                <td>{{ $fatura['client']['vatNumber'] ?? 'N/D' }}</td>
                                <td>{{ \Carbon\Carbon::parse($fatura['date'])->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($fatura['expiration'])->format('d/m/Y') }}</td>
                                <td>€ {{ number_format(abs($fatura['balance']), 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Sem faturas para mostrar</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    {{-- COMPRAS --}}
    <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
        <h1 class="text-dark text-center">Compras a Vencer - Próximos 30 Dias</h1>

        @if(array_sum($dados['compras']['monetario']) == 0)
            <div class="text-center py-5">
                <h4 class="fw-semibold mt-4 mb-2">Sem compras a vencer</h4>
            </div>
        @else
            <div class="row mt-5">
                <div class="col-12">
                    <div id="comprasChart" style="height: 350px;"></div>
                </div>
            </div>

            <div class="row d-flex align-items-stretch mt-4">
                <div style="overflow-x:auto;">
                    <table class="table table-sm table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Fornecedor</th>
                            <th>NIF</th>
                            <th>Data</th>
                            <th>Data de Vencimento</th>
                            <th>Dívida</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($dados['compras']['faturas'] as $fatura)
                            <tr>
                                <td>{{ $fatura['number'] ?? 'N/D' }}</td>
                                <td>{{ $fatura['supplier']['name'] ?? 'N/D' }}</td>
                                <td>{{ $fatura['supplier']['vatNumber'] ?? 'N/D' }}</td>
                                <td>{{ \Carbon\Carbon::parse($fatura['date'])->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($fatura['expiration'])->format('d/m/Y') }}</td>
                                <td>€ {{ number_format(abs($fatura['balance']), 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Sem compras para mostrar</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

</div>

<script>
    window.faturasDatas = @json($dados['datas']);
    window.vendasTotais = @json(array_values($dados['vendas']['monetario']));
    window.comprasTotais = @json(array_values($dados['compras']['monetario']));
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btnExportCashflowPdf');
    if (!btn) return;

    btn.addEventListener('click', async function() {
        if (!window.cashflowChartInstance || typeof window.cashflowChartInstance.dataURI !== 'function') {
            alert('Gráfico ainda não está pronto para exportar.');
            return;
        }

        const uri = await window.cashflowChartInstance.dataURI();
        const img = uri && uri.imgURI ? uri.imgURI : null;

        if (!img || !img.startsWith('data:image')) {
            alert('Não foi possível obter a imagem do gráfico.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('relatorios.vencimento.export.pdf') }}";

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        const chart = document.createElement('input');
        chart.type = 'hidden';
        chart.name = 'chart_img';
        chart.value = img;
        form.appendChild(chart);

        document.body.appendChild(form);
        form.submit();
        form.remove();
    });
});
</script>

@endsection

@push('scripts')
    @vite(['resources/js/relatorios/vencimentos.js'])
@endpush
