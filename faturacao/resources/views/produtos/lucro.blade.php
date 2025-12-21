@extends('layout')

@section('title', 'Top 5 Artigos - Maior Lucro')

@section('content')
    <div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
        <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:500px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="text-dark text-center m-0 flex-grow-1">Top 5 Artigos - Maior Lucro</h1>

                {{-- Botões Export (direita) --}}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportLucroPdf()">
                        Exportar PDF
                    </button>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('artigos.lucro.export.csv') }}">
                        Exportar CSV
                    </a>
                </div>
            </div>

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
            window.csrfToken = "{{ csrf_token() }}";

            async function exportLucroPdf() {
                try {
                    if (!window.lucroProdutosChart) {
                        alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                        return;
                    }

                    const { imgURI } = await window.lucroProdutosChart.dataURI();

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('artigos.lucro.export.pdf') }}";

                    form.innerHTML = `
                        <input type="hidden" name="_token" value="${window.csrfToken}">
                        <input type="hidden" name="chart_img" value="${imgURI}">
                    `;

                    document.body.appendChild(form);
                    form.submit();
                } catch (e) {
                    console.error(e);
                    alert('Erro ao exportar PDF. Verifica a consola.');
                }
            }
        </script>
    @else
        <script>
            // Se não há dados, mantém o botão PDF a dar feedback simples
            function exportLucroPdf() {
                alert('Sem dados para exportar.');
            }
        </script>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/produtos/lucroProdutos.js'])
@endpush
