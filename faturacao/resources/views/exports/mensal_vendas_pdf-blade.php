<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório Mensal</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin: 0 0 6px 0; }
        .muted { color: #666; margin-bottom: 12px; }
        img { width: 100%; max-width: 780px; margin: 10px 0 16px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; text-align: left; }
        td.num { text-align: right; }
    </style>
</head>
<body>

    <h2>Relatório - Mensal ({{ $modo === 'vendas' ? 'Vendas' : 'Lucro' }})</h2>
    <div class="muted">
        {{ $periodoTexto ?? '' }} | Gerado em: {{ $geradoEm }}
    </div>

    <img src="{{ $chartImg }}" alt="Gráfico">

    <table>
        <thead>
            <tr>
                <th>Mês</th>
                <th class="num">Vendas c/IVA</th>
                <th class="num">Vendas s/IVA</th>
                <th class="num">Custos</th>
                <th class="num">Lucro</th>
                <th class="num">Quantidade</th>
                <th class="num">Nº Vendas</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendasPorMes as $d)
                <tr>
                    <td>{{ $d['mes'] ?? '' }}</td>
                    <td class="num">{{ number_format((float)($d['vendas_com_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($d['vendas_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($d['custos'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($d['lucro'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ $d['quantidade'] ?? 0 }}</td>
                    <td class="num">{{ $d['num_vendas'] ?? 0 }}</td>
                </tr>
            @endforeach

            <tr>
                <th>TOTAL</th>
                <th class="num">{{ number_format((float)($totais['total_vendas_iva'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_vendas'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_custos'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ number_format((float)($totais['total_lucro'] ?? 0), 2, ',', '.') }}</th>
                <th class="num">{{ $totais['total_quantidade'] ?? 0 }}</th>
                <th class="num">{{ $totais['total_num_vendas'] ?? 0 }}</th>
            </tr>
        </tbody>
    </table>

</body>
</html>
