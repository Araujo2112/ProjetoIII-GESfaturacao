<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\clientes\ListaClientesController;
use App\Http\Controllers\clientes\RankingClientesController;

use App\Http\Controllers\fornecedores\ListaFornecController;
use App\Http\Controllers\fornecedores\RankingFornecedoresController;

use App\Http\Controllers\produtos\ListaProdutosController;
use App\Http\Controllers\produtos\RankingProdutosController;
use App\Http\Controllers\produtos\StockProdutosController;
use App\Http\Controllers\produtos\LucroProdutosController;

use App\Http\Controllers\relatorios\DiarioVendasController;
use App\Http\Controllers\relatorios\PagamentosController;
use App\Http\Controllers\relatorios\MensalVendasController;
use App\Http\Controllers\relatorios\VencimentoController;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/', [LoginController::class, 'process'])
    -> name('login.process');

Route::post('/logout', function () {
    session()->forget('user');
    return redirect()->route('login');
})->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    -> name('dashboard');


//Clientes
Route::get('/clientes', [ListaClientesController::class, 'lista'])
    -> name('clientes.lista');

Route::get('/clientes/top5', [RankingClientesController::class, 'topClientes'])
    ->name('clientes.top');


//Fornecedores
Route::get('/fornecedores', [ListaFornecController::class, 'lista'])
    -> name('fornecedores.lista');

Route::get('/fornecedores/top5', [RankingFornecedoresController::class, 'topFornecedores'])
    ->name('fornecedores.top');

//Artigos
Route::get('/artigos', [ListaProdutosController::class, 'lista'])
    -> name('artigos.lista');

Route::get('/artigos/ranking', [RankingProdutosController::class, 'index'])
     ->name('artigos.ranking');

Route::get('/artigos/stock', [StockProdutosController::class, 'index'])
     ->name('artigos.stock');

Route::get('/artigos/lucro', [LucroProdutosController::class, 'index'])
    ->name('artigos.lucro');

//Relatorios
Route::get('/relatorios/diario', [DiarioVendasController::class, 'index'])
    -> name('relatorios.diario');

Route::get('/relatorios/pagamentos', [PagamentosController::class, 'index'])
    -> name('relatorios.pagamento');

Route::get('/relatorios/mensal', [MensalVendasController::class, 'index'])
    -> name('relatorios.mensal');

Route::get('/relatorios/vencimento', [VencimentoController::class, 'index'])
    -> name('relatorios.vencimento');