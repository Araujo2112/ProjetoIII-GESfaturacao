@extends('layout')

@section('title', 'Catálogo de Produtos')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Catálogo de Artigos</h1>
            
            <div class="d-flex justify-content-between align-items-center mb-3 gap-3" style="width:100%">
                <form method="GET" class="mb-3 d-flex align-items-center gap-2">
                    <label for="rows" class="text-dark">Mostrar</label>
                    <select name="rows" id="rows" onchange="this.form.submit()" class="form-select w-auto">
                        <option value="25" {{ $rows == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $rows == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $rows == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-dark">registos por página</span>
                </form>
                <form method="GET" class="d-flex gap-0" style="">
                    <input type="text" name="search" class="border border-secondar form-control-sm text-dark rounded-0" placeholder="Pesquisar..." value="{{ request('search') }}" style="min-width:180px;" />
                    <button class="btn btn-primary rounded-0 btn-sm" type="submit">Pesquisar</button>
                </form>
            </div>

            <div class="container py-2">
                <div class="row d-flex align-items-stretch">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'code',
                                                'direction' => (request('sort') === 'code' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Cód.
                                            @if(request('sort') === 'code')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'description',
                                                'direction' => (request('sort') === 'description' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Nome
                                            @if(request('sort') === 'description')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'category',
                                                'direction' => (request('sort') === 'category' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Categoria
                                            @if(request('sort') === 'category')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'type',
                                                'direction' => (request('sort') === 'type' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Tipo
                                            @if(request('sort') === 'type')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'pricePvp',
                                                'direction' => (request('sort') === 'pricePvp' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Preço PVP
                                            @if(request('sort') === 'pricePvp')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'tax',
                                                'direction' => (request('sort') === 'tax' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            IVA
                                            @if(request('sort') === 'tax')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'stock',
                                                'direction' => (request('sort') === 'stock' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Stock
                                            @if(request('sort') === 'stock')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a
                                            class="text-dark text-decoration-none"
                                            href="{{ request()->fullUrlWithQuery([
                                                'sort' => 'unit',
                                                'direction' => (request('sort') === 'unit' && request('direction') === 'asc') ? 'desc' : 'asc',
                                                'page' => 1
                                            ]) }}"
                                        >
                                            Unidade
                                            @if(request('sort') === 'unit')
                                                @if(request('direction') === 'asc')
                                                    ▲
                                                @else
                                                    ▼
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($produtos as $produtos)
                                    <tr>
                                        <td>{{ $produtos['code'] ?? '' }}</td>
                                        <td>{{ $produtos['description'] ?? '' }}</td>
                                        <td>{{ $produtos['category'] ?? 'Sem categoria' }}</td>
                                        <td>{{ $produtos['type'] ?? '' }}</td>
                                        <td>{{ number_format($produtos['pricePvp'] ?? 0, 2, ',', '.') }} €</td>
                                        <td>{{ number_format($produtos['tax'] ?? 0, 2, ',', '.') }} %</td>
                                        <td>{{ $produtos['stock'] ?? '' }}</td>
                                        <td>{{ $produtos['unit'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="mt-2 mb-2 text-end text-dark">
                    <span>
                        {{ ($rows*($paginaAtual-1) + 1) }} a {{ min($rows*$paginaAtual, $totalRegistos) }} de {{ $totalRegistos }} registos
                    </span>
                </div>

                <div class="mt-2">
                    <nav>
                        <ul class="pagination justify-content-end">
                            <li class="page-item {{ $paginaAtual == 1 ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginaAtual - 1, 'rows' => $rows]) }}">Anterior</a>
                            </li>

                            @for ($i = 1; $i <= $totalPaginas; $i++)
                                @if ($i == 1 || $i == $totalPaginas || abs($i - $paginaAtual) < 2)
                                    <li class="page-item {{ $paginaAtual == $i ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i, 'rows' => $rows]) }}">{{ $i }}</a>
                                    </li>
                                @elseif ($i == $paginaAtual - 2 || $i == $paginaAtual + 2)
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                @endif
                            @endfor

                            <li class="page-item {{ $paginaAtual == $totalPaginas ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginaAtual + 1, 'rows' => $rows]) }}">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection