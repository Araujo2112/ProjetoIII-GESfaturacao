@extends('layout')

@section('title', 'Dashboard')

@section('content')
    <div class="container-fluid">
        <div class="row" style="height: 100vh">
            {{-- LATERAL --}}
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

            {{-- CONTEÚDO --}}
            <div class="col-11 p-0 m-0">

                {{-- HEADER SUPERIOR (compacto) --}}
                <div class="w-100" style="background:#f0f3f7;border-bottom:1px solid #e5e7eb;">
                    <div class="container-fluid py-2 d-flex align-items-center gap-3">
                        <div style="width:44px;height:44px;border-radius:8px;background:#c1832e;display:flex;align-items:center;justify-content:center;">
                            <i class="bi bi-currency-euro text-white" style="font-size:1.2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="line-height:1.1">GESFaturação <span class="text-muted">— Visão Global</span></div>
                            <div class="text-muted small">Bem-vindo</div>
                        </div>
                        <div class="d-none d-xl-flex flex-wrap gap-4 small text-muted">
                            <div>Tot. Faturado: <strong>{{ number_format($kpis['faturadoAno'] ?? 0,2,',',' ') }} €</strong></div>
                            <div>Nº Clientes: <strong>{{ number_format($kpis['numClientes'] ?? 0,0,',',' ') }}</strong></div>
                            <div>Nº Faturas: <strong>{{ number_format($kpis['numFaturas'] ?? 0,0,',',' ') }}</strong></div>
                        </div>
                    </div>
                </div>

                {{-- FAIXA DOURADA VISUAL --}}
                <div class="w-100" style="background:#b27a2a;">
                    <div class="container-fluid">
                        <ul class="nav small">
                            <li class="nav-item"><span class="nav-link text-white py-2 px-3">Visão Global</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Orçamentos</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Doc. de Transporte</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Vendas</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Compras</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Análise</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">Tabelas</span></li>
                            <li class="nav-item"><span class="nav-link text-white-50 py-2 px-3">POS</span></li>
                        </ul>
                    </div>
                </div>

                {{-- PAINEL PRINCIPAL --}}
                <div class="d-flex justify-content-center" style="background:linear-gradient(180deg,#0a1423 0%, #0a1423 60%, #0a1423 60%); min-height: calc(100vh - 96px);">
                    <div class="w-100" style="max-width:1280px;">
                        <div class="bg-white rounded shadow-sm p-4 my-4">

                            {{-- Título central (muito discreto) --}}
                            <div class="text-center text-muted mb-3" style="letter-spacing:.05em">
                                <i class="bi bi-currency-euro me-1"></i> Visão Global
                            </div>

                            {{-- ESTILOS DOS KPIs (inline p/ não tocar no teu build) --}}
                            <style>
                                .metric{
                                    border:1px solid #eceff3; border-radius:12px; padding:18px 18px;
                                    display:flex; align-items:center; gap:16px; background:#fff;
                                }
                                .metric:hover{ background:#fcfdff; }
                                .metric .ic{
                                    width:48px;height:48px;border-radius:10px;background:#f3f5f8;
                                    display:flex;align-items:center;justify-content:center;
                                    color:#6b7280;font-size:1.4rem;
                                }
                                .metric .value{ font-size:1.5rem; font-weight:700; color:#111827; line-height:1; }
                                .metric .label{ font-size:.85rem; color:#6b7280; margin-top:4px; }
                                @media (max-width: 992px){ .metric{ padding:16px } .metric .value{ font-size:1.3rem } }
                            </style>

                            {{-- GRELHA DE KPIs: 3 colunas desktop / 2 tablet / 1 mobile --}}
                            <div class="row g-3">
                                {{-- Faturado Hoje --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-currency-euro"></i></div>
                                        <div>
                                            <div class="value">{{ number_format($kpis['faturadoHoje'] ?? 0, 2, ',', ' ') }} €</div>
                                            <div class="label">Faturado Hoje</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Faturado Mês --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-currency-euro"></i></div>
                                        <div>
                                            <div class="value">{{ number_format($kpis['faturadoMes'] ?? 0, 2, ',', ' ') }} €</div>
                                            <div class="label">Faturado Mês</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Faturado Ano --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-currency-euro"></i></div>
                                        <div>
                                            <div class="value">{{ number_format($kpis['faturadoAno'] ?? 0, 2, ',', ' ') }} €</div>
                                            <div class="label">Faturado Ano</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- IVA Mês Anterior --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-receipt"></i></div>
                                        <div>
                                            <div class="value">
                                                {{ ($kpis['ivaMesAnterior'] ?? null) === null ? '—' : number_format($kpis['ivaMesAnterior'], 2, ',', ' ').' €' }}
                                            </div>
                                            <div class="label">IVA Mês Anterior</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- IVA Mês Atual --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-receipt-cutoff"></i></div>
                                        <div>
                                            <div class="value">
                                                {{ ($kpis['ivaMesAtual'] ?? null) === null ? '—' : number_format($kpis['ivaMesAtual'], 2, ',', ' ').' €' }}
                                            </div>
                                            <div class="label">IVA Mês Atual</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- IVA Anual --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-percent"></i></div>
                                        <div>
                                            <div class="value">
                                                {{ ($kpis['ivaAnual'] ?? null) === null ? '—' : number_format($kpis['ivaAnual'], 2, ',', ' ').' €' }}
                                            </div>
                                            <div class="label">IVA Anual</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nº Clientes --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-people-fill"></i></div>
                                        <div>
                                            <div class="value">{{ number_format($kpis['numClientes'] ?? 0, 0, ',', ' ') }}</div>
                                            <div class="label">Nº Clientes</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nº Fornecedores --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-truck"></i></div>
                                        <div>
                                            <div class="value">
                                                {{ ($kpis['numFornecedores'] ?? null) === null ? '—' : number_format($kpis['numFornecedores'], 0, ',', ' ') }}
                                            </div>
                                            <div class="label">Nº Fornecedores</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Nº Faturas --}}
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="metric">
                                        <div class="ic"><i class="bi bi-file-earmark-bar-graph"></i></div>
                                        <div>
                                            <div class="value">{{ number_format($kpis['numFaturas'] ?? 0, 0, ',', ' ') }}</div>
                                            <div class="label">Nº Faturas Registadas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- /grelha --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
