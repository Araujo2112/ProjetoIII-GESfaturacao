<?php

namespace App\Http\Controllers\clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RankingClientesController extends Controller
{
    private function clientes()
    {
        if (!session()->has('user.token')) {
            return null;
        }

        $token = session('user.token');
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices');

        if (!$response->successful()) {
            return null;
        }

        $invoices = $response->json();
        $data = $invoices['data'] ?? $invoices;

        $ranking = [];
        foreach ($data as $invoice) {
            if (empty($invoice['client']['id']) || empty($invoice['client']['name'])) {
                continue;
            }

            if (empty($invoice['status']['id']) || intval($invoice['status']['id']) !== 2) {
                continue;
            }

            $clientId = $invoice['client']['id'];
            $clientName = $invoice['client']['name'];
            $vatNumber = $invoice['client']['vatNumber'] ?? '';
            $total = (float)$invoice['total'];

            if (!isset($ranking[$clientId])) {
                $ranking[$clientId] = [
                    'id' => $clientId, 
                    'cliente' => $clientName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_vendas' => 0,
                ];
            }

            $ranking[$clientId]['total_euros'] += $total;
            $ranking[$clientId]['num_vendas'] += 1;
        }
        return collect($ranking);
    }


    public function topEuros()
    {
        $ranking = $this->clientes();
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5 = $ranking->sortByDesc('total_euros')->take(5)->values();

        $clientesNomes = $top5->pluck('cliente')->all();
        $clientesTotais = $top5->pluck('total_euros')->all();

        return view('clientes.topVendasEuros', [
            'top5ClientesEuros' => $top5,
            'clientesNomes' => $clientesNomes,
            'clientesTotais' => $clientesTotais,
        ]);
    }


    public function topVendas()
    {
        $ranking = $this->clientes();
        if ($ranking === null) {
            return redirect()->route('login');
        }
        $top5 = $ranking->sortByDesc('num_vendas')->take(5)->values();

        $clientesNomes = $top5->pluck('cliente')->all();
        $clientesVendas = $top5->pluck('num_vendas')->all();

        return view('clientes.topVendasQtd', [
            'top5ClientesVendas' => $top5,
            'clientesNomes' => $clientesNomes,
            'clientesVendas' => $clientesVendas,
        ]);
    }
}
