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
use App\Http\Controllers\produtos\AbaixoStockProdutosController;
use App\Http\Controllers\produtos\RankingLucroProdutosController;

use App\Http\Controllers\relatorios\DiarioVendasController;

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

//Produtos
Route::get('/produtos', [ListaProdutosController::class, 'lista'])
    -> name('produtos.lista');

Route::get('/produtos/ranking', [RankingProdutosController::class, 'index'])
     ->name('produtos.ranking');

Route::get('/produtos/abaixo-stock', [AbaixoStockProdutosController::class, 'index'])
     ->name('produtos.abaixoStock');

Route::get('/produtos/ranking-lucro', [RankingLucroProdutosController::class, 'index'])
    ->name('produtos.rankingLucro');

//Relatorios
Route::get('/relatorios/diario', [DiarioVendasController::class, 'index'])
    -> name('relatorios.diario');