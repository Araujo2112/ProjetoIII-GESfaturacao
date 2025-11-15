@extends('layout')

@section('title', 'Top 5 Clientes — € Vendas')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Top 5 Clientes — € Vendas</h1>

            @php
                $rank = 1;
                $hasDataEuros = !empty($top5ClientesEuros) && count($top5ClientesEuros) > 0;
            @endphp

            <div class="container py-4">
                <div class="row d-flex align-items-stretch g-4">
                    {{-- Coluna da tabela --}}
                    <div class="col-lg-7" style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cód.</th>
                                    <th>Cliente</th>
                                    <th>NIF</th>
                                    <th class="text-center">Nº Vendas</th>
                                    <th class="text-end">Total (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top5ClientesEuros ?? [] as $c)
                                    <tr>
                                        <td>{{ $rank++ }}</td>
                                        <td>{{ $c['id'] }}</td>
                                        <td>{{ $c['cliente'] }}</td>
                                        <td>{{ $c['nif'] }}</td>
                                        <td class="text-center">{{ number_format($c['num_vendas'], 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($c['total_euros'], 2, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Não existem dados disponíveis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Coluna do gráfico --}}
                    <div class="col-lg-5 d-flex align-items-center">
                        <div id="chart_clientes_euros" style="width: 100%; height: 350px;">
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
    google.charts.setOnLoadCallback(drawChartClientesEuros);

    function drawChartClientesEuros() {
        var hasData = {{ $hasDataEuros ? 'true' : 'false' }};

        if (!hasData) {
            // já mostramos a mensagem "Sem dados..." no HTML
            return;
        }

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Cliente');
        data.addColumn('number', 'Total €');

        @foreach ($top5ClientesEuros ?? [] as $c)
            data.addRow([
                '{{ addslashes($c['cliente']) }}',
                {{ (float) $c['total_euros'] }}
            ]);
        @endforeach

        var options = {
            title: 'Top 5 Clientes por Valor de Vendas (€)',
            legend: { position: 'none' },
            hAxis: {
                minValue: 0
            },
            chartArea: { width: '70%', height: '70%' }
        };

        var chart = new google.visualization.BarChart(
            document.getElementById('chart_clientes_euros')
        );
        chart.draw(data, options);
    }
</script>
@endpush
