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

class DiarioVendasController extends Controller
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
        $vendasPorDia = $this->calcularVendasPorDia($token, $detalhesFaturas);
        $totais = $this->calcularTotais($vendasPorDia);

        $datasFormatadas = $this->formatarDatas($inicio, $fim);
        $valoresPorDia = $this->extrairValoresPorDia($vendasPorDia, $inicio, $fim);
        $lucroPorDia   = $this->extrairLucroPorDia($vendasPorDia, $inicio, $fim);

        return view('relatorios.diarioVendas', [
            'vendasPorDia' => $vendasPorDia,
            'datasFormatadas' => $datasFormatadas,
            'valoresPorDia' => $valoresPorDia,
            'lucroPorDia'   => $lucroPorDia,
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

    // ============================
    // EXPORT PDF
    // ============================
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
        $vendasPorDia = $this->calcularVendasPorDia($token, $detalhesFaturas);
        $totais = $this->calcularTotais($vendasPorDia);

        $pdf = Pdf::loadView('exports.diario_vendas_pdf', [
            'titulo' => 'Relatório - Diário',
            'chartImg' => $chartImg,
            'modo' => $modo,
            'modoTexto' => ($modo === 'vendas') ? 'Vendas' : 'Lucro',
            'periodoTexto' => $periodoTexto,
            'vendasPorDia' => $vendasPorDia,
            'totais' => $totais,
            'geradoEm' => now(),
        ])->setPaper('a4', 'portrait');

        $nome = $modo === 'vendas' ? 'relatorio_diario_vendas.pdf' : 'relatorio_diario_lucro.pdf';
        return $pdf->download($nome);
    }

    // ============================
    // EXPORT CSV
    // ============================
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
        $vendasPorDia = $this->calcularVendasPorDia($token, $detalhesFaturas);
        $totais = $this->calcularTotais($vendasPorDia);

        $filename = 'relatorio_diario.csv';

        return response()->streamDownload(function () use ($vendasPorDia, $totais) {
            $out = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";

            fputcsv($out, ['Dia', 'Vendas c/IVA', 'Vendas s/IVA', 'Custos', 'Lucro', 'Quantidade', 'Nº Vendas'], ';');

            foreach ($vendasPorDia as $dados) {
                fputcsv($out, [
                    $dados['dia'] ?? '',
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

    // =========================
    // HELPERS
    // =========================

    private function calcularTotais(Collection $vendasPorDia): array
    {
        $total_vendas_iva = 0;
        $total_vendas = 0;
        $total_custos = 0;
        $total_lucro = 0;
        $total_quantidade = 0;
        $total_num_vendas = 0;

        foreach ($vendasPorDia as $dados) {
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
        $hoje = Carbon::today()->format('Y-m-d');
        $primeiroDoMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $ultimoMesInicio = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $ultimoMesFim = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');

        $periodo = $request->input('periodo', 'mes');
        $inicio = null;
        $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'mes':
                $inicio = $primeiroDoMes;
                $fim = $hoje;
                $periodoTexto = "Mês atual: $primeiroDoMes a $hoje";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio;
                $fim = $ultimoMesFim;
                $periodoTexto = "Mês anterior: $ultimoMesInicio a $ultimoMesFim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                if (!$inicio || !$fim) {
                    $inicio = $primeiroDoMes;
                    $fim = $hoje;
                    $periodo = 'mes';
                }
                $periodoTexto = "Personalizado: $inicio a $fim";
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
            if ($statusId != 2) return false;
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
                        $pool->withHeaders(['Authorization' => $token, 'Accept' => 'application/json'])->get($url);
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

    private function calcularVendasPorDia(string $token, Collection $detalhesFaturas): Collection
    {
        $produtosCache = [];

        return $detalhesFaturas->groupBy(fn ($f) => substr($f['date'] ?? '', 0, 10))
            ->map(function ($faturasDia, $dia) use ($token, &$produtosCache) {
                $vendasIVA = $faturasDia->sum(fn($f) => (float)($f['grossTotal'] ?? 0));
                $vendas = $faturasDia->sum(fn($f) => (float)($f['netTotal'] ?? 0));
                $custos = 0.0;
                $numVendas = $faturasDia->count();
                $quantidade = 0.0;

                foreach ($faturasDia as $fatura) {
                    foreach ($fatura['lines'] ?? [] as $line) {
                        $quantLine = (float)($line['quantity'] ?? 0);
                        $quantidade += $quantLine;

                        $prodId = $line['article']['id'] ?? null;
                        if ($prodId) {
                            if (!array_key_exists($prodId, $produtosCache)) {
                                $response = Http::withHeaders([
                                    'Authorization' => $token,
                                    'Accept' => 'application/json',
                                ])->get("https://api.gesfaturacao.pt/api/v1.0.4/products/{$prodId}");

                                $produtosCache[$prodId] = $response->successful()
                                    ? (float)($response->json('data.initialPrice') ?? 0)
                                    : 0.0;
                            }
                            $custos += $produtosCache[$prodId] * $quantLine;
                        }
                    }
                }

                return [
                    'dia' => $dia,
                    'vendas_com_iva' => $vendasIVA,
                    'vendas_sem_iva' => $vendas,
                    'custos' => $custos,
                    'lucro' => $vendas - $custos,
                    'quantidade' => $quantidade,
                    'num_vendas' => $numVendas,
                ];
            })->sortKeys();
    }

    private function formatarDatas(string $inicio, string $fim): array
    {
        $period = CarbonPeriod::create($inicio, $fim);
        $datas = [];
        foreach ($period as $data) $datas[] = $data->format('d-m');
        return $datas;
    }

    private function extrairValoresPorDia(Collection $vendasPorDia, string $inicio, string $fim): array
    {
        $period = CarbonPeriod::create($inicio, $fim);
        $dados = $vendasPorDia->toArray();
        $valores = [];

        foreach ($period as $data) {
            $chave = $data->format('Y-m-d');
            $valores[] = isset($dados[$chave]) ? round((float)($dados[$chave]['vendas_com_iva'] ?? 0), 2) : 0;
        }
        return $valores;
    }

    private function extrairLucroPorDia(Collection $vendasPorDia, string $inicio, string $fim): array
    {
        $period = CarbonPeriod::create($inicio, $fim);
        $dados = $vendasPorDia->toArray();
        $valores = [];

        foreach ($period as $data) {
            $chave = $data->format('Y-m-d');
            $valores[] = isset($dados[$chave]) ? round((float)($dados[$chave]['lucro'] ?? 0), 2) : 0;
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
            $response = Http::withHeaders(['Authorization' => $token, 'Accept' => 'application/json'])->get($endpoint);
            if (!$response->successful()) return false;

            $todasFaturas = array_merge($todasFaturas, $response->json('data') ?? []);
        }

        return collect($todasFaturas);
    }
}
