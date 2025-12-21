<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
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
        td.center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    <div class="meta">
        Período: {{ $periodoTexto }}<br>
        Gerado em: {{ $geradoEm->format('d/m/Y H:i') }}
    </div>

    @if(!empty($chartImg))
        <div class="chart">
            <img src="{{ $chartImg }}" alt="Gráfico">
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Cliente</th>
                <th>NIF</th>
                <th class="center">Nº Vendas</th>
                <th class="num">Total (€)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $c)
                <tr>
                    <td>{{ $c['id'] ?? '' }}</td>
                    <td>{{ $c['cliente'] ?? '' }}</td>
                    <td>{{ $c['nif'] ?? '' }}</td>
                    <td class="center">{{ $c['num_vendas'] ?? 0 }}</td>
                    <td class="num">{{ number_format((float)($c['total_euros'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
