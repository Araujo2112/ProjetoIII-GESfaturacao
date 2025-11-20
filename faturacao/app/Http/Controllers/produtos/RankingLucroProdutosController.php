<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class RankingLucroProdutosController extends Controller
{
    public function index()
    {
        if (!session()->has('user.token')) {
            return redirect()->route('login');
        }

        $token = session('user.token');

        // Mesmo pedido da lista de produtos
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept'        => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        if (!$response->successful()) {
            return view('produtos.rankingLucro', [
                'top5' => [],
                'erro' => 'Não foi possível obter os dados dos produtos. (HTTP ' . $response->status() . ')',
            ]);
        }

        $dados    = $response->json();
        $produtos = collect($dados['data'] ?? []);

        // Mapear produtos e calcular % lucro
        $produtos = $produtos->map(function ($produto) {

            $pvp = $produto['pricePvp'] ?? 0;

            // ⚠️ AQUI é o ponto que podes ter de ajustar:
            // troca 'priceCost' pelo nome real do campo de custo na tua API
            $custo = $produto['priceCost']
                ?? $produto['costPrice']
                ?? $produto['purchasePrice']
                ?? $produto['buyPrice']
                ?? null;

            $margemPercent = null;

            if (!is_null($custo) && $custo > 0) {
                $margemPercent = (($pvp - $custo) / $custo) * 100;
            }

            return [
                'id'            => $produto['id'] ?? '',
                'code'          => $produto['code'] ?? '',
                'description'   => $produto['description'] ?? '',
                'category'      => $produto['category']['name'] ?? 'Sem Categoria',
                'type'          => $produto['type'] ?? '',
                'pricePvp'      => $pvp,
                'cost'          => $custo,
                'marginPercent' => $margemPercent,
                'unit'          => $produto['unit']['name'] ?? '',
            ];
        });

        // Filtrar só produtos que têm custo e margem calculada
        $produtosComMargem = $produtos->filter(function ($p) {
            return !is_null($p['marginPercent']);
        });

        // Ordenar por % lucro desc e ficar só com top 5
        $top5 = $produtosComMargem
            ->sortByDesc('marginPercent')
            ->take(5)
            ->values()
            ->toArray();

        return view('produtos.rankingLucro', [
            'top5' => $top5,
        ]);
    }
}
