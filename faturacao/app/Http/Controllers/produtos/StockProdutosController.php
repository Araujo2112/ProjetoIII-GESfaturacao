<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StockProdutosController extends Controller
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
            return view('produtos.stock', [
                'produtos' => [],
                'mensagem' => 'Nenhum produto encontrado'
            ]);
        }

        $produtosFormatados = $this->formatarProdutosStockBaixo($produtosRaw);

        return view('produtos.stock', [
            'produtos' => $produtosFormatados,
        ]);
    }

    private function fetchProdutos(): array
    {
        $token = session('user.token');
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        return $response->json()['data'] ?? [];
    }

    private function formatarProdutosStockBaixo(array $produtos): array
    {
        $produtosFormatados = [];

        foreach ($produtos as $produto) {
            $stockAtual = (float) ($produto['stock'] ?? 0);
            $stockMinimo = (float) ($produto['minStock'] ?? 0);

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

        usort($produtosFormatados, fn($a, $b) => $b['diferenca'] <=> $a['diferenca']);
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
