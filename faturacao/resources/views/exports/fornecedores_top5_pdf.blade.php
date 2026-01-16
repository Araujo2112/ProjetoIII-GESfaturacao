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
                <th class="center">#</th>
                <th>Fornecedor</th>
                <th>NIF</th>
                <th class="center">Nº Compras</th>
                <th class="num">Total (€)</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($top5 ?? []) as $i => $f)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $f['fornecedor'] ?? '' }}</td>
                    <td>{{ $f['nif'] ?? '' }}</td>
                    <td class="center">{{ $f['num_compras'] ?? 0 }}</td>
                    <td class="num">{{ number_format((float)($f['total_euros'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
