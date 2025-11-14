<?php

namespace App\Http\Controllers\fornecedores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RankingFornecedoresController extends Controller
{
    private function fornecedores()
    {
        if (!session()->has('user.token')) {
            return null;
        }

        $token = session('user.token');
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/purchases/invoices');

        if (!$response->successful()) {
            return null;
        }

        $invoices = $response->json();
        $data = $invoices['data'] ?? $invoices;

        $ranking = [];
        foreach ($data as $invoice) {
            if (empty($invoice['supplier']['id']) || empty($invoice['supplier']['name'])) {
                continue;
            }
            $supplierId = $invoice['supplier']['id'];
            $supplierName = $invoice['supplier']['name'];
            $vatNumber = $invoice['supplier']['vatNumber'] ?? '';
            $total = (float)$invoice['total'];
            $isPaid = (
                (isset($invoice['status']['name']) && strtolower($invoice['status']['name']) == 'pago')
                || (isset($invoice['balance']) && ((float)$invoice['balance'] == 0))
            );

            if (!isset($ranking[$supplierId])) {
                $ranking[$supplierId] = [
                    'fornecedor' => $supplierName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_compras' => 0,
                ];
            }

            if ($isPaid) {
                $ranking[$supplierId]['total_euros'] += $total;
            } else {
                $ranking[$supplierId]['total_euros'] -= $total;
            }
            $ranking[$supplierId]['num_compras'] += 1;
        }
        return collect($ranking);
    }

    public function topEuros()
    {
        $ranking = $this->fornecedores();
        if ($ranking === null) {
            return redirect()->route('login');
        }
        $top5 = $ranking->sortByDesc('total_euros')->take(5);
        return view('fornecedores.topComprasEuros', [
            'top5FornecedoresEuros' => $top5
        ]);
    }

    public function topCompras()
    {
        $ranking = $this->fornecedores();
        if ($ranking === null) {
            return redirect()->route('login');
        }
        $top5 = $ranking->sortByDesc('num_compras')->take(5);
        return view('fornecedores.topComprasQtd', [
            'top5FornecedoresQtd' => $top5
        ]);
    }
}
