@extends('layout')

@section('title', 'Lista de Clientes')

@section('content')
    <div style="margin-left:200px; margin-top:60px;">
        <div class="bg-white rounded shadow p-4">
            <h1 class="text-dark text-center">Lista de Clientes</h1>

            <form method="GET" class="mb-3 d-flex align-items-center gap-2">
                <label for="rows" class="text-dark">Mostrar</label>
                <select name="rows" id="rows" onchange="this.form.submit()" class="form-select w-auto">
                    <option value="25" {{ $rows == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $rows == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $rows == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-dark">registos por página</span>
            </form>

            <div class="container py-4">
                <div class="row d-flex align-items-stretch">
                    <div style="overflow-x:auto;">
                        <table class="table table-sm table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Cód.</th>
                                    <th>Cód. Int.</th>
                                    <th>Nome</th>
                                    <th>NIF</th>
                                    <th>Contacto</th>
                                    <th>Email</th>
                                    <th>Cód. Postal/Localidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientes as $cliente)
                                    <tr>
                                        <td>{{ $cliente['code'] ?? '' }}</td>
                                        <td>{{ $cliente['internalCode'] ?? '' }}</td>
                                        <td>{{ $cliente['name'] ?? '' }}</td>
                                        <td>{{ $cliente['vatNumber'] ?? '' }}</td>
                                        <td>{{ $cliente['phone'] ?? '' }}</td>
                                        <td>{{ $cliente['email'] ?? '' }}</td>
                                        <td>{{ ($cliente['zipCode'] ?? '') . ' ' . ($cliente['city'] ?? '') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-2 mb-2 text-end">
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
