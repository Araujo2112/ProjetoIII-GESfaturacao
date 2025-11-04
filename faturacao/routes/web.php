<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\clientes\ListaController;

Route::get('/', function () {
    return view('login');
})->name('login');

Route::post('/', [LoginController::class, 'process'])-> name('login.process');

Route::get('/dashboard', [DashboardController::class, 'index'])-> name('dashboard');

Route::get('/clientes', function () {
    return view('clientes.lista');
});

Route::get('/clientes/rankings', function () {
    return view('clientes.rankings');
});

Route::get('/clientes', [ListaController::class, 'lista'])-> name('clientes.lista');
