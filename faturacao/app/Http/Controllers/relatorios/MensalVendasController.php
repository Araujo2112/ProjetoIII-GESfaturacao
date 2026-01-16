<?php

namespace App\Http\Controllers\relatorios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class MensalVendasController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

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

    public function exportPdf(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $chartImg = $request->input('chart_img');
        $modo = $request->input('modo', 'lucro');
        if (!in_array($modo, ['lucro', 'vendas'])) {
            $modo = 'lucro';
        }

        if (!$chartImg || !Str::startsWith($chartImg, 'data:image')) {
            return back()->withErrors(['error' => 'Não foi possível obter a imagem do gráfico para exportação.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $faturasValidas = $this->buscarFaturasValidas($token, $inicio, $fim);
        if ($faturasValidas === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesFaturas = $this->buscarDetalhesFaturas($token, $faturasValidas);
        $vendasPorMes = $this->calcularVendasPorMes($token, $detalhesFaturas);
        $totais = $this->calcularTotais($vendasPorMes);

        $pdf = Pdf::loadView('exports.mensal_vendas_pdf', [
            'titulo' => 'Relatório - Mensal',
            'chartImg' => $chartImg,
            'modo' => $modo,
            'modoTexto' => ($modo === 'vendas') ? 'Vendas' : 'Lucro',
            'periodoTexto' => $periodoTexto,
            'vendasPorMes' => $vendasPorMes,
            'totais' => $totais,
            'geradoEm' => now(),
        ])->setPaper('a4', 'portrait');

        $nome = $modo === 'vendas' ? 'relatorio_mensal_vendas.pdf' : 'relatorio_mensal_lucro.pdf';
        return $pdf->download($nome);
    }

    public function exportCsv(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $faturasValidas = $this->buscarFaturasValidas($token, $inicio, $fim);
        if ($faturasValidas === false) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $detalhesFaturas = $this->buscarDetalhesFaturas($token, $faturasValidas);
        $vendasPorMes = $this->calcularVendasPorMes($token, $detalhesFaturas);
        $totais = $this->calcularTotais($vendasPorMes);

        $filename = 'relatorio_mensal.csv';

        return response()->streamDownload(function () use ($vendasPorMes, $totais) {
            $out = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";

            fputcsv($out, ['Mês', 'Vendas c/IVA', 'Vendas s/IVA', 'Custos', 'Lucro', 'Quantidade', 'Nº Vendas'], ';');

            foreach ($vendasPorMes as $dados) {
                fputcsv($out, [
                    $dados['mes'] ?? '',
                    number_format((float)($dados['vendas_com_iva'] ?? 0), 2, ',', '.'),
                    number_format((float)($dados['vendas_sem_iva'] ?? 0), 2, ',', '.'),
                    number_format((float)($dados['custos'] ?? 0), 2, ',', '.'),
                    number_format((float)($dados['lucro'] ?? 0), 2, ',', '.'),
                    (string)($dados['quantidade'] ?? 0),
                    (string)($dados['num_vendas'] ?? 0),
                ], ';');
            }

            fputcsv($out, [
                'TOTAL',
                number_format((float)($totais['total_vendas_iva'] ?? 0), 2, ',', '.'),
                number_format((float)($totais['total_vendas'] ?? 0), 2, ',', '.'),
                number_format((float)($totais['total_custos'] ?? 0), 2, ',', '.'),
                number_format((float)($totais['total_lucro'] ?? 0), 2, ',', '.'),
                (string)($totais['total_quantidade'] ?? 0),
                (string)($totais['total_num_vendas'] ?? 0),
            ], ';');

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ===== helpers (iguais aos teus) =====

    private function calcularTotais(Collection $vendasPorMes): array
    {
        $totais = [
            'total_vendas_iva' => 0,
            'total_vendas' => 0,
            'total_custos' => 0,
            'total_lucro' => 0,
            'total_quantidade' => 0,
            'total_num_vendas' => 0,
        ];

        foreach ($vendasPorMes as $dados) {
            $totais['total_vendas_iva'] += $dados['vendas_com_iva'] ?? 0;
            $totais['total_vendas']     += $dados['vendas_sem_iva'] ?? 0;
            $totais['total_custos']     += $dados['custos'] ?? 0;
            $totais['total_lucro']      += $dados['lucro'] ?? 0;
            $totais['total_quantidade'] += $dados['quantidade'] ?? 0;
            $totais['total_num_vendas'] += $dados['num_vendas'] ?? 0;
        }

        return $totais;
    }

    private function definirPeriodo(Request $request): array
    {
        $anoAtual = Carbon::now()->year;
        $anoAnterior = $anoAtual - 1;

        $periodo = $request->input('periodo', 'ano_atual');

        switch ($periodo) {
            case 'ano_anterior':
                $inicio = Carbon::create($anoAnterior, 1, 1)->format('Y-m-d');
                $fim    = Carbon::create($anoAnterior, 12, 31)->format('Y-m-d');
                $periodoTexto = "Ano anterior: $anoAnterior";
                break;

            case 'ano_atual':
            default:
                $inicio = Carbon::create($anoAtual, 1, 1)->format('Y-m-d');
                $fim    = Carbon::create($anoAtual, 12, 31)->format('Y-m-d');
                $periodoTexto = "Ano atual: $anoAtual";
                $periodo = 'ano_atual';
                break;
        }

        return [$inicio, $fim, $periodoTexto, $periodo];
    }

    private function buscarFaturasValidas(string $token, string $inicio, string $fim): Collection|bool
    {
        $faturas = $this->listarTodasFaturasSemFiltro($token);
        if ($faturas === false) return false;

        return $faturas->filter(function ($fatura) use ($inicio, $fim) {
            $statusId = $fatura['status']['id'] ?? 0;
            if ($statusId == 5 || $statusId == 0) return false;

            $dataFatura = substr($fatura['date'] ?? '', 0, 10);
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
                    $id = $fatura['id'] ?? null;
                    if (!$id) continue;

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
                if ($response instanceof \Illuminate\Http\Client\Response) {
                    if ($response->successful()) {
                        $detalhes->push($response->json('data'));
                    } else {
                        Log::warning('MensalVendasController: detalhe fatura falhou', [
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ]);
                    }
                } elseif ($response instanceof \Exception) {
                    Log::error('MensalVendasController: exceção ao obter detalhe fatura', [
                        'msg' => $response->getMessage(),
                    ]);
                }
            }
        });

        return $detalhes;
    }

    private function calcularVendasPorMes(string $token, Collection $detalhesFaturas): Collection
    {
        $produtosCache = [];

        return $detalhesFaturas
            ->groupBy(fn ($fatura) => substr($fatura['date'] ?? '', 0, 7)) // Y-m
            ->map(function ($faturasMes, $mes) use ($token, &$produtosCache) {
                $vendasIVA = $faturasMes->sum(fn ($f) => (float)($f['grossTotal'] ?? 0));
                $vendas    = $faturasMes->sum(fn ($f) => (float)($f['netTotal'] ?? 0));

                $custos = 0.0;
                $numVendas = $faturasMes->count();
                $quantidade = 0.0;

                foreach ($faturasMes as $fatura) {
                    foreach ($fatura['lines'] ?? [] as $line) {
                        $quantLine = (float)($line['quantity'] ?? 0);
                        $quantidade += $quantLine;

                        $prodId = $line['article']['id'] ?? null;
                        if (!$prodId) continue;

                        if (!array_key_exists($prodId, $produtosCache)) {
                            $resp = Http::withHeaders([
                                'Authorization' => $token,
                                'Accept' => 'application/json',
                            ])->get("https://api.gesfaturacao.pt/api/v1.0.4/products/{$prodId}");

                            $produtosCache[$prodId] = $resp->successful()
                                ? (float)($resp->json('data.initialPrice') ?? 0)
                                : 0.0;
                        }

                        $custos += $produtosCache[$prodId] * $quantLine;
                    }
                }

                return [
                    'mes' => $mes,
                    'vendas_com_iva' => $vendasIVA,
                    'vendas_sem_iva' => $vendas,
                    'custos' => $custos,
                    'lucro' => $vendas - $custos,
                    'quantidade' => $quantidade,
                    'num_vendas' => $numVendas,
                ];
            })
            ->sortKeys();
    }

    private function formatarMesesAno(int $ano): array
    {
        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $meses[] = Carbon::create($ano, $m, 1)->translatedFormat('F');
        }
        return $meses;
    }

    private function extrairValoresAno(Collection $vendasPorMes, int $ano, string $campo): array
    {
        $dados = $vendasPorMes->toArray();
        $valores = [];

        for ($m = 1; $m <= 12; $m++) {
            $chave = sprintf('%d-%02d', $ano, $m);
            $valores[] = isset($dados[$chave]) ? round((float)($dados[$chave][$campo] ?? 0), 2) : 0;
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
                Log::warning('MensalVendasController: listar faturas falhou', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                ]);
                return false;
            }

            $todasFaturas = array_merge($todasFaturas, $response->json('data') ?? []);
        }

        return collect($todasFaturas);
    }
}
