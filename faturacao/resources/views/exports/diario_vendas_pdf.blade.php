<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório Diário</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; text-align: center; }
        .meta { margin: 6px 0 14px 0; text-align: center; font-size: 11px; color: #333; }
        .chart { margin: 10px 0 14px 0; text-align: center; }
        .chart img { width: 100%; max-width: 720px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 6px; }
        th { background: #f3f3f3; text-align: left; }
        td.num, th.num { text-align: right; }
        .total th { background: #eaeaea; }
        .small { font-size: 10px; color:#444; }
    </style>
</head>
<body>

    <h1>Relatório - Diário ({{ $modo === 'vendas' ? 'Vendas' : 'Lucro' }})</h1>

    <div class="meta">
        <div><strong>{{ $periodoTexto ?? '' }}</strong></div>
        <div class="small">Gerado em: {{ isset($geradoEm) ? $geradoEm->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
    </div>

    @if(!empty($chartImg))
        <div class="chart">
            <img src="{{ $chartImg }}" alt="Gráfico">
        </div>
    @endif

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
            @foreach($vendasPorDia as $dados)
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

            <tr class="total">
                <th>TOTAL</th>
                <th class="num">€ {{ number_format((float)($totais['total_vendas_iva'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_vendas'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_custos'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">€ {{ number_format((float)($totais['total_lucro'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_quantidade'] ?? 0), 0, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_num_vendas'] ?? 0), 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
