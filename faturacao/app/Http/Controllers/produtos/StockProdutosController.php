<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class StockProdutosController extends Controller
{
    public function index(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        if (!$this->validateToken($token)) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        $produtosRaw = $this->fetchProdutos();
        if (!$produtosRaw) {
            return view('produtos.stock', [
                'produtos' => [],
                'graficoDados' => ['nomes' => [], 'diferencas' => [], 'codigos' => []],
                'mensagem' => 'Nenhum produto encontrado'
            ]);
        }

        $produtosFormatados = $this->formatarProdutosStockBaixo($produtosRaw);

        return view('produtos.stock', [
            'produtos' => $produtosFormatados,
            'graficoDados' => [
                'nomes' => array_column($produtosFormatados, 'nome'),
                'diferencas' => array_column($produtosFormatados, 'falta_repor'),
                'codigos' => array_column($produtosFormatados, 'cod'),
            ]
        ]);
    }

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

        $produtosRaw = $this->fetchProdutos();
        $produtosFormatados = $this->formatarProdutosStockBaixo($produtosRaw ?? []);

        $pdf = Pdf::loadView('exports.produtos_stock_baixo_pdf', [
            'titulo' => 'Top 5 Artigos - Abaixo do Limite de Stock',
            'produtos' => $produtosFormatados,
            'chartImg' => $chartImg,
            'geradoEm' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('top_5_artigos_abaixo_stock.pdf');
    }

    public function exportCsv(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        if (!$this->validateToken($token)) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        $produtosRaw = $this->fetchProdutos();
        $produtosFormatados = $this->formatarProdutosStockBaixo($produtosRaw ?? []);

        $filename = 'top_5_artigos_abaixo_stock.csv';

        return response()->streamDownload(function () use ($produtosFormatados) {
            $out = fopen('php://output', 'w');
            echo "\xEF\xBB\xBF";

            fputcsv($out, ['Código', 'Nome', 'Categoria', 'Stock Atual', 'Stock Mínimo', 'Falta Repor'], ';');

            foreach ($produtosFormatados as $p) {
                fputcsv($out, [
                    $p['cod'] ?? '',
                    $p['nome'] ?? '',
                    $p['categoria'] ?? 'Sem Categoria',
                    number_format((float)($p['stock_atual'] ?? 0), 2, ',', '.'),
                    number_format((float)($p['stock_minimo'] ?? 0), 2, ',', '.'),
                    number_format((float)($p['falta_repor'] ?? 0), 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function fetchProdutos(): array
    {
        $token = session('user.token');

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        return $response->successful() ? ($response->json()['data'] ?? []) : [];
    }

    private function formatarProdutosStockBaixo(array $produtos): array
    {
        $produtosFormatados = [];

        foreach ($produtos as $produto) {
            $stockAtual = (float)($produto['stock'] ?? 0);
            $stockMinimo = (float)($produto['minStock'] ?? 0);

            if ($stockAtual < $stockMinimo) {
                $diferenca = $stockMinimo - $stockAtual;
                $produtosFormatados[] = [
                    'id' => $produto['id'] ?? '',
                    'cod' => $produto['code'] ?? '',
                    'nome' => $produto['description'] ?? '',
                    'categoria' => $produto['category']['name'] ?? 'Sem Categoria',
                    'stock_atual' => $stockAtual,
                    'stock_minimo' => $stockMinimo,
                    'diferenca' => $diferenca,
                    'falta_repor' => $diferenca,
                ];
            }
        }

        usort($produtosFormatados, fn($a, $b) => (float)$b['diferenca'] <=> (float)$a['diferenca']);
        return array_slice($produtosFormatados, 0, 5);
    }

    private function validateToken($token): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->successful();
    }
}
