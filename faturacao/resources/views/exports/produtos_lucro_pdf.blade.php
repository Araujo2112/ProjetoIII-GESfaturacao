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
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th class="num">Preço s/IVA (€)</th>
                <th class="num">Custo (€)</th>
                <th class="num">Lucro (€)</th>
            </tr>
            </thead>
            <tbody>
            @foreach(($produtos ?? []) as $p)
                <tr>
                    <td>{{ $p['cod'] ?? '' }}</td>
                    <td>{{ $p['nome'] ?? '' }}</td>
                    <td>{{ $p['categoria'] ?? '' }}</td>
                    <td class="num">{{ number_format((float)($p['preco_s_iva'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['custo'] ?? 0), 2, ',', '.') }}</td>
                    <td class="num">{{ number_format((float)($p['lucro'] ?? 0), 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endsection
