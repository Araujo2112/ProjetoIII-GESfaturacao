@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
        <h1 class="text-dark text-center">Visão Geral</h1>
        <div class="container py-4">
            <div class="row g-4">

                <!-- Card Faturação Hoje -->
                <div class="col">
                    <div class="card h-100 bg-light p-3 text-center shadow-sm">
                        <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                            <i class="bi bi-cart fs-1 text-primary"></i>
                        </div>
                        <div class="fw-bold fs-2 text-primary">
                            € {{ $faturadoHoje ?? '0,00' }}
                        </div>
                        <div class="small text-success fw-semibold mb-1">
                            0,00% Ontem: € 0,00
                        </div>
                        <div class="text-secondary fs-5">
                            Faturação Hoje
                        </div>
                    </div>
                </div>

                <!-- Card Faturação Mensal -->
                <div class="col">
                    <div class="card h-100 bg-light p-3 text-center shadow-sm">
                        <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                            <i class="bi bi-calendar2-month fs-1 text-primary"></i>
                        </div>
                        <div class="fw-bold fs-2 text-primary">
                            € {{ $faturadoMes ?? '0,00' }}
                        </div>
                        <div class="small text-success fw-semibold mb-1">
                            0,00% Anterior: € 0,00
                        </div>
                        <div class="text-secondary fs-5">
                            Faturação Mensal
                        </div>
                    </div>
                </div>

                <!-- Card Faturação Anual -->
                <div class="col">
                    <div class="card h-100 bg-light p-3 text-center shadow-sm">
                        <div class="mx-auto mb-2 rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:60px; height:60px;">
                            <i class="bi bi-bar-chart-line fs-1 text-primary"></i>
                        </div>
                        <div class="fw-bold fs-2 text-primary">
                            € {{ $faturadoAno ?? '0,00' }}
                        </div>
                        <div class="small text-success fw-semibold mb-1">
                            0,00% Anterior: € 0,00
                        </div>
                        <div class="text-secondary fs-5">
                            Faturação Anual
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div id="faturacaoChart" style="height:300px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        var options = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: false }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            series: [{
                name: 'Faturação',
                data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 37, 0, 10, 5, 7] // seus valores reais aqui!
            }],
            xaxis: {
                categories: [
                    '19-10', '20-10', '21-10', '22-10', '23-10', '24-10', '25-10', '26-10', '27-10', '28-10', 
                    '29-10', '30-10', '31-10', '01-11', '02-11', '03-11', '04-11', '05-11', '06-11', '07-11', 
                    '08-11', '09-11', '10-11', '11-11', '12-11', '13-11', '14-11', '15-11', '16-11', '17-11'
                ]
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return '€ ' + value;
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return '€ ' + value;
                    }
                }
            },
            colors: ['#2980FF'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.8,
                    stops: [0, 100]
                }
            },
            grid: {
                borderColor: "#e4e7ed",
            }
        };

        var chart = new ApexCharts(document.querySelector("#faturacaoChart"), options);
        chart.render();
    </script>
@endpush