@extends('exports._pdf_base')

@section('conteudo')

    @if(!empty($chartImg))
        <div class="box">
            <div class="box-title">Gráfico — Cashflow</div>
            <div class="chart">
                <img src="{{ $chartImg }}" alt="Cashflow">
            </div>
        </div>
    @endif

    <div class="box">
        <div class="box-title">Faturas de Vendas a vencer (30 dias)</div>
        <table>
            <thead>
            <tr>
                <th>Nº</th>
                <th>Cliente</th>
                <th>NIF</th>
                <th>Data</th>
                <th>Vencimento</th>
                <th class="num">Dívida</th>
            </tr>
            </thead>
            <tbody>
            @forelse(($vendas ?? []) as $f)
                <tr>
                    <td>{{ $f['number'] ?? 'N/D' }}</td>
                    <td>{{ $f['client']['name'] ?? 'N/D' }}</td>
                    <td>{{ $f['client']['vatNumber'] ?? 'N/D' }}</td>
                    <td>{{ !empty($f['date']) ? \Carbon\Carbon::parse($f['date'])->format('d/m/Y') : '' }}</td>
                    <td>{{ !empty($f['expiration']) ? \Carbon\Carbon::parse($f['expiration'])->format('d/m/Y') : '' }}</td>
                    <td class="num">€ {{ number_format((float)abs($f['balance'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">Sem faturas de vendas a vencer</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="box">
        <div class="box-title">Faturas de Compras a vencer (30 dias)</div>
        <table>
            <thead>
            <tr>
                <th>Nº</th>
                <th>Fornecedor</th>
                <th>NIF</th>
                <th>Data</th>
                <th>Vencimento</th>
                <th class="num">Dívida</th>
            </tr>
            </thead>
            <tbody>
            @forelse(($compras ?? []) as $f)
                <tr>
                    <td>{{ $f['number'] ?? 'N/D' }}</td>
                    <td>{{ $f['supplier']['name'] ?? 'N/D' }}</td>
                    <td>{{ $f['supplier']['vatNumber'] ?? 'N/D' }}</td>
                    <td>{{ !empty($f['date']) ? \Carbon\Carbon::parse($f['date'])->format('d/m/Y') : '' }}</td>
                    <td>{{ !empty($f['expiration']) ? \Carbon\Carbon::parse($f['expiration'])->format('d/m/Y') : '' }}</td>
                    <td class="num">€ {{ number_format((float)abs($f['balance'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="center">Sem faturas de compras a vencer</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

@endsection
