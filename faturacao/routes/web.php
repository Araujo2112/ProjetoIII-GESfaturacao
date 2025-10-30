<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return view('login');
});

Route::post('/', [LoginController::class, 'process'])-> name('login.process');

Route::get('/dashboard', [DashboardController::class, 'index'])-> name('dashboard');
