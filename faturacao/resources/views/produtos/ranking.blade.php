@extends('layout')

@section('title', 'Top 5 Produtos Mais Vendidos')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
        <h1 class="text-dark text-center">Top 5 Produtos Mais Vendidos</h1>

        @if(isset($erro))
            <div class="alert alert-danger mt-3">
                {{ $erro }}
            </div>
        @endif

        @if(empty($top5))
            <p class="text-center mt-4">Ainda não existem dados suficientes para o ranking.</p>
        @else
            <div class="container py-2">
                <div class="row d-flex align-items-stretch">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover mt-3">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Cód.</th>
                                    <th>Nome</th>
                                    <th>Categoria</th>
                                    <th>Tipo</th>
                                    <th>Preço PVP</th>
                                    <th>Stock Atual</th>
                                    <th>Unidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($top5 as $index => $produto)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $produto['code'] ?? '' }}</td>
                                        <td>{{ $produto['description'] ?? '' }}</td>
                                        <td>{{ $produto['category'] ?? 'Sem categoria' }}</td>
                                        <td>{{ $produto['type'] ?? '' }}</td>
                                        <td>{{ number_format($produto['pricePvp'] ?? 0, 2, ',', '.') }} €</td>
                                        <td>{{ $produto['stock'] ?? 0 }}</td>
                                        <td>{{ $produto['unit'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
