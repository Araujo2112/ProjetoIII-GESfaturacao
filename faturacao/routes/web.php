<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\LoginController;

// LOGIN
Route::get('/', function () {
    return view('login');
});
Route::post('/', [LoginController::class, 'process'])->name('login.process');

// DASHBOARD (VISÃO GLOBAL)
Route::get('/dashboard', function () {
    // Datas úteis
    $today  = Carbon::today();
    $startMonth = Carbon::now()->startOfMonth();
    $endMonth   = Carbon::now()->endOfMonth();
    $startYear  = Carbon::now()->startOfYear();
    $endYear    = Carbon::now()->endOfYear();

    // KPIs baseados na tabela "invoices" (o que já existe)
    $kpis = [
        'faturadoHoje' => DB::table('invoices')
            ->where('status', 'paid')
            ->whereDate('issued_at', $today)
            ->sum('total'),

        'faturadoMes' => DB::table('invoices')
            ->where('status', 'paid')
            ->whereBetween('issued_at', [$startMonth, $endMonth])
            ->sum('total'),

        'faturadoAno' => DB::table('invoices')
            ->where('status', 'paid')
            ->whereBetween('issued_at', [$startYear, $endYear])
            ->sum('total'),

        // Sem tabela de IVA/fornecedores ainda -> placeholders
        'ivaMesAnterior' => null,
        'ivaMesAtual'    => null,
        'ivaAnual'       => null,

        // Contagens
        'numClientes' => DB::table('customers')->count(),
        'numFornecedores' => null, // placeholder (quando existir tabela "suppliers")
        'numFaturas'  => DB::table('invoices')->count(),
    ];

    return view('dashboard', compact('kpis'));
})->name('dashboard');

// CLIENTES → RANKINGS
Route::get('/clientes/rankings', function () {
    $top5ClientesMaisVendas = DB::table('invoices as i')
        ->join('customers as c', 'c.id', '=', 'i.customer_id')
        ->where('i.status', 'paid')
        ->groupBy('i.customer_id', 'c.name')
        ->selectRaw('c.name AS cliente, COUNT(*) AS num_vendas, SUM(i.total) AS total_euros')
        ->orderByDesc('num_vendas')
        ->limit(5)
        ->get();

    return view('clientes.rankings', compact('top5ClientesMaisVendas'));
})->name('clientes.rankings');
