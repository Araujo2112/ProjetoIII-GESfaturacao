<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
