<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class RankingProdutosController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        $validacao = $this->validateToken($token);
        if (!$validacao) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);

        $topProdutos = $this->obterTopProdutos($token, $inicio, $fim);

        return view('produtos.topProdutos', [
            'produtos' => $topProdutos,
            'periodoTexto' => $periodoTexto,
            'graficoDados' => [
                'nomes' => array_column($topProdutos, 'nome'),
                'qtds' => array_column($topProdutos, 'qtd'),
            ]
        ]);
    }

    /**
     * Export PDF - recebe chart_img (data URI) e modo (mais/menos)
     */
    public function exportPdf(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        if (!$this->validateToken($token)) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        $chartImg = $request->input('chart_img');
        if (!$chartImg || !Str::startsWith($chartImg, 'data:image')) {
            return back()->withErrors(['error' => 'Não foi possível obter a imagem do gráfico para exportação.']);
        }

        $modo = $request->input('modo', 'mais');
        if (!in_array($modo, ['mais', 'menos'])) {
            $modo = 'mais';
        }

        [$inicio, $fim, $periodoTexto] = $this->definirPeriodo($request);

        $topProdutos = $this->obterTopProdutos($token, $inicio, $fim);

        // Ordenação para bater com o que o utilizador está a ver
        usort($topProdutos, function ($a, $b) use ($modo) {
            $qa = (float)($a['qtd'] ?? 0);
            $qb = (float)($b['qtd'] ?? 0);
            return ($modo === 'menos') ? ($qa <=> $qb) : ($qb <=> $qa);
        });

        $pdf = Pdf::loadView('exports.produtos_top_pdf', [
            'produtos' => $topProdutos,
            'chartImg' => $chartImg,
            'geradoEm' => now(),
            'periodoTexto' => $periodoTexto,
            'modoTexto' => ($modo === 'menos') ? '- Vendidos' : '+ Vendidos',
        ])->setPaper('a4', 'portrait');

        return $pdf->download('top_5_artigos_' . ($modo === 'menos' ? 'menos' : 'mais') . '_vendidos.pdf');
    }

    /**
     * Export CSV - usa query string (periodo, datas) e modo (mais/menos)
     */
    public function exportCsv(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        if (!$this->validateToken($token)) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        $modo = $request->input('modo', 'mais');
        if (!in_array($modo, ['mais', 'menos'])) {
            $modo = 'mais';
        }

        [$inicio, $fim, $periodoTexto] = $this->definirPeriodo($request);
        $topProdutos = $this->obterTopProdutos($token, $inicio, $fim);

        usort($topProdutos, function ($a, $b) use ($modo) {
            $qa = (float)($a['qtd'] ?? 0);
            $qb = (float)($b['qtd'] ?? 0);
            return ($modo === 'menos') ? ($qa <=> $qb) : ($qb <=> $qa);
        });

        $filename = 'top_5_artigos_' . ($modo === 'menos' ? 'menos' : 'mais') . '_vendidos.csv';

        return response()->streamDownload(function () use ($topProdutos, $periodoTexto, $modo) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 (Excel PT)
            echo "\xEF\xBB\xBF";

            // Cabeçalho extra (opcional)
            fputcsv($out, ['Relatório', 'Top 5 Artigos', ($modo === 'menos' ? '- Vendidos' : '+ Vendidos')], ';');
            fputcsv($out, ['Período', $periodoTexto], ';');
            fputcsv($out, [], ';');

            // Cabeçalho tabela
            fputcsv($out, ['Código', 'Nome', 'Categoria', 'Qtd Vendida', 'Preço c/IVA'], ';');

            foreach ($topProdutos as $p) {
                fputcsv($out, [
                    $p['cod'] ?? '',
                    $p['nome'] ?? '',
                    $p['categoria'] ?? 'Sem Categoria',
                    number_format((float)($p['qtd'] ?? 0), 0, ',', '.'),
                    number_format((float)($p['preco_c_iva'] ?? 0), 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * ---- Helpers ----
     */

    private function obterTopProdutos(string $token, string $inicio, string $fim): array
    {
        $faturasValidas = $this->buscarFaturasValidas($token, $inicio, $fim);
        if (!$faturasValidas || $faturasValidas->isEmpty()) {
            return [];
        }

        $faturasDetalhadas = $this->buscarDetalhesFaturas($token, $faturasValidas);
        if ($faturasDetalhadas->isEmpty()) {
            return [];
        }

        $qtdPorProduto = $this->calcularQtdPorProduto($faturasDetalhadas);
        if (empty($qtdPorProduto)) {
            return [];
        }

        arsort($qtdPorProduto);
        $top5Ids = array_slice(array_keys($qtdPorProduto), 0, 5);

        $topProdutos = [];
        foreach ($top5Ids as $prodId) {
            $produtoData = $this->fetchProdutosID($prodId, $token);
            if (!empty($produtoData)) {
                $topProdutos[] = [
                    'cod' => $produtoData['code'] ?? '',
                    'nome' => $produtoData['description'] ?? '',
                    'categoria' => $produtoData['category']['name'] ?? 'Sem Categoria',
                    'qtd' => $qtdPorProduto[$prodId] ?? 0,
                    'preco_c_iva' => (float)($produtoData['pricePvp'] ?? 0),
                ];
            }
        }

        // Garantir ordem por defeito: + vendidos
        usort($topProdutos, fn($a, $b) => (float)$b['qtd'] <=> (float)$a['qtd']);

        return $topProdutos;
    }

    private function definirPeriodo(Request $request)
    {
        $hoje = Carbon::today()->format('Y-m-d');
        $inicioSemanaAtual = Carbon::now()->startOfWeek()->format('Y-m-d');
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

    private function buscarFaturasValidas(string $token, string $inicio, string $fim): Collection|bool
    {
        $faturas = $this->listarTodasFaturasSemFiltro($token);
        if ($faturas === false) {
            return false;
        }

        return $faturas->filter(function ($fatura) use ($inicio, $fim) {
            $statusId = $fatura['status']['id'] ?? 0;
            if ($statusId != 2) {
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
                    \Log::error('Erro na requisição: ' . (method_exists($response, 'status') ? $response->status() : 'Desconhecido'));
                }
            }
        });

        return $detalhes;
    }

    private function calcularQtdPorProduto(Collection $faturasDetalhadas): array
    {
        $qtdPorProduto = [];

        foreach ($faturasDetalhadas as $fatura) {
            foreach ($fatura['lines'] ?? [] as $linha) {
                $artigoId = $linha['article']['id'] ?? null;
                if (!$artigoId) continue;

                $quantidade = (float)($linha['quantity'] ?? 0);

                if (!isset($qtdPorProduto[$artigoId])) {
                    $qtdPorProduto[$artigoId] = 0;
                }

                $qtdPorProduto[$artigoId] += $quantidade;
            }
        }

        return $qtdPorProduto;
    }

    private function fetchProdutosID($id, $token)
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get("https://api.gesfaturacao.pt/api/v1.0.4/products/{$id}");

        if ($response->successful()) {
            return $response->json('data');
        }
        return [];
    }

    private function validateToken($token)
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->successful();
    }
}
