@extends('layout')

@section('title', 'Lista de Fornecedores')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
            <h1 class="text-dark text-center">Lista de Fornecedores</h1>
            
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
                                            'sort' => 'name',
                                            'direction' => (request('sort') === 'name' && request('direction') === 'asc') ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}">
                                            Nome
                                            @if(request('sort') === 'name')
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
                                            'sort' => 'vatNumber',
                                            'direction' => (request('sort') === 'vatNumber' && request('direction') === 'asc') ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}">
                                            NIF
                                            @if(request('sort') === 'vatNumber')
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
                                            'sort' => 'phone',
                                            'direction' => (request('sort') === 'phone' && request('direction') === 'asc') ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}">
                                            Contacto
                                            @if(request('sort') === 'phone')
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
                                            'sort' => 'email',
                                            'direction' => (request('sort') === 'email' && request('direction') === 'asc') ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}">
                                            Email
                                            @if(request('sort') === 'email')
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
                                            'sort' => 'zipCode',
                                            'direction' => (request('sort') === 'zipCode' && request('direction') === 'asc') ? 'desc' : 'asc',
                                            'page' => 1
                                        ]) }}">
                                            Cód. Postal/Localidade
                                            @if(request('sort') === 'zipCode')
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
                                @foreach ($fornecedores as $fornec)
                                    <tr>
                                        <td>{{ $fornec['code'] ?? '' }}</td>
                                        <td>{{ $fornec['name'] ?? '' }}</td>
                                        <td>{{ $fornec['vatNumber'] ?? '' }}</td>
                                        <td>{{ $fornec['phone'] ?? '' }}</td>
                                        <td>{{ $fornec['email'] ?? '' }}</td>
                                        <td>{{ ($fornec['zipCode'] ?? '') . ', ' . ($fornec['city'] ?? '') }}</td>
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