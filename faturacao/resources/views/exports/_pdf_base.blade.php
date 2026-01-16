<!doctype html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'Relatório' }}</title>

    <style>
        @page { margin: 22px 22px; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
            margin: 0;
        }

        .page { width: 100%; }

        .header { text-align: center; margin-bottom: 10px; }
        .title { font-size: 18px; margin: 0; font-weight: 700; }

        .meta {
            margin-top: 6px;
            font-size: 11px;
            line-height: 1.35;
            color: #333;
        }
        .meta strong { font-weight: 700; color: #111; }

        .box {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 12px;
            margin: 12px 0;
            page-break-inside: avoid;
        }
        .box-title { font-weight: 700; margin-bottom: 8px; }

        .chart { text-align: center; }
        .chart img {
            width: 100%;
            max-width: 780px;
            height: auto;
            display: inline-block;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
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

        td.num, th.num { text-align: right; white-space: nowrap; }
        td.center, th.center { text-align: center; }

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
        <h1 class="title">{{ $titulo ?? 'Relatório' }}</h1>

        <div class="meta">
            @if(!empty($periodoTexto))
                <div><strong>Período:</strong> {{ $periodoTexto }}</div>
            @endif

            @if(!empty($modoTexto))
                <div><strong>Modo:</strong> {{ $modoTexto }}</div>
            @endif

            <div>
                <strong>Gerado em:</strong>
                @php
                    $g = $geradoEm ?? now();
                    try {
                        echo \Carbon\Carbon::parse($g)->format('d/m/Y H:i');
                    } catch (\Exception $e) {
                        echo $g;
                    }
                @endphp
            </div>
        </div>
    </div>

    @yield('conteudo')

    <div class="footer">Exportação PDF — GESFaturação</div>
</div>
</body>
</html>
