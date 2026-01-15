<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório - Pagamentos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; margin: 0; }
        .page { width: 100%; padding: 18px 22px; }

        h1 { font-size: 18px; margin: 0; text-align: center; }
        .meta { margin-top: 6px; text-align: center; font-size: 11px; color:#333; line-height: 1.4; }
        .meta strong { font-weight: 700; color:#111; }

        .box { border: 1px solid #ddd; border-radius: 8px; padding: 10px 12px; margin: 12px auto; }
        .box-title { font-weight: 700; margin-bottom: 8px; }

        img { width: 100%; height: auto; display:block; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f3f3f3; text-align: left; }
        .right { text-align:right; }

        .footer { margin-top: 6px; font-size: 10px; color:#666; text-align: right; }
    </style>
</head>
<body>
<div class="page">
    <h1>Relatório - Pagamentos</h1>

    <div class="meta">
        <div><strong>Período:</strong> {{ $periodoTexto ?? '-' }}</div>
        <div><strong>Modo:</strong> {{ ($modo ?? 'evolucao') === 'top' ? 'Top' : 'Evolução' }}</div>
        <div><strong>Gerado em:</strong> {{ $geradoEm ?? now() }}</div>
    </div>

    <div class="box">
        <div class="box-title">Gráfico</div>
        <img src="{{ $chartImg }}" alt="Gráfico">
    </div>

    @if(($modo ?? 'evolucao') === 'top')
        <div class="box">
            <div class="box-title">Distribuição por método de pagamento</div>

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

            <div class="footer">Exportação PDF — GESFaturação</div>
        </div>
    @else
        <div class="box">
            <div class="box-title">Evolução (pagamentos por dia)</div>

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

            <div class="footer">Exportação PDF — GESFaturação</div>
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

        <div class="footer">Exportação PDF — GESFaturação</div>
    </div>
</div>
</body>
</html>
