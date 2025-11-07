<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\clientes\ListaController;
use App\Http\Controllers\clientes\RankingController;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

Route::get('/__diag', function () {
    if (!session()->has('user.token')) {
        return 'Sem token na sessão. Faz login outra vez.';
    }

    $base = 'https://api.gesfaturacao.pt/api/v1.0.4';
    $headers = [
        'Authorization' => session('user.token'), // EXATAMENTE como a Lista
        'Accept'        => 'application/json',
    ];

    $out = [];

    // 1) CLIENTES — exatamente como a tua Lista
    $r = Http::withHeaders($headers)->get("$base/clients/5/1");
    $out['clients'] = [
        'ok'     => $r->successful(),
        'status' => $r->status(),
        'count'  => count(($r->json()['data'] ?? [])),
        'sample' => ($r->json()['data'][0] ?? null),
    ];

    // 2) DOCS — tenta uma lista de endpoints no mesmo esquema {rows}/{page}
    $candidates = [
        'invoices', 'sales', 'documents', 'sales-documents', 'sales/invoices',
        'faturas', 'facturas', 'documentos-venda',
    ];

    foreach ($candidates as $p) {
        $url = "$base/$p/5/1";
        $res = Http::withHeaders($headers)->get($url);
        $data = $res->json()['data'] ?? [];
        $out['docs'][$p] = [
            'url'    => $url,
            'ok'     => $res->successful(),
            'status' => $res->status(),
            'count'  => is_array($data) ? count($data) : 0,
            'sample' => is_array($data) && count($data) ? $data[0] : null,
        ];
        if ($res->successful() && !empty($data)) {
            // Encontrou um endpoint com dados — paramos aqui
            break;
        }
    }

    return response()->json($out, 200, [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
});


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



// Rankings
Route::get('/clientes/top-euros', [RankingController::class, 'topEuros'])
    ->name('clientes.top.euros');

Route::get('/clientes/top-quantidade', [RankingController::class, 'topQuantidade'])
    ->name('clientes.top.quantidade');

Route::post('/logout', function () {
    session()->forget('user');   // limpa a tua sessão
    // se estiveres a usar Auth::, podes também fazer: Auth::logout();
    return redirect()->route('login');  // volta à página de login
})->name('logout');
