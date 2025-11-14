@extends('layout')

@section('title', 'Top 5 Fornecedores — Nº de Compras')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
        <h1 class="text-dark text-center">Top 5 Fornecedores — Nº de Compras</h1>

        <div class="container py-4">
            <div class="row d-flex align-items-stretch">
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
                        <tbody>
                            @forelse(collect($top5FornecedoresQtd ?? []) as $i => $c)
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
            </div>
        </div>
    </div>
</div>
@endsection
