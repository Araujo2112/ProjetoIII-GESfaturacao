@extends('layout')

@section('title', 'Top 5 Artigos - Abaixo do Limite de Stock')

@section('content')
@php
    // ✅ garante que existem sempre (evita erros de undefined e de parse)
    $listaProdutos = (!empty($produtos) && is_array($produtos)) ? $produtos : [];

    $nomes = !empty($listaProdutos) ? array_values(array_column($listaProdutos, 'nome')) : [];
    $diferencas = !empty($listaProdutos) ? array_values(array_column($listaProdutos, 'falta_repor')) : [];
    $codigos = !empty($listaProdutos) ? array_values(array_column($listaProdutos, 'cod')) : [];
@endphp

<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:500px;">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="text-dark text-center m-0 flex-grow-1">Top 5 Artigos - Abaixo do Limite de Stock</h1>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportStockBaixoPdf()">
                    Exportar PDF
                </button>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('produtos.stock.export.csv') }}">
                    Exportar CSV
                </a>
            </div>
        </div>

        @if(empty($listaProdutos))
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
                                    <th class="text-end">Falta Repor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listaProdutos as $index => $produto)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $produto['cod'] ?? '' }}</td>
                                        <td>{{ $produto['nome'] ?? '' }}</td>
                                        <td>{{ $produto['categoria'] ?? 'Sem Categoria' }}</td>
                                        <td class="text-end">{{ number_format((float)($produto['stock_atual'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float)($produto['stock_minimo'] ?? 0), 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float)($produto['falta_repor'] ?? 0), 2) }}</td>
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

{{-- DADOS + EXPORT (sempre definidos) --}}
<script>
    window.csrfToken = "{{ csrf_token() }}";

    // ✅ o JS espera isto: window.stockBaixoData.nomes / diferencas / codigos
    window.stockBaixoData = {
        nomes: @json($nomes),
        diferencas: @json($diferencas),
        codigos: @json($codigos)
    };

    async function exportStockBaixoPdf() {
        try {
            if (!window.stockBaixoData || !window.stockBaixoData.diferencas || window.stockBaixoData.diferencas.length === 0) {
                alert('Sem dados para exportar.');
                return;
            }

            if (!window.stockBaixoChart) {
                alert('O gráfico ainda não está pronto. Tenta novamente em 1-2 segundos.');
                return;
            }

            const { imgURI } = await window.stockBaixoChart.dataURI();

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('produtos.stock.export.pdf') }}";

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
@endsection

@push('scripts')
    @vite(['resources/js/produtos/stockProdutos.js'])
@endpush
