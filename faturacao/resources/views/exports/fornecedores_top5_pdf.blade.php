<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Top 5 Fornecedores</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 12px; }
        .title { font-size: 18px; font-weight: 700; margin: 0; }
        .meta { margin-top: 6px; font-size: 11px; color: #444; }
        .box { border: 1px solid #ddd; border-radius: 6px; padding: 10px; margin-top: 10px; }
        .chart { text-align: center; margin-top: 8px; }
        .chart img { max-width: 100%; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; font-weight: 700; }
        td.num, th.num { text-align: right; }
        td.center, th.center { text-align: center; }
        .footer { margin-top: 10px; font-size: 10px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Top 5 Fornecedores</p>
        <div class="meta">
            <div><strong>Período:</strong> {{ $periodoTexto ?? 'Todos os dados disponíveis' }}</div>
            <div><strong>Modo:</strong> {{ $tituloModo ?? $mode }}</div>
            <div><strong>Gerado em:</strong> {{ $geradoEm ?? '' }}</div>
        </div>
    </div>

    <div class="box">
        <div class="chart">
            <img src="{{ $chartImg }}" alt="Gráfico Top 5 Fornecedores">
        </div>

        <table>
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Fornecedor</th>
                    <th>NIF</th>
                    <th class="center">Nº Compras</th>
                    <th class="num">Total (€)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($top5 as $i => $f)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $f['fornecedor'] ?? '' }}</td>
                        <td>{{ $f['nif'] ?? '' }}</td>
                        <td class="center">{{ $f['num_compras'] ?? 0 }}</td>
                        <td class="num">{{ number_format((float)($f['total_euros'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Exportação PDF — GESfaturação
        </div>
    </div>
</body>
</html>
