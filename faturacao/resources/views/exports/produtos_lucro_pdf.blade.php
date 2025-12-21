<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Top 5 Artigos - Maior Lucro</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { font-size: 10px; color: #555; margin-bottom: 14px; }
        .chart { margin: 10px 0 18px 0; text-align: center; }
        .chart img { width: 100%; max-width: 720px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f3f3f3; text-align: left; }
        td.num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Top 5 Artigos — Maior Lucro</h1>
    <div class="meta">
        Gerado em: {{ $geradoEm->format('d/m/Y H:i') }}
    </div>

    <div class="chart">
        <img src="{{ $chartImg }}" alt="Gráfico de lucro">
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th class="num">Preço s/IVA (€)</th>
                <th class="num">Custo (€)</th>
                <th class="num">Lucro (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $p)
                <tr>
                    <td>{{ $p['cod'] }}</td>
                    <td>{{ $p['nome'] }}</td>
                    <td>{{ $p['categoria'] }}</td>
                    <td class="num">{{ number_format((float)$p['preco_s_iva'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)$p['custo'], 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)$p['lucro'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
