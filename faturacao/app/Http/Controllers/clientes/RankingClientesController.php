<?php

namespace App\Http\Controllers\clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RankingClientesController extends Controller
{
    private function clientes($inicio = null, $fim = null)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        $validacao = $this->validateToken($token);

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices');


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

            $data_fatura = $invoice['date'] ?? null;
            if ($inicio && $fim && $data_fatura) {
                if ($data_fatura < $inicio || $data_fatura > $fim) continue;
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

    private function validateToken($token) {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);
        return $response->json();
    }

    public function topClientes(Request $request)
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

        switch($periodo) {
            case 'geral':
                $periodoTexto = "Todos os dados disponíveis";
                $inicio = null;
                $fim = null;
                break;
            case 'hoje':
                $inicio = $hoje; $fim = $hoje;
                $periodoTexto = "Hoje: $hoje";
                break;
            case 'ontem':
                $inicio = $ontem; $fim = $ontem;
                $periodoTexto = "Ontem: $ontem";
                break;
            case 'mes':
                $inicio = $primeiroDoMes; $fim = $hoje;
                $periodoTexto = "Mês: $primeiroDoMes a $hoje";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio; $fim = $ultimoMesFim;
                $periodoTexto = "Último Mês: $ultimoMesInicio a $ultimoMesFim";
                break;
            case 'ano':
                $inicio = $primeiroDoAno; $fim = $hoje;
                $periodoTexto = "Ano: $primeiroDoAno a $hoje";
                break;
            case 'ultimo_ano':
                $inicio = $ultimoAnoInicio; $fim = $ultimoAnoFim;
                $periodoTexto = "Último Ano: $ultimoAnoInicio a $ultimoAnoFim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
        }

        $ranking = $this->clientes($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5Vendas = $ranking->sortByDesc('num_vendas')->take(5)->values();
        $top5Euros = $ranking->sortByDesc('total_euros')->take(5)->values();

        return view('clientes.topClientes', [
            'top5ClientesVendas' => $top5Vendas,
            'top5ClientesEuros' => $top5Euros,
            'periodoTexto' => $periodoTexto,
        ]);
    }
}



