@extends('layout')

@section('title', 'Top 5 Artigos - Abaixo do Limite de Stock')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:500px;">
            <h1 class="text-dark text-center mb-4">Top 5 Artigos - Abaixo do Limite de Stock</h1>

            @if(empty($produtos))
                <div class="text-center py-5">
                    <h4 class="fw-semibold mt-4 mb-2">Nenhum produto abaixo do stock mínimo</h4>
                    <div class="text-muted">Todos os artigos têm stock suficiente.</div>
                </div>
            @else
                {{-- GRÁFICO --}}
                <div class="row mb-4">
                    <div class="col-12">
                        <div id="stockBaixoChart" style="height: 350px;"></div>
                    </div>
                </div>

                {{-- TABELA --}}
                <div class="row">
                    <div class="col-12">
                        <div style="overflow-x:auto;">
                            <table class="table table-sm table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Cód.</th>
                                        <th>Nome</th>
                                        <th>Categoria</th>
                                        <th class="text-end">Stock Atual</th>
                                        <th class="text-end">Stock Mínimo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produtos as $index => $produto)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $produto['cod'] }}</td>
                                            <td>{{ $produto['nome'] }}</td>
                                            <td>{{ $produto['categoria'] }}</td>
                                            <td class="text-end">{{ number_format($produto['stock_atual'], 2) }}</td>
                                            <td class="text-end">{{ number_format($produto['stock_minimo'], 2) }}</td>
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

    {{-- DADOS PARA GRÁFICO --}}
    @if(!empty($produtos))
        <script>
            window.stockBaixoData = {
                nomes: @json(array_column($produtos, 'nome')),
                diferencas: @json(array_column($produtos, 'falta_repor')),
                codigos: @json(array_column($produtos, 'cod'))
            };
        </script>
    @endif

@endsection

@push('scripts')
    @vite(['resources/js/produtos/stockProdutos.js'])
@endpush
