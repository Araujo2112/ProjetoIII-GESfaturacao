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
    ->name('login.process');

Route::post('/logout', function () {
    session()->forget('user');
    return redirect()->route('login');
})->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');


//Clientes
Route::get('/clientes', [ListaClientesController::class, 'lista'])
    ->name('clientes.lista');

Route::get('/clientes/top5', [RankingClientesController::class, 'topClientes'])
    ->name('clientes.top');

Route::post('/clientes/top5/export/pdf', [RankingClientesController::class, 'exportPdf'])  //usa template
    ->name('clientes.top.export.pdf');

Route::get('/clientes/top5/export/csv', [RankingClientesController::class, 'exportCsv'])  //dowload direto
    ->name('clientes.top.export.csv');


//Fornecedores
Route::get('/fornecedores', [ListaFornecController::class, 'lista'])
    ->name('fornecedores.lista');

Route::get('/fornecedores/top5', [RankingFornecedoresController::class, 'topFornecedores'])
    ->name('fornecedores.top');

Route::post('/fornecedores/top5/export/pdf', [RankingFornecedoresController::class, 'exportPdf'])
    ->name('fornecedores.top.export.pdf');

Route::get('/fornecedores/top5/export/csv', [RankingFornecedoresController::class, 'exportCsv'])
    ->name('fornecedores.top.export.csv');


//Artigos
Route::get('/artigos', [ListaProdutosController::class, 'lista'])
    ->name('artigos.lista');

Route::get('/artigos/ranking', [RankingProdutosController::class, 'index'])
    ->name('artigos.ranking');

Route::get('/artigos/stock', [StockProdutosController::class, 'index'])
    ->name('artigos.stock');

Route::get('/artigos/lucro', [LucroProdutosController::class, 'index'])
    ->name('artigos.lucro');

Route::post('/artigos/ranking/export/pdf', [RankingProdutosController::class, 'exportPdf'])
    ->name('artigos.ranking.export.pdf');

Route::get('/artigos/ranking/export/csv', [RankingProdutosController::class, 'exportCsv'])
    ->name('artigos.ranking.export.csv');

Route::post('/produtos/stock/export/pdf', [StockProdutosController::class, 'exportPdf'])
    ->name('produtos.stock.export.pdf');

Route::get('/produtos/stock/export/csv', [StockProdutosController::class, 'exportCsv'])
    ->name('produtos.stock.export.csv');


// EXPORT PDF/CSV
Route::post('/artigos/lucro/export/pdf', [LucroProdutosController::class, 'exportPdf'])
    ->name('artigos.lucro.export.pdf');

Route::get('/artigos/lucro/export/csv', [LucroProdutosController::class, 'exportCsv'])
    ->name('artigos.lucro.export.csv');


//Relatorios
Route::get('/relatorios/diario', [DiarioVendasController::class, 'index'])
    ->name('relatorios.diario');

Route::get('/relatorios/pagamentos', [PagamentosController::class, 'index'])
    ->name('relatorios.pagamento');

Route::get('/relatorios/mensal', [MensalVendasController::class, 'index'])
    ->name('relatorios.mensal');

Route::get('/relatorios/vencimento', [VencimentoController::class, 'index'])
    ->name('relatorios.vencimento');

Route::post('/relatorios/diario/export/pdf', [DiarioVendasController::class, 'exportPdf'])
    ->name('relatorios.diario.export.pdf');

Route::get('/relatorios/diario/export/csv', [DiarioVendasController::class, 'exportCsv'])
    ->name('relatorios.diario.export.csv');

Route::post('/relatorios/mensal/export/pdf', [MensalVendasController::class, 'exportPdf'])
    ->name('relatorios.mensal.export.pdf');

Route::get('/relatorios/mensal/export/csv', [MensalVendasController::class, 'exportCsv'])
    ->name('relatorios.mensal.export.csv');

Route::post('/relatorios/pagamentos/export/pdf', [PagamentosController::class, 'exportPdf'])
    ->name('relatorios.pagamentos.export.pdf');

Route::get('/relatorios/pagamentos/export/csv', [PagamentosController::class, 'exportCsv'])
    ->name('relatorios.pagamentos.export.csv');

Route::post('/relatorios/vencimento/export/pdf', [VencimentoController::class, 'exportPdf'])
    ->name('relatorios.vencimento.export.pdf');

Route::get('/relatorios/vencimento/export/csv', [VencimentoController::class, 'exportCsv'])
    ->name('relatorios.vencimento.export.csv');
