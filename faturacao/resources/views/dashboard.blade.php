@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div class="bg-dark-subtle d-flex justify-content-center align-items-start min-vh-100 pt-5">
    <div class="bg-white rounded shadow p-4 mx-auto" style="width:100%; max-width:1400px; min-height:380px;">
        <h1 class="text-dark text-center">Visão Geral</h1>
        <div class="container py-4">
            <div class="row g-4">
                <div class="col">
                    <div class="card h-100 bg-light p-2">
                        <div class="d-flex h-100 align-items-center">
                            <div class="d-flex flex-column justify-content-between flex-grow-1 h-100">
                                <div class="d-flex align-items-baseline justify-content-start mb-2">
                                    <span class="fw-bold fs-3 text-primary">...</span>
                                </div>
                                <div>
                                    <span class="text-secondary fs-5">Faturado Hoje</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <div class="col">
                        <div class="card h-100 bg-light p-2">
                            <div class="d-flex h-100 align-items-center">
                                <div class="d-flex flex-column justify-content-between flex-grow-1 h-100">
                                    <div class="d-flex align-items-baseline justify-content-start mb-2">
                                        <span class="fw-bold fs-3 text-primary">...</span>
                                    </div>
                                    <div>
                                        <span class="text-secondary fs-5">Faturado Mês</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card h-100 bg-light p-2">
                            <div class="d-flex h-100 align-items-center">
                                <div class="d-flex flex-column justify-content-between flex-grow-1 h-100">
                                    <div class="d-flex align-items-baseline justify-content-start mb-2">
                                        <span class="fw-bold fs-3 text-primary">{{ $faturadoAno }}</span>
                                    </div>
                                    <div>
                                        <span class="text-secondary fs-5">Faturado Ano</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card h-100 bg-light p-2">
                            <div class="d-flex h-100 align-items-center">
                                <div class="d-flex flex-column justify-content-between flex-grow-1 h-100">
                                    <div class="d-flex align-items-baseline justify-content-start mb-2">
                                        <span class="fw-bold fs-3 text-primary">...</span>
                                    </div>
                                    <div>
                                        <span class="text-secondary fs-5">IVA Mês</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="card h-100 bg-light p-2">
                            <div class="d-flex h-100 align-items-center">
                                <div class="d-flex flex-column justify-content-between flex-grow-1 h-100">
                                    <div class="d-flex align-items-baseline justify-content-start mb-2">
                                        <span class="fw-bold fs-3 text-primary">...</span>
                                    </div>
                                    <div>
                                        <span class="text-secondary fs-5">IVA Anual</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                                
                </div>
            </div>
        </div>
    </div>
@endsection
