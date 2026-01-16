@extends('exports._pdf_base')

@section('conteudo')

    @if(!empty($chartImg))
        <div class="box">
            <div class="box-title">Gráfico</div>
            <div class="chart">
                <img src="{{ $chartImg }}" alt="Gráfico">
            </div>
        </div>
    @endif

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
                            try { $mesFmt = $mesRaw ? \Carbon\Carbon::createFromFormat('Y-m', $mesRaw)->format('m/Y') : ''; }
                            catch (\Exception $e) { $mesFmt = $mesRaw; }
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
    </div>

@endsection
