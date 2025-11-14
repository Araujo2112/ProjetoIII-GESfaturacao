<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class AbaixoStockProdutosController extends Controller
{
    public function index()
    {
        // 1. Verificar token
        if (!session()->has('user.token')) {
            return redirect()->route('login');
        }

        $token = session('user.token');

        // 2. Obter produtos (MESMA CHAMADA da lista)
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept'        => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        if (!$response->successful()) {
            return view('produtos.abaixoStock', [
                'produtos' => [],
                'erro' => 'Não foi possível obter os produtos. (HTTP ' . $response->status() . ')',
            ]);
        }

        $dados    = $response->json();
        $produtos = collect($dados['data'] ?? []);

        // 3. Estrutura normalizada (igual à lista)
        $produtos = $produtos->map(function ($produto) {
            return [
                'id'          => $produto['id'] ?? '',
                'code'        => $produto['code'] ?? '',
                'description' => $produto['description'] ?? '',
                'category'    => $produto['category']['name'] ?? 'Sem Categoria',
                'type'        => $produto['type'] ?? '',
                'pricePvp'    => $produto['pricePvp'] ?? 0,
                'tax'         => $produto['tax']['value'] ?? 0,
                'stock'       => $produto['stock'] ?? 0,
                'unit'        => $produto['unit']['name'] ?? '',
            ];
        });

        // 4. Filtrar produtos abaixo do stock -> assume-se stock == 0
        $produtosAbaixo = $produtos
            ->filter(fn ($p) => ($p['stock'] ?? 0) <= 0)
            ->sortBy('stock')           // ordenar por stock asc
            ->take(5)                   // TOP 5
            ->values()
            ->toArray();

        return view('produtos.abaixoStock', [
            'produtos' => $produtosAbaixo,
        ]);
    }
}
