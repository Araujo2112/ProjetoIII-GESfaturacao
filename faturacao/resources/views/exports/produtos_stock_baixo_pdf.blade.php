<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Top 5 Artigos - Abaixo do Limite de Stock</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { font-size: 11px; color: #444; margin-bottom: 12px; }
        img { max-width: 100%; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; text-align: left; }
        td.num, th.num { text-align: right; }
    </style>
</head>
<body>
    <h1>Top 5 Artigos - Abaixo do Limite de Stock</h1>
    <div class="meta">
        <div><strong>Gerado em:</strong> {{ $geradoEm ?? now() }}</div>
    </div>

    <div style="margin: 10px 0 14px 0;">
        <img src="{{ $chartImg }}" alt="Gráfico Stock Baixo">
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Cód.</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th class="num">Stock Atual</th>
                <th class="num">Stock Mínimo</th>
                <th class="num">Falta Repor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produtos as $i => $p)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p['cod'] ?? '' }}</td>
                    <td>{{ $p['nome'] ?? '' }}</td>
                    <td>{{ $p['categoria'] ?? 'Sem Categoria' }}</td>
                    <td class="num">{{ number_format((float)($p['stock_atual'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['stock_minimo'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['falta_repor'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
