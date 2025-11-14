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
            $clientId = $invoice['client']['id'];
            $clientName = $invoice['client']['name'];
            $vatNumber = $invoice['client']['vatNumber'] ?? '';
            $total = (float)$invoice['total'];
            $isPaid = (
                (isset($invoice['status']['name']) && strtolower($invoice['status']['name']) == 'pago')
                || (isset($invoice['balance']) && ((float)$invoice['balance'] == 0))
            );

            if (!isset($ranking[$clientId])) {
                $ranking[$clientId] = [
                    'id' => $clientId, 
                    'cliente' => $clientName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_vendas' => 0,
                ];
            }

            if ($isPaid) {
                $ranking[$clientId]['total_euros'] += $total;
            } else {
                $ranking[$clientId]['total_euros'] -= $total;
            }
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
        $top5 = $ranking->sortByDesc('total_euros')->take(5);
        return view('clientes.topVendasEuros', [
            'top5ClientesEuros' => $top5
        ]);
    }

    public function topVendas()
    {
        $ranking = $this->clientes();
        if ($ranking === null) {
            return redirect()->route('login');
        }
        $top5 = $ranking->sortByDesc('num_vendas')->take(5);
        return view('clientes.topVendasQtd', [
            'top5ClientesVendas' => $top5
        ]);
    }
}
