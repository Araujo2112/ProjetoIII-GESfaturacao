<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filtros opcionais por data
        $start = $request->query('start'); // yyyy-mm-dd
        $end   = $request->query('end');   // yyyy-mm-dd

        // TOP 5 CLIENTES — MAIS VENDAS (contagem de faturas por cliente)
        // Se quiseres considerar só faturas pagas, mantém where('status','paid')
        $top5ClientesMaisVendas = DB::table('invoices as i')
            ->join('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('i.status', 'paid')
            ->when($start, fn($q) => $q->whereDate('i.issued_at', '>=', $start))
            ->when($end,   fn($q) => $q->whereDate('i.issued_at', '<=', $end))
            ->groupBy('i.customer_id', 'c.name')
            ->selectRaw('i.customer_id, c.name as cliente, COUNT(*) as num_vendas, SUM(i.total) as total_euros')
            ->orderByDesc('num_vendas')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'filters' => ['start' => $start, 'end' => $end],
            'top5ClientesMaisVendas' => $top5ClientesMaisVendas,
        ]);
    }
}
