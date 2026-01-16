<?php

namespace App\Http\Controllers\relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PagamentosController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $recibosValidos = $this->buscarRecibosValidos($token, $inicio, $fim);
        if ($recibosValidos === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesRecibos = $this->buscarDetalhesRecibos($token, $recibosValidos);

        $resultadoCalculo = $this->calcularDadosRecibos($detalhesRecibos);

        $pagamentos = $resultadoCalculo['recibos']->sortBy('data')->values();
        $contagemMetodosPagamento = $resultadoCalculo['contagem_metodos_pagamento'];
        $contagemPagamentosPorDia = $resultadoCalculo['contagem_pagamentos_por_dia'];

        [$datasFormatadas, $datasYMD] = $this->formatarDatas($inicio, $fim);

        return view('relatorios.pagamentos', [
            'pagamentos' => $pagamentos,
            'contagemMetodosPagamento' => $contagemMetodosPagamento,
            'contagemPagamentosPorDia' => $contagemPagamentosPorDia,
            'datasFormatadas' => $datasFormatadas,
            'datasYMD' => $datasYMD,
            'periodoTexto' => $periodoTexto,
            'periodo' => $periodo,
            'inicio' => $inicio,
            'fim' => $fim,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $chartImg = $request->input('chart_img');
        $modo = $request->input('modo', 'evolucao');
        if (!in_array($modo, ['evolucao', 'top'])) {
            $modo = 'evolucao';
        }

        if (!$chartImg || !Str::startsWith($chartImg, 'data:image')) {
            return back()->withErrors(['error' => 'Não foi possível obter a imagem do gráfico para exportação.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $recibosValidos = $this->buscarRecibosValidos($token, $inicio, $fim);
        if ($recibosValidos === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesRecibos = $this->buscarDetalhesRecibos($token, $recibosValidos);
        $resultadoCalculo = $this->calcularDadosRecibos($detalhesRecibos);

        $pdf = Pdf::loadView('exports.pagamentos_pdf', [
            'titulo' => 'Relatório - Pagamentos',
            'chartImg' => $chartImg,
            'modo' => $modo,
            'modoTexto' => ($modo === 'top') ? 'Top' : 'Evolução',
            'periodoTexto' => $periodoTexto,
            'pagamentos' => $resultadoCalculo['recibos']->sortBy('data')->values(),
            'contagemMetodosPagamento' => $resultadoCalculo['contagem_metodos_pagamento'],
            'contagemPagamentosPorDia' => $resultadoCalculo['contagem_pagamentos_por_dia'],
            'geradoEm' => now(),
        ])->setPaper('a4', 'portrait');

        $nome = $modo === 'top' ? 'relatorio_pagamentos_top.pdf' : 'relatorio_pagamentos_evolucao.pdf';
        return $pdf->download($nome);
    }

    public function exportCsv(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $recibosValidos = $this->buscarRecibosValidos($token, $inicio, $fim);
        if ($recibosValidos === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesRecibos = $this->buscarDetalhesRecibos($token, $recibosValidos);
        $resultadoCalculo = $this->calcularDadosRecibos($detalhesRecibos);

        /** @var \Illuminate\Support\Collection $pagamentos */
        $pagamentos = $resultadoCalculo['recibos']->sortBy('data')->values();

        $filename = 'relatorio_pagamentos.csv';

        return response()->streamDownload(function () use ($pagamentos) {
            $out = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";

            fputcsv($out, ['Data', 'Número Recibo', 'Tipo de Pagamento', 'Preço c/IVA', 'Preço s/IVA'], ';');

            foreach ($pagamentos as $p) {
                fputcsv($out, [
                    $p['data'] ?? '',
                    $p['numero_recibo'] ?? '',
                    $p['metodo_pagamento'] ?? '',
                    number_format((float)($p['preco_com_iva'] ?? 0), 2, ',', '.'),
                    number_format((float)($p['preco_sem_iva'] ?? 0), 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
                if (!$inicio || !$fim) {
                    $inicio = $inicioSemanaAtual;
                    $fim = $hoje;
                    $periodo = 'semana';
                }
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
            $dataRecibo = substr($recibo['date'] ?? '', 0, 10);
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

            $todosRecibos = array_merge($todosRecibos, $response->json('data') ?? []);
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
                    $id = $recibo['id'] ?? null;
                    if (!$id) continue;

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
                }
            }
        });

        return $detalhes;
    }

    private function calcularDadosRecibos(Collection $detalhesFaturas): array
    {
        $contagemMetodos = [];
        $contagemPorDia = [];

        $recibos = $detalhesFaturas->map(function ($fatura) use (&$contagemMetodos, &$contagemPorDia) {
            $dataCompleta = $fatura['date'] ?? null;
            $dataYMD = $dataCompleta ? substr($dataCompleta, 0, 10) : null;

            $metodo = $fatura['paymentMethod']['name'] ?? 'Desconhecido';
            $numero = $fatura['number'] ?? '';
            $prefix = strtoupper(substr($numero, 0, 2));

            $precoComIva = 0.0;
            $precoSemIva = 0.0;

            if ($prefix === 'RG') {
                $precoComIva = (float)($fatura['total'] ?? 0);
                $precoSemIva = (float)($fatura['netTotal'] ?? 0);
            } elseif ($prefix === 'FR') {
                $precoComIva = (float)($fatura['grossTotal'] ?? 0);
                $precoSemIva = (float)($fatura['netTotal'] ?? 0);
            }

            $contagemMetodos[$metodo] = ($contagemMetodos[$metodo] ?? 0) + 1;
            if ($dataYMD) {
                $contagemPorDia[$dataYMD] = ($contagemPorDia[$dataYMD] ?? 0) + 1;
            }

            return [
                'data' => $dataYMD,
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
        $labels = [];
        $keys = [];

        foreach ($period as $data) {
            $labels[] = $data->format('d-m');
            $keys[] = $data->format('Y-m-d');
        }

        return [$labels, $keys];
    }
}
