<?php

namespace App\Http\Controllers\relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VencimentoController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $faturasVencer = $this->buscarFaturasVencerProximos30Dias($token, 'sales');
        if ($faturasVencer === false) {
            return redirect('/')->withErrors(['error' => 'Error fetching sales invoices.']);
        }

        $faturasVencerCompras = $this->buscarFaturasVencerProximos30Dias($token, 'purchases');
        if ($faturasVencerCompras === false) {
            return redirect('/')->withErrors(['error' => 'Error fetching purchase invoices.']);
        }

        $faturasVencer = $faturasVencer->sortBy('expiration')->values();
        $faturasVencerCompras = $faturasVencerCompras->sortBy('expiration')->values();

        $hoje = Carbon::today()->format('Y-m-d');
        $limite = Carbon::today()->addDays(30)->format('Y-m-d');
        [$datasFormatadas, $datas] = $this->formatarDatas($hoje, $limite);

        $vendasMonetariasNormalizadas = $this->calcularTotaisMonetarios($faturasVencer, $datas);
        $comprasMonetariasNormalizadas = $this->calcularTotaisMonetarios($faturasVencerCompras, $datas);

        return view('relatorios.vencimentos', [
            'dados' => [
                'vendas' => [
                    'faturas' => $faturasVencer,
                    'monetario' => $vendasMonetariasNormalizadas,
                ],
                'compras' => [
                    'faturas' => $faturasVencerCompras,
                    'monetario' => $comprasMonetariasNormalizadas,
                ],
                'datas' => $datasFormatadas,
                'datasRaw' => $datas,
            ]
        ]);
    }

    private function buscarFaturasVencerProximos30Dias(string $token, string $tipo): Collection|bool
    {
        $faturas = $this->listarFaturasPorTipo($token, $tipo);
        if ($faturas === false) {
            return false;
        }

        $hoje = Carbon::today();
        $limite = Carbon::today()->addDays(30);

        return $faturas->filter(function ($fatura) use ($hoje, $limite) {
            $statusId = $fatura['status']['id'] ?? 0;
            if ($statusId != 1) {
                return false;
            }

            if (empty($fatura['expiration'])) {
                return false;
            }

            $expiration = Carbon::parse($fatura['expiration']);

            return $expiration->betweenIncluded($hoje, $limite);
        });
    }

    private function listarFaturasPorTipo(string $token, string $tipo): Collection|bool
    {
        $endpoints = match($tipo) {
            'sales' => [
                'https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices',
                'https://api.gesfaturacao.pt/api/v1.0.4/sales/simplified-invoices',
                'https://api.gesfaturacao.pt/api/v1.0.4/sales/receipt-invoices',
            ],
            'purchases' => [
                'https://api.gesfaturacao.pt/api/v1.0.4/purchases/invoices',
            ],
            default => []
        };

        $todasFaturas = [];

        foreach ($endpoints as $endpoint) {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Accept' => 'application/json',
            ])->get($endpoint);

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            $faturas = $data['data'] ?? [];
            $todasFaturas = array_merge($todasFaturas, $faturas);
        }

        return collect($todasFaturas);
    }

    private function calcularTotaisMonetarios(Collection $faturas, array $datas): array
    {
        $totais = $faturas
            ->groupBy(function ($fatura) {
                return substr($fatura['expiration'], 0, 10);
            })
            ->map(function ($grupo) {
                return abs($grupo->sum('balance'));
            })
            ->toArray();

        return $this->normalizarContagem($totais, $datas);
    }

    private function normalizarContagem(array $contagem, array $datas): array
    {
        $contagemNormalizada = [];
        foreach ($datas as $data) {
            $contagemNormalizada[$data] = $contagem[$data] ?? 0;
        }
        return $contagemNormalizada;
    }

    private function formatarDatas(string $inicio, string $fim): array
    {
        $period = CarbonPeriod::create($inicio, $fim);
        $datasFormatadas = [];
        $datas = [];

        foreach ($period as $data) {
            $datasFormatadas[] = $data->format('d-m');
            $datas[] = $data->format('Y-m-d');
        }

        return [$datasFormatadas, $datas];
    }
}
