@extends('layout')

@section('title', 'Top 5 Clientes — € Vendas')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Top 5 Clientes — € Vendas</h1>

            <div class="container py-4">
                <div class="row d-flex align-items-stretch">
                    <div style="overflow-x:auto;">

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
                                @php $rank = 1; @endphp
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
                </div>

                <div class="row mt-5">
                    <div class="col-12">
                        <div id="topClientesChart" style="height: 350px;"></div>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
@endsection

@push('scripts')
<script>
    var options = {
        chart: {
            type: 'bar',
            height: 350,
            toolbar: {
                show: false
            }
        },
        series: [{
            name: 'Total Vendas (€)',
            data: @json($clientesTotais)
        }],
        xaxis: {
            categories: @json($clientesNomes),
            labels: {
                rotate: -45,
                style: {
                    fontSize: '13px'
                }
            }
        },
        yaxis: {
            labels: {
                formatter: function(value) {
                    return '€ ' + value.toFixed(2);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(value) {
                    return '€ ' + value.toFixed(2);
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            }
        },
        colors: ['#2980FF'],
        dataLabels: {
            enabled: true,
            formatter: function(value) {
                return '€ ' + value.toFixed(2);
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#topClientesChart"), options);
    chart.render();
</script>
@endpush

