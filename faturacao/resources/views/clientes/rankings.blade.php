@extends('layout')

@section('title', 'Clientes — Rankings')

@section('content')
<div class="container-fluid">
  <div class="row" style="height: 100vh">
    {{-- Lateral --}}
    <div class="col-1 bg-dark d-flex flex-column justify-content-between" style="height: 100vh;">
      <div>
        <nav class="navbar bg-dark border-bottom border-white" data-bs-theme="dark">
          <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">GESFaturação</a>
          </div>
        </nav>

        <nav class="nav flex-column">
          <div class="btn-group dropend w-100 mb-2 mt-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Clientes</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Lista</a></li>
              <li><a class="dropdown-item" href="{{ route('clientes.rankings') }}">Rankings</a></li>
            </ul>
          </div>

          <div class="btn-group dropend w-100 mb-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Fornecedores</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Lista</a></li>
              <li><a class="dropdown-item" href="#">Rankings</a></li>
            </ul>
          </div>

          <div class="btn-group dropend w-100 mb-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Produtos</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Catálogo</a></li>
              <li><a class="dropdown-item" href="#">Rankings</a></li>
              <li><a class="dropdown-item" href="#">Abaixo do Stock</a></li>
            </ul>
          </div>

          <div class="btn-group dropend w-100 mb-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Faturas</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Listagem</a></li>
              <li><a class="dropdown-item" href="#">A vencer (30 dias)</a></li>
            </ul>
          </div>

          <div class="btn-group dropend w-100 mb-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Compras</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Listagem</a></li>
              <li><a class="dropdown-item" href="#">A vencer (30 dias)</a></li>
            </ul>
          </div>

          <div class="btn-group dropend w-100 mb-2">
            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Análise</button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Gráficos</a></li>
              <li><a class="dropdown-item" href="#">Comparativos</a></li>
            </ul>
          </div>
        </nav>
      </div>

      <div class="border-top border-white w-100 p-2 mb-3">
        <a href="#" class="nav-link text-white d-flex align-items-center">
          <i class="bi bi-arrow-bar-right me-2"></i> Logout
        </a>
      </div>
    </div>

    {{-- Conteúdo --}}
    <div class="col-11 p-0 m-0" style="background: transparent;">
      <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="navbar-link" href="#"><i class="bi bi-arrow-bar-right me-2"></i>Logout</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="d-flex justify-content-center align-items-start" style="height: calc(100vh - 60px);">
        <div class="bg-white rounded shadow p-4 mt-4" style="width: 90%; min-height: 60%; overflow:auto;">

          <h5 class="mb-3">Clientes — Rankings</h5>

          {{-- Top 5 Clientes — Mais Vendas --}}
          <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong>Top 5 Clientes — Mais Vendas</strong>
              <span class="text-muted small">ordenado por nº de faturas (status = paid)</span>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-hover table-sm mb-0 align-middle">
                  <thead class="table-light">
                    <tr>
                      <th style="width:60px;">#</th>
                      <th>Cliente</th>
                      <th class="text-end" style="width:140px;">Nº Vendas</th>
                      <th class="text-end" style="width:160px;">Total (€)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse(($top5ClientesMaisVendas ?? []) as $i => $row)
                      <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td>{{ $row->cliente }}</td>
                        <td class="text-end">
                          <span class="badge text-bg-primary">{{ number_format($row->num_vendas, 0, ',', ' ') }}</span>
                        </td>
                        <td class="text-end">{{ number_format($row->total_euros ?? 0, 2, ',', ' ') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-muted py-4">
                          Sem dados. Insira faturas com status <code>paid</code>.
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {{-- Aqui depois metemos o "Top 5 Clientes — € Vendas" --}}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
