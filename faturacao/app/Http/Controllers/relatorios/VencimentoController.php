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

        $faturasVencer = $this->buscarFaturasVencerProximos30Dias($token);
        if ($faturasVencer === false) {
            return redirect('/')->withErrors(['error' => 'Erro ao buscar faturas.']);
        }

        $contagemPorDia = $this->contarFaturasPorDia($faturasVencer);

        $hoje = Carbon::today()->format('Y-m-d');
        $limite = Carbon::today()->addDays(30)->format('Y-m-d');
        [$datasFormatadas, $datas] = $this->formatarDatas($hoje, $limite);

        $contagemNormalizada = [];
        foreach ($datas as $data) {
            $contagemNormalizada[$data] = $contagemPorDia[$data] ?? 0;
        }

        return view('relatorios.vencimento', [
            'faturasVencer'        => $faturasVencer,
            'contagemPorDia'       => $contagemPorDia,
            'datasFormatadas'      => $datasFormatadas,
            'datas'          => $datas,
            'contagemNormalizada'  => $contagemNormalizada,
        ]);
    }


    private function buscarFaturasVencerProximos30Dias(string $token): Collection|bool
    {
        $faturas = $this->listarTodasFaturasSemFiltro($token);
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


    private function contarFaturasPorDia(Collection $faturas): array
    {
        return $faturas
            ->groupBy(function ($fatura) {
                return substr($fatura['expiration'], 0, 10);
            })
            ->map(function ($grupo) {
                return $grupo->count();
            })
            ->toArray();
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


    private function listarTodasFaturasSemFiltro(string $token): Collection|bool
    {
        $endpoints = [
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices',
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/simplified-invoices',
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/receipt-invoices',
        ];

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
}
