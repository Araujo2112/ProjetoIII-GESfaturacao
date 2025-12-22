<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório - Pagamentos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .muted { color:#666; font-size: 11px; }
        .box { border: 1px solid #ddd; border-radius: 6px; padding: 10px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f3f3f3; text-align: left; }
        .right { text-align:right; }
        .small { font-size: 10px; }
        img { width: 100%; height: auto; }
    </style>
</head>
<body>
    <h1>Relatório - Pagamentos</h1>
    <div class="muted">
        <div><strong>Período:</strong> {{ $periodoTexto ?? '-' }}</div>
        <div><strong>Modo:</strong> {{ ($modo ?? 'evolucao') === 'top' ? 'Top' : 'Evolução' }}</div>
        <div class="small"><strong>Gerado em:</strong> {{ $geradoEm ?? now() }}</div>
    </div>

    <div class="box">
        <strong>Gráfico</strong>
        <div style="margin-top:8px;">
            <img src="{{ $chartImg }}" alt="Gráfico">
        </div>
    </div>

    @if(($modo ?? 'evolucao') === 'top')
        <div class="box">
            <strong>Distribuição por método de pagamento</strong>
            <table>
                <thead>
                    <tr>
                        <th>Método</th>
                        <th class="right">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($contagemMetodosPagamento ?? []) as $metodo => $qtd)
                        <tr>
                            <td>{{ $metodo }}</td>
                            <td class="right">{{ $qtd }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="box">
            <strong>Evolução (pagamentos por dia)</strong>
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th class="right">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(($contagemPagamentosPorDia ?? []) as $dia => $qtd)
                        <tr>
                            <td>{{ $dia }}</td>
                            <td class="right">{{ $qtd }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="box">
        <strong>Listagem de pagamentos</strong>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Número Recibo</th>
                    <th>Tipo de Pagamento</th>
                    <th class="right">Preço c/IVA</th>
                    <th class="right">Preço s/IVA</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($pagamentos ?? []) as $p)
                    <tr>
                        <td>{{ $p['data'] ?? '' }}</td>
                        <td>{{ $p['numero_recibo'] ?? '' }}</td>
                        <td>{{ $p['metodo_pagamento'] ?? '' }}</td>
                        <td class="right">€ {{ number_format((float)($p['preco_com_iva'] ?? 0), 2, ',', '.') }}</td>
                        <td class="right">€ {{ number_format((float)($p['preco_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
