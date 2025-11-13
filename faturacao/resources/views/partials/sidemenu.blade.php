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
                    <li><a class="dropdown-item" href="{{ route('clientes.lista', [], false) }}">Lista</a></li>
                    <li><a class="dropdown-item" href="{{ route('clientes.top.euros') }}">Top € Vendas</a></li>
                    <li><a class="dropdown-item" href="{{ route('clientes.top.quantidade') }}">Top Nº Vendas</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Fornecedores
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Lista</a></li>
                    <li><a class="dropdown-item" href="#">Rankings</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Produtos
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Catálogo</a></li>
                    <li><a class="dropdown-item" href="#">Rankings</a></li>
                    <li><a class="dropdown-item" href="#">Abaixo do Stock</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Faturas
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Listagem</a></li>
                    <li><a class="dropdown-item" href="#">A vencer (30 dias)</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Compras
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Listagem</a></li>
                    <li><a class="dropdown-item" href="#">A vencer (30 dias)</a></li>
                </ul>
            </div>

            <div class="btn-group dropend w-100 mb-2">
                <button type="button"
                        class="btn btn-dark nav-link text-white w-100 dropdown-toggle d-flex justify-content-between align-items-center text-start"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    Análise
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Gráficos</a></li>
                    <li><a class="dropdown-item" href="#">Comparativos</a></li>
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
