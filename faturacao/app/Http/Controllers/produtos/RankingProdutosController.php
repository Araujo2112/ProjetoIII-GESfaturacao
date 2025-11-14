<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class RankingProdutosController extends Controller
{
    public function index()
    {
        // 1. Verificar sessão
        if (!session()->has('user.token')) {
            return redirect()->route('login');
        }

        $token = session('user.token');

        // 2. Buscar TODOS os produtos (mesmo pedido que na lista)
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept'        => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        if (!$response->successful()) {
            return view('produtos.ranking', [
                'top5' => [],
                'erro' => 'Não foi possível obter os dados dos produtos. (HTTP ' . $response->status() . ')',
            ]);
        }

        $dados    = $response->json();
        $produtos = collect($dados['data'] ?? []);

        // 3. Mapear produtos para a mesma estrutura da lista
        $produtos = $produtos->map(function ($produto) {
            return [
                'id'          => $produto['id'] ?? '',
                'code'        => $produto['code'] ?? '',
                'description' => $produto['description'] ?? '',
                'category'    => $produto['category']['name'] ?? 'Sem Categoria',
                'type'        => $produto['type'] ?? '',
                'pricePvp'    => $produto['pricePvp'] ?? 0,
                'tax'         => isset($produto['tax']['value']) ? $produto['tax']['value'] : 0,
                'stock'       => $produto['stock'] ?? 0,
                'unit'        => $produto['unit']['name'] ?? '',
            ];
        });

        // 4. Ordenar por stock crescente (menos stock = mais vendido)
        $top5 = $produtos
            ->sortBy('stock')   // menor stock primeiro
            ->take(5)
            ->values()
            ->toArray();

        // 5. Enviar para a view
        return view('produtos.ranking', [
            'top5' => $top5,
        ]);
    }
}
