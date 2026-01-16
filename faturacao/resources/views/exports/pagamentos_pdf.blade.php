@extends('exports._pdf_base')

@section('conteudo')

    @if(!empty($chartImg))
        <div class="box">
            <div class="box-title">Gráfico</div>
            <div class="chart">
                <img src="{{ $chartImg }}" alt="Gráfico">
            </div>
        </div>
    @endif

    @if(($modo ?? 'evolucao') === 'top')
        <div class="box">
            <div class="box-title">Distribuição por método de pagamento</div>
            <table>
                <thead>
                <tr>
                    <th>Método</th>
                    <th class="num">Quantidade</th>
                </tr>
                </thead>
                <tbody>
                @foreach(($contagemMetodosPagamento ?? []) as $metodo => $qtd)
                    <tr>
                        <td>{{ $metodo }}</td>
                        <td class="num">{{ $qtd }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="box">
            <div class="box-title">Evolução (pagamentos por dia)</div>
            <table>
                <thead>
                <tr>
                    <th>Data</th>
                    <th class="num">Quantidade</th>
                </tr>
                </thead>
                <tbody>
                @foreach(($contagemPagamentosPorDia ?? []) as $dia => $qtd)
                    <tr>
                        <td>{{ $dia }}</td>
                        <td class="num">{{ $qtd }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="box">
        <div class="box-title">Listagem de pagamentos</div>
        <table>
            <thead>
            <tr>
                <th>Data</th>
                <th>Número Recibo</th>
                <th>Tipo de Pagamento</th>
                <th class="num">Preço c/IVA</th>
                <th class="num">Preço s/IVA</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($pagamentos ?? []) as $p)
                <tr>
                    <td>{{ $p['data'] ?? '' }}</td>
                    <td>{{ $p['numero_recibo'] ?? '' }}</td>
                    <td>{{ $p['metodo_pagamento'] ?? '' }}</td>
                    <td class="num">€ {{ number_format((float)($p['preco_com_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($p['preco_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
