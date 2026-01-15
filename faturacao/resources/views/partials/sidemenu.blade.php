<div class="col-1 bg-dark d-flex flex-column justify-content-between position-fixed" style="height: 100vh;">
    <div>
        <nav class="navbar bg-dark border-bottom border-white" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand text-white" href="{{ route('dashboard') }}">GESFaturação</a>
            </div>
        </nav>

        <nav class="nav flex-column">

            <div class="btn-group dropend w-100 mb-2 mt-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Clientes
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('clientes.lista') }}">Lista</a></li>
                    <li><a class="dropdown-item" href="{{ route('clientes.top') }}">Top 5</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Fornecedores
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('fornecedores.lista') }}">Lista</a></li>
                    <li><a class="dropdown-item" href="{{ route('fornecedores.top') }}">Top 5</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Artigos
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('artigos.lista') }}">Catálogo</a></li>
                    <li><a class="dropdown-item" href="{{ route('artigos.ranking') }}">Top Vendas</a></li>
                    <li><a class="dropdown-item" href="{{ route('artigos.stock') }}">Abaixo do Stock</a></li>
                    <li><a class="dropdown-item" href="{{ route('artigos.lucro') }}">Top % Lucro</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Relatórios
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('relatorios.vencimento') }}">Cashflow - Vencimentos</a></li>
                    <li><a class="dropdown-item" href="{{ route('relatorios.diario') }}">Diário</a></li>
                    <li><a class="dropdown-item" href="{{ route('relatorios.pagamento') }}">Pagamentos</a></li>
                    <li><a class="dropdown-item" href="#">Dias da semana</a></li>
                    <li><a class="dropdown-item" href="{{ route('relatorios.mensal') }}">Mensal</a></li>
                </ul>
            </div>

        </nav>
    </div>

    <div class="border-top border-white w-100 p-2 mb-3">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link text-white d-flex align-items-center btn btn-link p-0 text-start w-100">
                <i class="bi bi-arrow-bar-right me-2"></i> Logout
            </button>
        </form>
    </div>
</div>
