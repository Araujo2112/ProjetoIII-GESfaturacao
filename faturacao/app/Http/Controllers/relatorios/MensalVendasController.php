<?php

namespace App\Http\Controllers\relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MensalVendasController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        list($inicio, $fim, $periodoTexto, $periodo) = $this->definirPeriodo($request);

        $faturasValidas = $this->buscarFaturasValidas($token, $inicio, $fim);

        if ($faturasValidas === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesFaturas = $this->buscarDetalhesFaturas($token, $faturasValidas);

        $vendasPorMes = $this->calcularVendasPorMes($token, $detalhesFaturas);

        $totais = $this->calcularTotais($vendasPorMes);

        $ano = Carbon::parse($inicio)->year;

        $mesesFormatados = $this->formatarMesesAno($ano);
        $valoresPorMes   = $this->extrairValoresAno($vendasPorMes, $ano, 'vendas_com_iva');
        $lucroPorMes     = $this->extrairValoresAno($vendasPorMes, $ano, 'lucro');

        return view('relatorios.mensalVendas', [
            'vendasPorMes' => $vendasPorMes,
            'mesesFormatados' => $mesesFormatados,
            'valoresPorMes' => $valoresPorMes,
            'lucroPorMes' => $lucroPorMes,
            'periodoTexto' => $periodoTexto,
            'periodo' => $periodo,
            'inicio' => $inicio,
            'fim' => $fim,
            'total_vendas_iva' => $totais['total_vendas_iva'],
            'total_vendas' => $totais['total_vendas'],
            'total_custos' => $totais['total_custos'],
            'total_lucro' => $totais['total_lucro'],
            'total_quantidade' => $totais['total_quantidade'],
            'total_num_vendas' => $totais['total_num_vendas'],
        ]);
    }

    private function calcularTotais(Collection $vendasPorMes): array
    {
        $total_vendas_iva = 0;
        $total_vendas = 0;
        $total_custos = 0;
        $total_lucro = 0;
        $total_quantidade = 0;
        $total_num_vendas = 0;

        foreach ($vendasPorMes as $dados) {
            $total_vendas_iva += $dados['vendas_com_iva'] ?? 0;
            $total_vendas += $dados['vendas_sem_iva'] ?? 0;
            $total_custos += $dados['custos'] ?? 0;
            $total_lucro += $dados['lucro'] ?? 0;
            $total_quantidade += $dados['quantidade'] ?? 0;
            $total_num_vendas += $dados['num_vendas'] ?? 0;
        }

        return [
            'total_vendas_iva' => $total_vendas_iva,
            'total_vendas' => $total_vendas,
            'total_custos' => $total_custos,
            'total_lucro' => $total_lucro,
            'total_quantidade' => $total_quantidade,
            'total_num_vendas' => $total_num_vendas,
        ];
    }

    private function definirPeriodo(Request $request)
    {
        $anoAtual = Carbon::now()->year;
        $anoAnterior = $anoAtual - 1;

        $periodo = $request->input('periodo', 'ano_atual');
        $inicio = null;
        $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'ano_atual':
                $inicio = Carbon::create($anoAtual, 1, 1)->format('Y-m-d');
                $fim    = Carbon::create($anoAtual, 12, 31)->format('Y-m-d');
                $periodoTexto = "Ano atual: $anoAtual";
                break;
            case 'ano_anterior':
                $inicio = Carbon::create($anoAnterior, 1, 1)->format('Y-m-d');
                $fim    = Carbon::create($anoAnterior, 12, 31)->format('Y-m-d');
                $periodoTexto = "Ano anterior: $anoAnterior";
                break;
        }

        return [$inicio, $fim, $periodoTexto, $periodo];
    }


    private function buscarFaturasValidas(string $token, string $inicio, string $fim): Collection|bool
    {
        $faturas = $this->listarTodasFaturasSemFiltro($token);
        if ($faturas === false) {
            return false;
        }

        return $faturas->filter(function ($fatura) use ($inicio, $fim) {
            $statusId = $fatura['status']['id'] ?? 0;
            if ($statusId == 5 || $statusId == 0) {
                return false;
            }
            $dataFatura = substr($fatura['date'], 0, 10);
            return $dataFatura >= $inicio && $dataFatura <= $fim;
        });
    }

    private function buscarDetalhesFaturas(string $token, Collection $faturasValidas): Collection
    {
        $detalhes = collect();
        $batchSize = 50;

        $faturasValidas->chunk($batchSize)->each(function ($lote) use ($token, &$detalhes) {
            $responses = Http::pool(function ($pool) use ($lote, $token) {
                foreach ($lote as $fatura) {
                    $id = $fatura['id'];
                    $number = $fatura['number'] ?? '';
                    $prefix = strtoupper(substr($number, 0, 2));

                    $tipo = match ($prefix) {
                        'FT' => 'invoices',
                        'FS' => 'simplified-invoices',
                        'FR' => 'receipt-invoices',
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

    private function calcularVendasPorMes(string $token, Collection $detalhesFaturas): Collection
    {
        $produtosCache = [];

        return $detalhesFaturas->groupBy(fn ($fatura) => substr($fatura['date'], 0, 7)) // Y-m
            ->map(function ($faturasMes, $mes) use ($token, &$produtosCache) {
                $vendasIVA = $faturasMes->sum(fn($f) => floatval($f['grossTotal'] ?? 0));
                $vendas = $faturasMes->sum(fn($f) => floatval($f['netTotal'] ?? 0));
                $custos = 0;
                $numVendas = $faturasMes->count();
                $quantidade = 0;

                foreach ($faturasMes as $fatura) {
                    foreach ($fatura['lines'] ?? [] as $line) {
                        $quantLine = floatval($line['quantity'] ?? 0);
                        $quantidade += $quantLine;

                        $prodId = $line['article']['id'] ?? null;
                        if ($prodId) {
                            if (!isset($produtosCache[$prodId])) {
                                $response = Http::withHeaders([
                                    'Authorization' => $token,
                                    'Accept' => 'application/json',
                                ])->get("https://api.gesfaturacao.pt/api/v1.0.4/products/{$prodId}");

                                if ($response->successful()) {
                                    $produto = $response->json();
                                    $produtosCache[$prodId] = floatval($produto['data']['initialPrice'] ?? 0);
                                } else {
                                    $produtosCache[$prodId] = 0;
                                }
                            }
                            $custos += $produtosCache[$prodId] * $quantLine;
                        }
                    }
                }

                $lucro = $vendas - $custos;

                return [
                    'mes' => $mes,
                    'vendas_com_iva' => $vendasIVA,
                    'vendas_sem_iva' => $vendas,
                    'custos' => $custos,
                    'lucro' => $lucro,
                    'quantidade' => $quantidade,
                    'num_vendas' => $numVendas,
                ];
            })->sortKeys();
    }

    private function formatarMesesAno(int $ano): array
    {
        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $meses[] = Carbon::create($ano, $m, 1)->translatedFormat('F'); // “janeiro”, “fevereiro”, ...
        }
        return $meses;
    }

    private function extrairValoresAno(Collection $vendasPorMes, int $ano, string $campo): array
    {
        $dados = $vendasPorMes->toArray(); // chave Y-m
        $valores = [];

        for ($m = 1; $m <= 12; $m++) {
            $chave = sprintf('%d-%02d', $ano, $m);
            $valores[] = isset($dados[$chave]) ? round($dados[$chave][$campo] ?? 0, 2) : 0;
        }

        return $valores;
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
