@extends('layout')

@section('title', 'Clientes — Rankings')

@section('content')

      <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="navbar-link" href="#">...</a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="d-flex justify-content-center align-items-start" style="height: calc(100vh - 60px);">
        <div class="bg-white rounded shadow p-4 mt-4" style="width: 90%; min-height: 60%; overflow:auto;">

          <h5 class="mb-3">Clientes — Rankings</h5>

          <div class="card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong>Top 5 Clientes — Mais Vendas</strong>
              <span class="text-muted small">ordenado por nº de faturas</span>
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

                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div>
      </div>
@endsection
