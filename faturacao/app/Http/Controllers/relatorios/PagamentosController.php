<?php

namespace App\Http\Controllers\relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PagamentosController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        list($inicio, $fim, $periodoTexto, $periodo) = $this->definirPeriodo($request);

        $recibosValidos = $this->buscarRecibosValidos($token, $inicio, $fim);

        if ($recibosValidos === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesRecibos = $this->buscarDetalhesRecibos($token, $recibosValidos);

        $resultadoCalculo = $this->calcularDadosRecibos($token, $detalhesRecibos);
        $vendasPorRecibo = $resultadoCalculo['recibos'];

        $contagemMetodosPagamento = $resultadoCalculo['contagem_metodos_pagamento'];
        $contagemPagamentosPorDia = $resultadoCalculo['contagem_pagamentos_por_dia'];

        $datasFormatadas = $this->formatarDatas($inicio, $fim);

        return view('relatorios.pagamentos', [
            'pagamentos' => $vendasPorRecibo,
            'contagemMetodosPagamento' => $contagemMetodosPagamento,
            'contagemPagamentosPorDia' => $contagemPagamentosPorDia,
            'datasFormatadas' => $datasFormatadas,
            'periodoTexto' => $periodoTexto,
            'periodo' => $periodo,
        ]);

    }

    private function definirPeriodo(Request $request)
    {
        $hoje = Carbon::today()->format('Y-m-d');
        $inicioSemanaAtual = Carbon::now()->startOfWeek()->format('Y-m-d');
        $fimSemanaAtual = Carbon::now()->endOfWeek()->format('Y-m-d');
        $inicioSemanaPassada = Carbon::now()->subWeek()->startOfWeek()->format('Y-m-d');
        $fimSemanaPassada = Carbon::now()->subWeek()->endOfWeek()->format('Y-m-d');
        $primeiroDoMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $ultimoMesInicio = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $ultimoMesFim = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $periodo = $request->input('periodo', 'semana');
        $inicio = null;
        $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'semana':
                $inicio = $inicioSemanaAtual;
                $fim = $hoje;
                $periodoTexto = "Semana atual: $inicio a $fim";
                break;
            case 'ultima_semana':
                $inicio = $inicioSemanaPassada;
                $fim = $fimSemanaPassada;
                $periodoTexto = "Semana anterior: $inicio a $fim";
                break;
            case 'mes':
                $inicio = $primeiroDoMes;
                $fim = $hoje;
                $periodoTexto = "Mês atual: $inicio a $fim";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio;
                $fim = $ultimoMesFim;
                $periodoTexto = "Mês anterior: $inicio a $fim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
        }

        return [$inicio, $fim, $periodoTexto, $periodo];
    }

    private function buscarRecibosValidos(string $token, string $inicio, string $fim): Collection|bool
    {
        $recibos = $this->listarTodosRecibosSemFiltro($token);
        if ($recibos === false) {
            return false;
        }

        return $recibos->filter(function ($recibo) use ($inicio, $fim) {
            $statusId = $recibo['status']['id'] ?? 0;
            if ($statusId == 5 || $statusId == 0) {
                return false;
            }
            $dataRecibo = substr($recibo['date'], 0, 10);
            return $dataRecibo >= $inicio && $dataRecibo <= $fim;
        });
    }

        private function listarTodosRecibosSemFiltro(string $token): Collection|bool
    {
        $endpoints = [
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/receipt-invoices',
            'https://api.gesfaturacao.pt/api/v1.0.4/sales/receipts',
        ];

        $todosRecibos = [];

        foreach ($endpoints as $endpoint) {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Accept' => 'application/json',
            ])->get($endpoint);

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            $recibos = $data['data'] ?? [];
            $todosRecibos = array_merge($todosRecibos, $recibos);
        }

        return collect($todosRecibos);
    }

    private function buscarDetalhesRecibos(string $token, Collection $recibosValidos): Collection
    {
        $detalhes = collect();
        $batchSize = 50;

        $recibosValidos->chunk($batchSize)->each(function ($lote) use ($token, &$detalhes) {
            $responses = Http::pool(function ($pool) use ($lote, $token) {
                foreach ($lote as $recibo) {
                    $id = $recibo['id'];
                    $number = $recibo['number'] ?? '';
                    $prefix = strtoupper(substr($number, 0, 2));

                    $tipo = match ($prefix) {
                        'FR' => 'receipt-invoices',
                        'RG' => 'receipts',
                        default => null,
                    };

                    if ($tipo) {
                        $url = "https://api.gesfaturacao.pt/api/v1.0.4/sales/{$tipo}/{$id}";
                        $pool->withHeaders([
                            'Authorization' => $token,
                            'Accept' => 'application/json',
                        ])->get($url);
                    }
                }
            });

            foreach ($responses as $response) {
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $detalhes->push($response->json('data'));
                } elseif ($response instanceof \Exception) {
                    \Log::error('Erro na requisição: Exceção - ' . $response->getMessage());
                } else {
                    \Log::error('Erro na requisição: ' . (method_exists($response, 'status') ? $response->status() : 'Desconhecido') . ' - ' . (method_exists($response, 'body') ? $response->body() : ''));
                }
            }
        });

        return $detalhes;
    }


    private function calcularDadosRecibos(string $token, Collection $detalhesFaturas): array
    {
        $contagemMetodos = [];
        $contagemPorDia = [];

        $recibos = $detalhesFaturas->map(function ($fatura) use (&$contagemMetodos, &$contagemPorDia) {
            $data = $fatura['date'] ?? null;
            $metodo = $fatura['paymentMethod']['name'] ?? 'Desconhecido';
            $numero = $fatura['number'] ?? '';
            $prefix = strtoupper(substr($numero, 0, 2));

            $precoComIva = 0;
            $precoSemIva = 0;

            if ($prefix === 'RG') {
                $precoComIva = floatval($fatura['total'] ?? 0);
                $precoSemIva = floatval($fatura['netTotal'] ?? 0);
            } elseif ($prefix === 'FR') {
                $precoComIva = floatval($fatura['grossTotal'] ?? 0);
                $precoSemIva = floatval($fatura['netTotal'] ?? 0);
            }

            $contagemMetodos[$metodo] = ($contagemMetodos[$metodo] ?? 0) + 1;
            $contagemPorDia[$data] = ($contagemPorDia[$data] ?? 0) + 1;

            return [
                'data' => $data,
                'metodo_pagamento' => $metodo,
                'numero_recibo' => $numero,
                'preco_com_iva' => $precoComIva,
                'preco_sem_iva' => $precoSemIva,
            ];
        });

        return [
            'recibos' => $recibos,
            'contagem_metodos_pagamento' => $contagemMetodos,
            'contagem_pagamentos_por_dia' => $contagemPorDia,
        ];
    }



    private function formatarDatas(string $inicio, string $fim): array
    {
        $period = CarbonPeriod::create($inicio, $fim);
        $datas = [];
        foreach ($period as $data) {
            $datas[] = $data->format('d-m');
        }
        return $datas;
    }
}
