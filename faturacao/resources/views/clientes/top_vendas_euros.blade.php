@extends('layout')

@section('title', 'Top 5 Clientes — € Vendas')

@section('content')

    {{-- CONTEÚDO --}}
    <div class="col-11 p-0 m-0">

      {{-- HEADER (sem faixa dourada) --}}
      <div class="w-100" style="background:#f0f3f7;border-bottom:1px solid #e5e7eb;">
        <div class="container-fluid py-2 d-flex align-items-center gap-3">
          <div style="width:44px;height:44px;border-radius:8px;background:#c1832e;display:flex;align-items:center;justify-content:center;">
            <i class="bi bi-people text-white" style="font-size:1.2rem;"></i>
          </div>
          <div class="flex-grow-1">
            <div class="fw-semibold" style="line-height:1.1">Clientes <span class="text-muted">— Lista</span></div>
            <div class="text-muted small">Top 5 Clientes — € Vendas (faturas pagas)</div>
          </div>
        </div>
      </div>

      {{-- PAINEL --}}
      <div class="d-flex justify-content-center" style="background:linear-gradient(180deg,#0a1423 0%, #0a1423 60%, #0a1423 60%); min-height: calc(100vh - 60px);">
        <div class="w-100" style="max-width:1280px;">
          <div class="bg-white rounded shadow-sm p-4 my-4">

            <div class="d-flex align-items-center justify-content-between mb-3">
              <h5 class="m-0">Top 5 Clientes — € Vendas</h5>
              <span class="text-muted small">Ordenado por <strong>valor total faturado</strong> (status = paid)</span>
            </div>

            @php
              // Aceita a variável que vier do controlador
              $rows = $top5ClientesEuros ?? $clientes ?? collect();
            @endphp

            @if($rows->isEmpty())
              <div class="alert alert-warning mb-0">Não existem dados disponíveis (sem faturas pagas).</div>
            @else
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:60px">#</th>
                    <th>Cliente</th>
                    <th class="text-center" style="width:160px">Nº Vendas</th>
                    <th class="text-end" style="width:200px">Total (€)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($rows as $i => $c)
                    <tr>
                      <td><span class="badge bg-secondary">{{ $i + 1 }}</span></td>
                      <td>{{ $c->cliente ?? $c->name }}</td>
                      <td class="text-center">
                        <span class="badge bg-primary">{{ number_format($c->num_vendas ?? 0, 0, ',', ' ') }}</span>
                      </td>
                      <td class="text-end fw-semibold">{{ number_format($c->total_euros ?? 0, 2, ',', ' ') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif

          </div>
        </div>
      </div>

    </div>
@endsection
