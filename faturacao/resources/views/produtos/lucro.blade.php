@extends('layout')

@section('title', 'Top 5 Artigos - Maior Lucro')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:500px;">
            <h1 class="text-dark text-center mb-4">Top 5 Artigos - Maior Lucro</h1>

            @if(empty($produtos))
                <p class="text-center mt-4">Nenhum artigo encontrado.</p>
            @else
                <div class="row mb-4">
                    <div class="col-12">
                        <div id="lucroProdutosChart" style="height: 350px;"></div>
                    </div>
                </div>

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
                                        <th class="text-end">Preço s/IVA (€)</th>
                                        <th class="text-end">Custo (€)</th>
                                        <th class="text-end">Lucro % (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produtos as $index => $produto)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $produto['cod'] }}</td>
                                            <td>{{ $produto['nome'] }}</td>
                                            <td>{{ $produto['categoria'] }}</td>
                                            <td class="text-end">{{ number_format($produto['preco_s_iva'], 2) }}</td>
                                            <td class="text-end">{{ number_format($produto['custo'], 2) }}</td>
                                            <td class="text-end">{{ number_format($produto['lucro'], 2) }}</td>
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

    @if(!empty($graficoDados))
        <script>
            window.lucroProdutosData = @json($graficoDados);
        </script>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/produtos/lucroProdutos.js'])
@endpush
