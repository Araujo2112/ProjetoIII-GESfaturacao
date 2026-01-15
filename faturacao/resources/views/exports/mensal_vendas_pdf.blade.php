<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>Relatório - Mensal</title>
    <style>
        @page { margin: 22px 22px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
        }

        .page {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            margin: 0;
            font-weight: 700;
        }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.35;
            color: #333;
        }

        .meta strong {
            font-weight: 700;
            color: #111;
        }

        .box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            margin: 12px 0;
            page-break-inside: avoid;
        }

        .box-title {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .chart-wrap {
            text-align: center;
        }

        .chart-wrap img {
            width: 100%;
            max-width: 780px;
            height: auto;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead { display: table-header-group; }
        tfoot { display: table-row-group; }

        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
            text-align: left;
            font-weight: 700;
        }

        td.num, th.num {
            text-align: right;
            white-space: nowrap;
        }

        tfoot th {
            background: #f3f3f3;
        }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <h1 class="title">Relatório - Mensal</h1>
        <div class="meta">
            <div><strong>Período:</strong> {{ $periodoTexto ?? '-' }}</div>
            <div><strong>Modo:</strong> {{ ($modo ?? 'lucro') === 'vendas' ? 'Vendas' : 'Lucro' }}</div>
            <div><strong>Gerado em:</strong> {{ $geradoEm ?? now() }}</div>
        </div>
    </div>

    <div class="box">
        <div class="box-title">Gráfico</div>
        <div class="chart-wrap">
            <img src="{{ $chartImg }}" alt="Gráfico">
        </div>
    </div>

    <div class="box">
        <div class="box-title">Resumo mensal</div>

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
            @foreach(($vendasPorMes ?? []) as $d)
                <tr>
                    <td>
                        @php
                            $mesRaw = $d['mes'] ?? '';
                            try {
                                $mesFmt = $mesRaw ? \Carbon\Carbon::createFromFormat('Y-m', $mesRaw)->format('m/Y') : '';
                            } catch (\Exception $e) {
                                $mesFmt = $mesRaw;
                            }
                        @endphp
                        {{ $mesFmt }}
                    </td>
                    <td class="num">€ {{ number_format((float)($d['vendas_com_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($d['vendas_sem_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($d['custos'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">€ {{ number_format((float)($d['lucro'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ $d['quantidade'] ?? 0 }}</td>
                    <td class="num">{{ $d['num_vendas'] ?? 0 }}</td>
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
                <th class="num">{{ $totais['total_quantidade'] ?? 0 }}</th>
                <th class="num">{{ $totais['total_num_vendas'] ?? 0 }}</th>
            </tr>
            </tfoot>
        </table>

        <div class="footer">Exportação PDF — GESFaturação</div>
    </div>

</div>
</body>
</html>
