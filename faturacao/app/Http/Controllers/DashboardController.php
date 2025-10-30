<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $token = session('user.token');

        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $validacao = $this->validateToken($token);
        // dd($validacao);

        $faturadoAno = $this->faturacaoAno($token);
        return view('dashboard', compact('faturadoAno'));
    }

    private function validateToken($token)
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->json();
    }


    private function faturacaoAno($token)
    {
        $dateFrom = date('Y-m-01');
        $dateTo = date('Y-m-t');

        $faturas = $this->listarTodasFaturas($token, $dateFrom, $dateTo);

        $totalFaturado = 0;
        if ($faturas) {
            foreach ($faturas as $fatura) {
                $totalFaturado += floatval($fatura['total'] ?? 0);
            }
        }

        return number_format($totalFaturado, 2, ',', '.');
    }


    private function listarTodasFaturas($token, $dateFrom, $dateTo)
    {
        $page = 1;
        $todasFaturas = [];

        do {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Accept' => 'application/json',
            ])->get('https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices', [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'page' => $page,
            ]);

            if (!$response->successful()) {
                break;
            }

            $data = $response->json();

            $faturas = $data['data'] ?? [];
            $todasFaturas = array_merge($todasFaturas, $faturas);

            $lastPage = $data['pagination']['lastPage'] ?? 1;
            $page++;
        } while ($page <= $lastPage);

        return $todasFaturas;
    }
}
