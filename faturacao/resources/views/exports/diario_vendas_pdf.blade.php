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

    <div class="box">
        <div class="box-title">Vendas por dia</div>

        <table>
            <thead>
            <tr>
                <th>Dia</th>
                <th class="num">Vendas c/IVA</th>
                <th class="num">Vendas s/IVA</th>
                <th class="num">Custos</th>
                <th class="num">Lucro</th>
                <th class="num">Quantidade</th>
                <th class="num">Nº Vendas</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($vendasPorDia ?? []) as $dados)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($dados['dia'])->format('d/m/Y') }}</td>
                    <td class="num">€ {{ number_format((float)($dados['vendas_com_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($dados['vendas_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($dados['custos'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($dados['lucro'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($dados['quantidade'] ?? 0), 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($dados['num_vendas'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>

            <tfoot>
            <tr>
                <th>TOTAL</th>
                <th class="num">€ {{ number_format((float)($totais['total_vendas_iva'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_vendas'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_custos'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_lucro'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_quantidade'] ?? 0), 0, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_num_vendas'] ?? 0), 0, ',', '.') }}</th>
            </tr>
            </tfoot>
        </table>
    </div>

@endsection
