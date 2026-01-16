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
        <div class="box-title">Tabela</div>

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
            @foreach(($clientes ?? []) as $c)
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
    </div>

@endsection
