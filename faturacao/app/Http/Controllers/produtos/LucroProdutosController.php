<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LucroProdutosController extends Controller
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

        $produtosRaw = $this->fetchProdutos();
        if (!$produtosRaw) {
            return view('produtos.lucro', [
                'produtos' => [],
                'mensagem' => 'Nenhum produto encontrado'
            ]);
        }

        $produtosFormatados = $this->formatarProdutosComLucro($produtosRaw);

        $topProdutos = collect($produtosFormatados)
            ->sortByDesc('lucro')
            ->take(5)
            ->values()
            ->all();

        return view('produtos.lucro', [
            'produtos' => $topProdutos,
            'graficoDados' => [
                'nomes' => array_column($topProdutos, 'nome'),
                'lucros' => array_column($topProdutos, 'lucro'),
            ]
        ]);
    }

    /**
     * Exporta PDF (com gráfico incluído como imagem base64 enviada pelo frontend)
     * Espera receber: chart_img = "data:image/png;base64,...."
     */
    public function exportPdf(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        $validacao = $this->validateToken($token);
        if (!$validacao) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        $chartImg = $request->input('chart_img');
        if (!$chartImg || !Str::startsWith($chartImg, 'data:image')) {
            return back()->withErrors(['error' => 'Não foi possível obter a imagem do gráfico para exportação.']);
        }

        // Reutiliza exatamente a mesma lógica do index()
        $produtosRaw = $this->fetchProdutos();
        $produtosFormatados = $this->formatarProdutosComLucro($produtosRaw);

        $topProdutos = collect($produtosFormatados)
            ->sortByDesc('lucro')
            ->take(5)
            ->values()
            ->all();

        $pdf = Pdf::loadView('exports.produtos_lucro_pdf', [
            'produtos' => $topProdutos,
            'chartImg' => $chartImg,
            'geradoEm' => now(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('top_5_artigos_maior_lucro.pdf');
    }

    /**
     * Exporta CSV (dados da tabela)
     */
    public function exportCsv(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        $validacao = $this->validateToken($token);
        if (!$validacao) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        // Reutiliza exatamente a mesma lógica do index()
        $produtosRaw = $this->fetchProdutos();
        $produtosFormatados = $this->formatarProdutosComLucro($produtosRaw);

        $topProdutos = collect($produtosFormatados)
            ->sortByDesc('lucro')
            ->take(5)
            ->values()
            ->all();

        $filename = 'top_5_artigos_maior_lucro.csv';

        return response()->streamDownload(function () use ($topProdutos) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 para Excel abrir acentos corretamente
            echo "\xEF\xBB\xBF";

            // Separador ; (Excel PT)
            fputcsv($out, ['Código', 'Nome', 'Categoria', 'Preço s/IVA', 'Custo', 'Lucro'], ';');

            foreach ($topProdutos as $p) {
                fputcsv($out, [
                    $p['cod'],
                    $p['nome'],
                    $p['categoria'],
                    number_format((float)$p['preco_s_iva'], 2, ',', '.'),
                    number_format((float)$p['custo'], 2, ',', '.'),
                    number_format((float)$p['lucro'], 2, ',', '.'),
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function fetchProdutos()
    {
        $token = session('user.token');
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        return $response->json()['data'] ?? [];
    }

    private function formatarProdutosComLucro(array $produtos): array
    {
        $formatados = [];
        foreach ($produtos as $produto) {
            $preco_s_iva = (float) ($produto['price'] ?? 0);
            $custo = (float) ($produto['initialPrice'] ?? 0);
            $lucro = $preco_s_iva - $custo;
            $formatados[] = [
                'id' => $produto['id'] ?? '',
                'cod' => $produto['code'] ?? '',
                'nome' => $produto['description'] ?? '',
                'categoria' => $produto['category']['name'] ?? 'Sem Categoria',
                'preco_s_iva' => $preco_s_iva,
                'custo' => $custo,
                'lucro' => $lucro,
            ];
        }
        return $formatados;
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
