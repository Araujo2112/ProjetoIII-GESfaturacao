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

            if (empty($invoice['status']['id']) || intval($invoice['status']['id']) !== 2) {
                continue;
            }

            $supplierId = $invoice['supplier']['id'];
            $supplierName = $invoice['supplier']['name'];
            $vatNumber = $invoice['supplier']['vatNumber'] ?? '';
            $total = (float)$invoice['total'];

            if (!isset($ranking[$supplierId])) {
                $ranking[$supplierId] = [
                    'fornecedor' => $supplierName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_compras' => 0,
                ];
            }

            $ranking[$supplierId]['total_euros'] += $total;
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

        $top5 = $ranking->sortByDesc('total_euros')->take(5)->values();

        $fornecedoresNomes = $top5->pluck('fornecedor')->all();
        $fornecedoresTotais = $top5->pluck('total_euros')->all();

        return view('fornecedores.topComprasEuros', [
            'top5Fornecedores' => $top5,
            'fornecedoresNomes' => $fornecedoresNomes,
            'fornecedoresTotais' => $fornecedoresTotais,
        ]);
    }


    public function topCompras()
    {
        $ranking = $this->fornecedores();
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5 = $ranking->sortByDesc('num_compras')->take(5)->values();

        $fornecedoresNomes = $top5->pluck('fornecedor')->all();
        $fornecedoresQtd = $top5->pluck('num_compras')->all();

        return view('fornecedores.topComprasQtd', [
            'top5Fornecedores' => $top5,
            'fornecedoresNomes' => $fornecedoresNomes,
            'fornecedoresQtd' => $fornecedoresQtd,
        ]);
    }
}
