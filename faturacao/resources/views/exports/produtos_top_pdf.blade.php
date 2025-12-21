<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Top 5 Artigos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { font-size: 11px; color: #444; margin-bottom: 12px; }
        .chart { margin: 10px 0 14px 0; }
        img { max-width: 100%; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; text-align: left; }
        td.num, th.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Top 5 Artigos ({{ $modoTexto ?? '+ Vendidos' }})</h1>
    <div class="meta">
        <div><strong>Período:</strong> {{ $periodoTexto ?? '-' }}</div>
        <div><strong>Gerado em:</strong> {{ $geradoEm ?? now() }}</div>
    </div>

    <div class="chart">
        <img src="{{ $chartImg }}" alt="Gráfico Top 5 Artigos">
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cód.</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th class="num">Qtd Vendida</th>
                <th class="num">Preço c/IVA (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p['cod'] ?? '' }}</td>
                    <td>{{ $p['nome'] ?? '' }}</td>
                    <td>{{ $p['categoria'] ?? 'Sem Categoria' }}</td>
                    <td class="num">{{ number_format((float)($p['qtd'] ?? 0), 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['preco_c_iva'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
