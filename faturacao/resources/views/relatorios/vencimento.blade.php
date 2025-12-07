@extends('layout')

@section('title', 'Faturas a Vencer - 30 Dias')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Faturas a Vencer - Próximos 30 Dias</h1>

            {{-- Gráfico --}}
            @if($contagemNormalizada == [] || array_sum($contagemNormalizada) == 0)
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Sem faturas a vencer nos próximos 30 dias</h4>
                    <div class="text-muted">
                        Todas as faturas estão em dia ou já vencidas.
                    </div>
                </div>
            @else
                <div class="row mt-5">
                    <div class="col-12">
                        <div id="faturasChart" style="height: 350px;"></div>
                    </div>
                </div>

                {{-- Tabela de Faturas --}}
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
                                @forelse($faturasVencer as $fatura)
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
        </div>
    </div>

    <script>
        window.faturasDatas = @json($datasFormatadas);
        window.faturasQuantidades = @json(array_values($contagemNormalizada));
    </script>

@endsection
