@extends('layout')

@section('title', 'Top 5 Fornecedores — € Compras')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Top 5 Fornecedores — € Compras</h1>

            @php
                $fornecedoresEuros = collect($top5FornecedoresEuros ?? []);
                $hasDataEuros = $fornecedoresEuros->count() > 0;
            @endphp

            <div class="container py-4">
                <div class="row d-flex align-items-stretch g-4">
                    {{-- Coluna da tabela --}}
                    <div class="col-lg-7" style="overflow-x:auto;">
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
                            <tbody>
                                @forelse($fornecedoresEuros as $i => $c)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $c['fornecedor'] }}</td>
                                        <td>{{ $c['nif'] }}</td>
                                        <td class="text-center">{{ number_format($c['num_compras'], 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($c['total_euros'], 2, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Não existem dados disponíveis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Coluna do gráfico --}}
                    <div class="col-lg-5 d-flex align-items-center">
                        <div id="chart_fornecedores_euros" style="width: 100%; height: 350px;">
                            @unless($hasDataEuros)
                                <p class="text-center text-muted mt-5">Sem dados para gerar gráfico.</p>
                            @endunless
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    google.charts.load('current', {packages: ['corechart']});
    google.charts.setOnLoadCallback(drawChartFornecedoresEuros);

    function drawChartFornecedoresEuros() {
        var hasData = {{ $hasDataEuros ? 'true' : 'false' }};

        if (!hasData) {
            return;
        }

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Fornecedor');
        data.addColumn('number', 'Total €');

        @foreach ($fornecedoresEuros as $c)
            data.addRow([
                '{{ addslashes($c['fornecedor']) }}',
                {{ (float) $c['total_euros'] }}
            ]);
        @endforeach

        var options = {
            title: 'Top 5 Fornecedores por Valor de Compras (€)',
            legend: { position: 'none' },
            hAxis: {
                minValue: 0
            },
            chartArea: { width: '70%', height: '70%' }
        };

        var chart = new google.visualization.BarChart(
            document.getElementById('chart_fornecedores_euros')
        );
        chart.draw(data, options);
    }
</script>
@endpush
