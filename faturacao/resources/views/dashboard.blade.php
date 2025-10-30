@extends('layout')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row" style="height: 100vh">
            <div class="col-1 bg-dark d-flex flex-column justify-content-between" style="height: 100vh;">
                <div>
                    <nav class="navbar bg-dark border-bottom border-white" data-bs-theme="dark">
                        <div class="container-fluid">
                            <a class="navbar-brand" href="dashboard">GESFaturação</a>
                        </div>
                    </nav>

                    <nav class="nav flex-column">
                        <div class="btn-group dropend w-100 mb-2 mt-2">
                            <button type="button" class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start" data-bs-toggle="dropdown" aria-expanded="false">Clientes</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Lista</a></li>
                                <li><a class="dropdown-item" href="#">Rankings</a></li>
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

                <div class="d-flex justify-content-center align-items-center" style="height: calc(100vh - 60px);">
                    <div class="bg-white rounded shadow p-4" style="width: 90%; height: 80%;">
                        <h1 class="text-dark text-center">Visão Geral</h1>
                        
                        <div class="container py-4">
                            <div class="row d-flex align-items-stretch">

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
                

            </div>

        </div>
    </div>
@endsection
