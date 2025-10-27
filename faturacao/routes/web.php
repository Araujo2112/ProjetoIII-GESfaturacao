<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;


Route::get('/', function () {
    return view('login');
});

Route::post('/',
 [LoginController::class, 'process'])->
 name('login.process');

 Route::get('/dashboard', function () {
    return view('dashboard');
});