@extends('layout')

@section('title', 'Dashboard')

@section('content')
    <div class="bg-dark-subtle min-vh-100 pt-5">

        <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
            <h1 class="text-dark text-center">Visão Geral</h1>
            <div class="container py-4">
                <div class="row g-4">

                    <!-- Faturação Hoje -->
                    <div class="col">
                        <div class="card h-100 bg-light p-3 text-center shadow-sm">
                            <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                <i class="bi bi-cart fs-1 text-primary"></i>
                            </div>
                            <div class="fw-bold fs-2 text-primary">
                                € {{ $faturadoHoje ?? '0,00' }}
                            </div>
                            <div class="small fw-semibold mb-1 {{ ($variacaoHoje['positivo'] ?? true) ? 'text-success' : 'text-danger' }}">
                                {{ $variacaoHoje['percent'] }} Ontem: € {{ $faturadoOntem ?? '0,00' }}
                            </div>
                            <div class="text-secondary fs-5">Faturação Hoje</div>
                        </div>
                    </div>

                    <!-- Faturação Mensal -->
                    <div class="col">
                        <div class="card h-100 bg-light p-3 text-center shadow-sm">
                            <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                <i class="bi bi-calendar2-month fs-1 text-primary"></i>
                            </div>
                            <div class="fw-bold fs-2 text-primary">
                                € {{ $faturadoMes ?? '0,00' }}
                            </div>
                            <div class="small fw-semibold mb-1 {{ ($variacaoMes['positivo'] ?? true) ? 'text-success' : 'text-danger' }}">
                                {{ $variacaoMes['percent'] }} Anterior: € {{ $faturadoMesAnterior ?? '0,00' }}
                            </div>
                            <div class="text-secondary fs-5">Faturação Mensal</div>
                        </div>
                    </div>

                    <!-- Faturação Anual -->
                    <div class="col">
                        <div class="card h-100 bg-light p-3 text-center shadow-sm">
                            <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                                <i class="bi bi-bar-chart-line fs-1 text-primary"></i>
                            </div>
                            <div class="fw-bold fs-2 text-primary">
                                € {{ $faturadoAno ?? '0,00' }}
                            </div>
                            <div class="small fw-semibold mb-1 {{ ($variacaoAno['positivo'] ?? true) ? 'text-success' : 'text-danger' }}">
                                {{ $variacaoAno['percent'] }} Anterior: € {{ $faturadoAnoAnterior ?? '0,00' }}
                            </div>
                            <div class="text-secondary fs-5">Faturação Anual</div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Faturação dos 7 Dias -->
        <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
            <h4 class="text-center my-4">Análise da Faturação - Últimos 7 Dias</h4>

            @if(empty($dadosCategorias['valores']) && empty($dadosCategorias['quantidadesPorDia']))

                <div class="text-center">
                    <h5>Sem faturas nos últimos 7 dias</h5>
                </div>

            @else

                <div id="faturacaoChart" style="height:300px;"></div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div id="categorias€Chart" style="height:350px;"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="categoriasQtdChart" style="height:350px;"></div>
                    </div>
                </div>

            @endif
        </section>


        <!-- Bloco Faturação Mensal e Compras -->
        <section class="bg-white rounded shadow p-4 mx-auto mb-5" style="max-width:1400px;">
            <div class="row">

                <div class="col-12 text-center mb-4">
                    <h4 class="my-4">Faturação Mensal - Ano Atual vs Ano Anterior</h4>
                    <div id="faturacaoMesChart" style="height:300px;"></div>
                </div>

                <div class="col-12 text-center">
                    <h4 class="my-4">Compras Mensal - Ano Atual vs Ano Anterior</h4>
                    <div id="comprasMesChart" style="height:300px;"></div>
                </div>

            </div>
        </section>

    </div>

    @if(isset($dadosCategorias))
        <script>
        window.dashboardCategorias = {
            quantidades: @json($dadosCategorias['quantidades'] ?? []),
            valores: @json($dadosCategorias['valores'] ?? []),
            quantidadesPorDia: @json($dadosCategorias['quantidadesPorDia'] ?? []),
            valoresPorDia: @json($dadosCategorias['valoresPorDia'] ?? [])
        };
        </script>
    @endif


    @if(isset($graficoTotais) && isset($graficoDatas) && isset($graficoMeses))
        <script>
            window.dashboardData = {
                totais: @json($graficoTotais),
                datas: @json($graficoDatas),
                mesesLabels: @json($graficoMeses['labels']),
                faturacaoAnoAtual: @json($graficoMeses['atual']),
                faturacaoAnoAnterior: @json($graficoMeses['anterior'])
            };
        </script>
    @endif

    @if(isset($graficoComprasMeses))
        <script>
            window.dashboardCompras = {
            mesesLabels: @json($graficoComprasMeses['labels']),
            comprasAnoAtual: @json($graficoComprasMeses['atual']),
            comprasAnoAnterior: @json($graficoComprasMeses['anterior'])
            };
        </script>
    @endif
    
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush