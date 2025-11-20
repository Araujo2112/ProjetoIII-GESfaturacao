<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $validacao = $this->validateToken($token);

        $faturasCollection = $this->listarTodasFaturasSemFiltro($token);
        if ($faturasCollection === false) {
            abort(500, 'Erro ao obter faturas da API');
        }

        $faturasValidas = $faturasCollection->filter(function ($fatura) {
            $statusId = $fatura['status']['id'] ?? 0;
            return $statusId != 5 && $statusId != 0;
        });

        $faturadoHojeValor = $this->calculaFaturacaoDia($faturasValidas);
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $faturadoOntemValor = $this->calculaFaturacaoDia(
            $faturasValidas,
            date('d', strtotime($ontem)),
            date('m', strtotime($ontem)),
            date('Y', strtotime($ontem))
        );

        $faturadoMesValor = $this->calculaFaturacaoMes($faturasValidas);
        $mesAnterior = date('Y-m', strtotime('first day of last month'));
        [$anoAnteriorMes, $mesAnteriorNum] = explode('-', $mesAnterior);
        $faturadoMesAnteriorValor = $this->calculaFaturacaoMes($faturasValidas, intval($mesAnteriorNum), intval($anoAnteriorMes));

        $anoAtual = date('Y');
        $anoAnterior = $anoAtual - 1;
        $faturadoAnoValor = $this->calculaFaturacaoAno($faturasValidas);
        $faturadoAnoAnteriorValor = $this->calculaFaturacaoAno($faturasValidas, $anoAnterior);

        $variacaoHoje = $this->calcularPercentualRelativo($faturadoHojeValor, $faturadoOntemValor);
        $variacaoMes = $this->calcularPercentualRelativo($faturadoMesValor, $faturadoMesAnteriorValor);
        $variacaoAno = $this->calcularPercentualRelativo($faturadoAnoValor, $faturadoAnoAnteriorValor);

        $datasFormatadas = [];
        $totaisPorDia = [];
        for ($i = 29; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $dataFormatada = $data->format('d-m');

            $totalDia = $faturasValidas->filter(function ($fatura) use ($data) {
                return substr($fatura['date'], 0, 10) === $data->format('Y-m-d');
            })->sum(function ($fatura) {
                return floatval($fatura['total'] ?? 0);
            });

            $datasFormatadas[] = $dataFormatada;
            $totaisPorDia[] = round($totalDia, 2);
        }

        return view('dashboard', [
            'faturadoHoje' => number_format($faturadoHojeValor, 2, ',', '.'),
            'faturadoOntem' => number_format($faturadoOntemValor, 2, ',', '.'),
            'variacaoHoje' => $variacaoHoje,

            'faturadoMes' => number_format($faturadoMesValor, 2, ',', '.'),
            'faturadoMesAnterior' => number_format($faturadoMesAnteriorValor, 2, ',', '.'),
            'variacaoMes' => $variacaoMes,

            'faturadoAno' => number_format($faturadoAnoValor, 2, ',', '.'),
            'faturadoAnoAnterior' => number_format($faturadoAnoAnteriorValor, 2, ',', '.'),
            'variacaoAno' => $variacaoAno,

            'graficoDatas' => $datasFormatadas,
            'graficoTotais' => $totaisPorDia,
        ]);
    }



    private function calcularPercentualRelativo(float $atual, float $anterior): array
    {
        if ($anterior == 0 || $atual == 0) {
            return ['percent' => '0,00%', 'seta' => '→', 'positivo' => true];
        }

        $diff = $atual - $anterior;
        $percent = ($diff / abs($anterior)) * 100;
        $positivo = $percent >= 0;

        $seta = $positivo ? '↑' : '↓';
        $percentFormatado = number_format($percent, 2, ',', '.') . '%';

        return ['percent' => $percentFormatado, 'seta' => $seta, 'positivo' => $positivo];
    }


    private function validateToken($token)
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->json();
    }

    private function listarTodasFaturasSemFiltro($token)
    {
        $endpoints = [
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices',
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/simplified-invoices',
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/receipt-invoices',
        ];

        $todasFaturas = [];

        foreach ($endpoints as $endpoint) {
            $page = 1;
            do {
                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'Accept' => 'application/json',
                ])->get($endpoint, [
                    'page' => $page,
                ]);

                if (!$response->successful()) {
                    \Log::error('Erro na API: ' . $response->body());
                    return false;
                }

                $data = $response->json();
                $faturas = $data['data'] ?? [];
                $todasFaturas = array_merge($todasFaturas, $faturas);

                $lastPage = $data['pagination']['lastPage'] ?? 1;
                $page++;
            } while ($page <= $lastPage);
        }

        return collect($todasFaturas);
    }

    private function calculaFaturacaoDia(Collection $faturasValidas, $dia = null, $mes = null, $ano = null): float
    {
        $ano = $ano ?? date('Y');
        $mes = $mes ?? date('m');
        $dia = $dia ?? date('d');

        $dateStr = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);

        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 10) === $dateStr)
                             ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }

    private function calculaFaturacaoMes(Collection $faturasValidas, $mes = null, $ano = null): float
    {
        $ano = $ano ?? date('Y');
        $mes = $mes ?? date('m');
        $prefixoData = sprintf('%04d-%02d', $ano, $mes);

        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 7) === $prefixoData)
                             ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }

    private function calculaFaturacaoAno(Collection $faturasValidas, $ano = null): float
    {
        $ano = $ano ?? date('Y');
        $prefixoData = sprintf('%04d', $ano);

        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 4) === $prefixoData)
                             ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }
}
