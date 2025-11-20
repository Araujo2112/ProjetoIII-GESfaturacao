<?php

namespace App\Http\Controllers\produtos;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ListaProdutosController extends Controller
{
    public function lista(Request $request)
    {
        if (!session()->has('user.token')) {
            return redirect()->route('login');
        }

        $token = session('user.token');
        $rows = $request->input('rows', 25);
        $page = $request->input('page', 1);
        $search = $request->input('search');
        $sort = $request->input('sort', 'description');
        $direction = $request->input('direction', 'asc');
        $allowedSorts = ['code', 'description', 'pricePvp', 'tax', 'category', 'type', 'unit', 'stock'];

        if (!in_array($sort, $allowedSorts)) { $sort = 'description'; }
        if (!in_array($direction, ['asc', 'desc'])) { $direction = 'asc'; }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/products');

        $produtos = collect();
        if ($response->successful()) {
            $dados = $response->json();
            $produtos = collect($dados['data'] ?? []);

            $produtos = $produtos->map(function ($produto) {
                return [
                    'id' => $produto['id'] ?? '',
                    'code' => $produto['code'] ?? '',
                    'description' => $produto['description'] ?? '',
                    'category' => $produto['category']['name'] ?? 'Sem Categoria',
                    'type' => $produto['type'] ?? '',
                    'pricePvp' => $produto['pricePvp'] ?? '',
                    'tax' => isset($produto['tax']['value']) ? $produto['tax']['value'] : '',
                    'stock' => $produto['stock'] ?? '',
                    'unit' => $produto['unit']['name'] ?? '',
                ];
            });

            if ($search) {
                $searchLower = mb_strtolower($search);
                $produtos = $produtos->filter(function($produto) use ($searchLower) {
                    return false !== stripos($produto['code'] ?? '', $searchLower)
                        || false !== stripos($produto['description'] ?? '', $searchLower)
                        || false !== stripos($produto['category'] ?? '', $searchLower)
                        || false !== stripos($produto['type'] ?? '', $searchLower)
                        || false !== stripos($produto['pricePvp'] ?? '', $searchLower)
                        || false !== stripos($produto['tax'] ?? '', $searchLower)
                        || false !== stripos($produto['unit'] ?? '', $searchLower);
                })->values();
            }

            if ($direction === 'asc') {
                $produtos = $produtos->sortBy($sort)->values();
            } else {
                $produtos = $produtos->sortByDesc($sort)->values();
            }
        }

        $totalRegistos = $produtos->count();
        $totalPaginas = max(1, ceil($totalRegistos / $rows));
        $paginaAtual = max(1, min($page, $totalPaginas));
        $produtosPaginados = $produtos->forPage($paginaAtual, $rows)->values();

        return view('produtos.listaProdutos', [
            'produtos' => $produtosPaginados->toArray(),
            'rows' => $rows,
            'paginaAtual' => $paginaAtual,
            'totalPaginas' => $totalPaginas,
            'totalRegistos' => $totalRegistos,
            'sort' => $sort,
            'direction' => $direction,
            'search' => $search,
        ]);
    }
}
