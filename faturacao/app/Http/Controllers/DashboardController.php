<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);
        }

        $validacao = $this->validateToken($token);

        // ----------------- Faturas ------------------
        $faturasCollection = $this->listarTodasFaturasSemFiltro($token);

        if ($faturasCollection === false) 
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);

        $faturasValidas = $this->filtrarPorStatus($faturasCollection);

        // Cálculos diários de vendas
        $faturadoHojeValor = $this->calculaFaturacaoDia($faturasValidas);

        $ontem = date('Y-m-d', strtotime('-1 day'));
        $faturadoOntemValor = $this->calculaFaturacaoDia(
            $faturasValidas,
            date('d', strtotime($ontem)),
            date('m', strtotime($ontem)),
            date('Y', strtotime($ontem))
        );

        // Cálculos mensais de vendas
        $faturadoMesValor = $this->calculaFaturacaoMes($faturasValidas);

        $mesAnterior = date('Y-m', strtotime('first day of last month'));
        [$anoAnteriorMes, $mesAnteriorNum] = explode('-', $mesAnterior);

        $faturadoMesAnteriorValor = $this->calculaFaturacaoMes($faturasValidas, intval($mesAnteriorNum), intval($anoAnteriorMes));

        // Cálculos anuais de vendas
        $anoAtual = date('Y');
        $anoAnterior = $anoAtual - 1;

        $faturadoAnoValor = $this->calculaFaturacaoAno($faturasValidas);
        $faturadoAnoAnteriorValor = $this->calculaFaturacaoAno($faturasValidas, $anoAnterior);

        // Variações percentuais de vendas
        $variacaoHoje = $this->calcularPercentualRelativo($faturadoHojeValor, $faturadoOntemValor);
        $variacaoMes = $this->calcularPercentualRelativo($faturadoMesValor, $faturadoMesAnteriorValor);
        $variacaoAno = $this->calcularPercentualRelativo($faturadoAnoValor, $faturadoAnoAnteriorValor);

        // Dados para gráfico últimos 7 dias (vendas)
        $datasFormatadas = [];
        $totaisPorDia = [];

        for ($i = 6; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $datasFormatadas[] = $data->format('d-m');

            $totalDia = $faturasValidas->filter(function ($fatura) use ($data) {
                return substr($fatura['date'], 0, 10) === $data->format('Y-m-d');
            })->sum(fn($fatura) => floatval($fatura['total'] ?? 0));

            $totaisPorDia[] = round($totalDia, 2);
        }

        // Gráfico mensal comparado (faturas)
        $faturacaoMesAnoAtual = [];
        $faturacaoMesAnoAnterior = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $faturacaoMesAnoAtual[] = $this->calculaFaturacaoMes($faturasValidas, $mes, $anoAtual);
            $faturacaoMesAnoAnterior[] = $this->calculaFaturacaoMes($faturasValidas, $mes, $anoAnterior);
        }
        $graficoMeses = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            'atual' => $faturacaoMesAnoAtual,
            'anterior' => $faturacaoMesAnoAnterior,
        ];

        // ------------------ Compras -------------------
        $comprasCollection = $this->listarTodasComprasSemFiltro($token);

        if ($comprasCollection === false) 
            return redirect('/')->withErrors(['error' => 'Por favor, faça login primeiro.']);

        $comprasValidas = $this->filtrarPorStatus($comprasCollection);

        $comprasMesAnoAtual = [];
        $comprasMesAnoAnterior = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $comprasMesAnoAtual[] = $this->calculaComprasMes($comprasValidas, $mes, $anoAtual);
            $comprasMesAnoAnterior[] = $this->calculaComprasMes($comprasValidas, $mes, $anoAnterior);
        }
        $graficoComprasMeses = [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            'atual' => $comprasMesAnoAtual,
            'anterior' => $comprasMesAnoAnterior,
        ];

        // ------------------ Recibos -------------------
        $faturasCollection = $this->listarTodasFaturasSemFiltro($token);


        $datasFormatadas = [];
        $quantidadesPorDiaFormatado = [];

        for ($i = 6; $i >= 0; $i--) {
            $data = now()->subDays($i);
            $dataFormatada = $data->format('d-m');
            $datasFormatadas[] = $dataFormatada;

            $quantidadesPorDiaFormatado[$dataFormatada] = [];

            $faturasDoDia = $faturasValidas->filter(function ($fatura) use ($data) {
                return substr($fatura['date'], 0, 10) === $data->format('Y-m-d');
            });

            foreach ($faturasDoDia as $fatura) {
                foreach ($fatura['lines'] ?? [] as $line) {
                    $idArtigo = $line['article']['id'] ?? null;
                    if (!$idArtigo) continue;

                    $produto = $this->buscarProdutoPorId($idArtigo, $token);
                    $categoria = $produto['category']['name'] ?? 'Desconhecido';

                    $qtd = floatval($line['quantity'] ?? 0);
                    $quantidadesPorDiaFormatado[$dataFormatada][$categoria] = ($quantidadesPorDiaFormatado[$dataFormatada][$categoria] ?? 0) + $qtd;
                }
            }
        }


        $inicioSeteDias = date('Y-m-d', strtotime('-6 days'));
        $fimSeteDias = date('Y-m-d');
        $faturasValidas = $this->filtrarPorStatusEData($faturasCollection, $inicioSeteDias, $fimSeteDias);

        $faturasDetalhadas = $this->buscarDetalhesFaturas($token, $faturasValidas);

        $dadosCategorias = $this->categoriasProdutos($token, $faturasDetalhadas);

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

            'graficoMeses' => $graficoMeses,
            'graficoComprasMeses' => $graficoComprasMeses,

            'dadosCategorias' => $dadosCategorias,
        ]);
    }

    private function validateToken($token) {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);
        return $response->json();
    }

    private function filtrarPorStatus(Collection $items) {
        return $items->filter(fn($item) => ($item['statusId'] ?? 0) != 2);
    }

    private function filtrarPorStatusEData(Collection $items, $inicio, $fim) {
        return $items->filter(function ($item) use ($inicio, $fim) {
            $statusId = $item['status']['id'] ?? 0;
            if ($statusId != 2) 
                return false;
            $dataItem = substr($item['date'], 0, 10);

            return $dataItem >= $inicio && $dataItem <= $fim;
        });
    }

    // ----- Faturas -----
    private function listarTodasFaturasSemFiltro($token) {
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
                ])->get($endpoint, ['page' => $page]);
                if (!$response->successful()) return false;
                $data = $response->json();
                $faturas = $data['data'] ?? [];
                $todasFaturas = array_merge($todasFaturas, $faturas);
                $lastPage = $data['pagination']['lastPage'] ?? 1;
                $page++;
            } while ($page <= $lastPage);
        }
        return collect($todasFaturas);
    }

    private function calculaFaturacaoDia(Collection $faturasValidas, $dia = null, $mes = null, $ano = null): float {
        $ano = $ano ?? date('Y');
        $mes = $mes ?? date('m');
        $dia = $dia ?? date('d');
        $dateStr = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 10) === $dateStr)
            ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }
    private function calculaFaturacaoMes(Collection $faturasValidas, $mes = null, $ano = null): float {
        $ano = $ano ?? date('Y');
        $mes = $mes ?? date('m');
        $prefixoData = sprintf('%04d-%02d', $ano, $mes);
        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 7) === $prefixoData)
            ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }
    private function calculaFaturacaoAno(Collection $faturasValidas, $ano = null): float {
        $ano = $ano ?? date('Y');
        $prefixoData = sprintf('%04d', $ano);
        return $faturasValidas->filter(fn($fatura) => substr($fatura['date'], 0, 4) === $prefixoData)
            ->sum(fn($fatura) => floatval($fatura['total'] ?? 0));
    }

    private function calcularPercentualRelativo(float $atual, float $anterior): array {
        if ($anterior == 0 || $atual == 0) return ['percent' => '0,00%', 'seta' => '→', 'positivo' => true];
        $diff = $atual - $anterior;
        $percent = ($diff / abs($anterior)) * 100;
        $positivo = $percent >= 0;
        $seta = $positivo ? '↑' : '↓';
        $percentFormatado = number_format($percent, 2, ',', '.') . '%';
        return ['percent' => $percentFormatado, 'seta' => $seta, 'positivo' => $positivo];
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

    // ----- Compras -----
    private function listarTodasComprasSemFiltro($token) {
        $endpoint = 'https://api.gesfaturacao.pt/api/v1.0.4/purchases/invoices';
        $todasCompras = [];
        $page = 1;
        do {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Accept' => 'application/json',
            ])->get($endpoint, ['page' => $page]);
            if (!$response->successful()) {
                \Log::error('Erro na API Compras: ' . $response->body());
                return false;
            }
            $data = $response->json();
            $compras = $data['data'] ?? [];
            $todasCompras = array_merge($todasCompras, $compras);
            $lastPage = $data['pagination']['lastPage'] ?? 1;
            $page++;
        } while ($page <= $lastPage);
        return collect($todasCompras);
    }

    private function calculaComprasMes(Collection $comprasValidas, $mes = null, $ano = null): float {
        $ano = $ano ?? date('Y');
        $mes = $mes ?? date('m');
        $prefixoData = sprintf('%04d-%02d', $ano, $mes);
        return $comprasValidas->filter(fn($compra) => substr($compra['date'], 0, 7) === $prefixoData)
            ->sum(fn($compra) => floatval($compra['total'] ?? 0));
    }

    // ----- Produtos -----
    private function buscarProdutoPorId($id, $token)
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

    private function categoriasProdutos($token, Collection $faturasDetalhadas)
    {
        $totalCategoriasVenda = [];
        $totalCategoriaMontante = [];
        $categoriasPorDia = [];
        $valoresPorDia = [];
        $artigosCache = [];

        foreach ($faturasDetalhadas as $faturaIndex => $fatura) {
            $dataFatura = substr($fatura['date'] ?? '', 0, 10);

            foreach ($fatura['lines'] ?? [] as $lineIndex => $line) {
                $idArtigo = $line['article']['id'] ?? null;
                $quantidade = floatval($line['quantity'] ?? 0);
                $valorLinha = floatval($line['total'] ?? 0);

                if ($idArtigo) {
                    if (!isset($artigosCache[$idArtigo])) {
                        $produto = $this->buscarProdutoPorId($idArtigo, $token);
                        $artigosCache[$idArtigo] = $produto;
                    } else {
                        $produto = $artigosCache[$idArtigo];
                    }
                    $categoria = $produto['category']['name'] ?? 'Desconhecido';
                } else {
                    $categoria = 'Outros';
                }


                $totalCategoriasVenda[$categoria] = ($totalCategoriasVenda[$categoria] ?? 0) + $quantidade;
                $totalCategoriaMontante[$categoria] = ($totalCategoriaMontante[$categoria] ?? 0) + $valorLinha;

                $categoriasPorDia[$dataFatura][$categoria] = ($categoriasPorDia[$dataFatura][$categoria] ?? 0) + $quantidade;
                $valoresPorDia[$dataFatura][$categoria] = ($valoresPorDia[$dataFatura][$categoria] ?? 0) + $valorLinha;
            }
        }

        return [
            'quantidades' => $totalCategoriasVenda,
            'valores' => $totalCategoriaMontante,
            'quantidadesPorDia' => $categoriasPorDia,
            'valoresPorDia' => $valoresPorDia,
        ];
    }
}
