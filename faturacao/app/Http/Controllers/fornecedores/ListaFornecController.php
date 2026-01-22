<?php

namespace App\Http\Controllers\fornecedores;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ListaFornecController extends Controller
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
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $allowedSorts = ['name', 'code', 'vatNumber', 'phone', 'email', 'zipCode'];

        if (!in_array($sort, $allowedSorts)) { $sort = 'name'; }
        if (!in_array($direction, ['asc', 'desc'])) { $direction = 'asc'; }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/suppliers');

        $fornecedores = collect(); //inicia a coleção vazia
        if ($response->successful()) {
            $dados = $response->json(); //converte JSON para array PHP
            $fornecedores = collect($dados['data'] ?? []); //extrai o array de fornecedores e converte para Collection

            if ($search) {
                $searchLower = mb_strtolower($search); //minúsculas
                $fornecedores = $fornecedores->filter(function($fornec) use ($searchLower) {
                    return false !== stripos($fornec['name'] ?? '', $searchLower)
                        || false !== stripos($fornec['code'] ?? '', $searchLower)
                        || false !== stripos($fornec['vatNumber'] ?? '', $searchLower)
                        || false !== stripos($fornec['zipCode'] ?? '', $searchLower)
                        || false !== stripos($fornec['city'] ?? '', $searchLower)
                        || false !== stripos($fornec['email'] ?? '', $searchLower)
                        || false !== stripos($fornec['phone'] ?? '', $searchLower);
                })->values();
            }

            if ($direction === 'asc') {
                $fornecedores = $fornecedores->sortBy($sort)->values();
            } else {
                $fornecedores = $fornecedores->sortByDesc($sort)->values();
            }
        }

        $totalRegistos = $fornecedores->count();
        $totalPaginas = max(1, ceil($totalRegistos / $rows));
        $paginaAtual = max(1, min($page, $totalPaginas)); //página atual dentro dos limites
        $fornecedoresPaginados = $fornecedores->forPage($paginaAtual, $rows)->values(); //extrai registos da página atual

        return view('fornecedores.listaFornec', [
            'fornecedores' => $fornecedoresPaginados->toArray(),
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
