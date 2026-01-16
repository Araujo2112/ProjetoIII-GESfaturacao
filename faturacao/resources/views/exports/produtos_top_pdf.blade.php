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
                <th>Cód.</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th class="num">Qtd Vendida</th>
                <th class="num">Preço c/IVA (€)</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($produtos ?? []) as $i => $p)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $p['cod'] ?? '' }}</td>
                    <td>{{ $p['nome'] ?? '' }}</td>
                    <td>{{ $p['categoria'] ?? 'Sem Categoria' }}</td>
                    <td class="num">{{ number_format((float)($p['qtd'] ?? 0), 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['preco_c_iva'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
