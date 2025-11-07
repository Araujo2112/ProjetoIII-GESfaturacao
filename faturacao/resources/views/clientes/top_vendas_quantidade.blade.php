@extends('layout')

@section('title', 'Top 5 Clientes — Nº de Vendas')

@section('content')
    <div style="margin-left:200px; margin-top:60px;">
        <div class="bg-white rounded shadow p-4">
            <h1 class="text-dark text-center">Top 5 Clientes — Nº de Vendas</h1>

            <div class="container py-4">
                <div class="row d-flex align-items-stretch">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:80px">#</th>
                                    <th>Cliente</th>
                                    <th style="width:160px" class="text-center">Nº Vendas</th>
                                    <th style="width:180px" class="text-end">Total (€)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(collect($top5ClientesQtd ?? []) as $i => $c)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ $c->cliente }}</td>
                                        <td class="text-center">{{ number_format($c->num_vendas, 0, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format($c->total_euros, 2, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Não existem dados disponíveis.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- sem paginação (é top-5) --}}
            </div>
        </div>
    </div>
@endsection
