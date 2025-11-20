<?php

namespace App\Http\Controllers\fornecedores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RankingFornecedoresController extends Controller
{
    private function fornecedores($inicio = null, $fim = null)
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

            $data_fatura = $invoice['date'] ?? null;
            if ($inicio && $fim && $data_fatura) {
                if ($data_fatura < $inicio || $data_fatura > $fim) continue;
            }

            $supplierId = $invoice['supplier']['id'];
            $supplierName = $invoice['supplier']['name'];
            $vatNumber = $invoice['supplier']['vatNumber'] ?? '';
            $total = (float)$invoice['total'];

            if (!isset($ranking[$supplierId])) {
                $ranking[$supplierId] = [
                    'id' => $supplierId,
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

    public function topFornecedores(Request $request)
    {
        $hoje = Carbon::today()->format('Y-m-d');
        $ontem = Carbon::yesterday()->format('Y-m-d');
        $primeiroDoMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $ultimoMesInicio = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $ultimoMesFim = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $primeiroDoAno = Carbon::now()->startOfYear()->format('Y-m-d');
        $ultimoAnoInicio = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
        $ultimoAnoFim = Carbon::now()->subYear()->endOfYear()->format('Y-m-d');

        $periodo = $request->input('periodo', 'geral');
        $inicio = $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'geral':
                $periodoTexto = "Todos os dados disponíveis";
                $inicio = null; $fim = null; break;
            case 'hoje':
                $inicio = $hoje; $fim = $hoje; $periodoTexto = "Hoje: $hoje"; break;
            case 'ontem':
                $inicio = $ontem; $fim = $ontem; $periodoTexto = "Ontem: $ontem"; break;
            case 'mes':
                $inicio = $primeiroDoMes; $fim = $hoje; $periodoTexto = "Mês: $primeiroDoMes a $hoje"; break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio; $fim = $ultimoMesFim; $periodoTexto = "Último Mês: $ultimoMesInicio a $ultimoMesFim"; break;
            case 'ano':
                $inicio = $primeiroDoAno; $fim = $hoje; $periodoTexto = "Ano: $primeiroDoAno a $hoje"; break;
            case 'ultimo_ano':
                $inicio = $ultimoAnoInicio; $fim = $ultimoAnoFim; $periodoTexto = "Último Ano: $ultimoAnoInicio a $ultimoAnoFim"; break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
        }

        $ranking = $this->fornecedores($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5Qtd = $ranking->sortByDesc('num_compras')->take(5)->values();
        $top5Euros = $ranking->sortByDesc('total_euros')->take(5)->values();

        return view('fornecedores.topFornecedores', [
            'top5FornecedoresQtd' => $top5Qtd,
            'top5FornecedoresEuros' => $top5Euros,
            'periodoTexto' => $periodoTexto,
        ]);
    }
}
