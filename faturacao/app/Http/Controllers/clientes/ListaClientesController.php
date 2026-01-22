<?php

namespace App\Http\Controllers\clientes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ListaClientesController extends Controller
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
    $allowedSorts = ['code', 'internalCode', 'name', 'vatNumber', 'phone', 'email', 'zipCode'];
    if (!in_array($sort, $allowedSorts)) { $sort = 'name'; }
    if (!in_array($direction, ['asc', 'desc'])) { $direction = 'asc'; }

    $response = Http::withHeaders([
        'Authorization' => $token,
        'Accept' => 'application/json',
    ])->get('https://api.gesfaturacao.pt/api/v1.0.4/clients');

    $clientes = collect(); //inicia a coleção vazia
    if ($response->successful()) {
        $dados = $response->json(); //converte JSON para array PHP
        $clientes = collect($dados['data'] ?? []); // Extrai o array de clientes e converte para Collection

        if ($search) {
            $searchLower = mb_strtolower($search); //minúsculas
            $clientes = $clientes->filter(function($cli) use ($searchLower) {
                return false !== stripos($cli['name'] ?? '', $searchLower)
                    || false !== stripos($cli['code'] ?? '', $searchLower)
                    || false !== stripos($cli['internalCode'] ?? '', $searchLower)
                    || false !== stripos($cli['vatNumber'] ?? '', $searchLower)
                    || false !== stripos($cli['zipCode'] ?? '', $searchLower)
                    || false !== stripos($cli['city'] ?? '', $searchLower)
                    || false !== stripos($cli['email'] ?? '', $searchLower)
                    || false !== stripos($cli['phone'] ?? '', $searchLower);
            })->values();
        }

        if ($direction === 'asc') {
            $clientes = $clientes->sortBy($sort)->values();
        } else {
            $clientes = $clientes->sortByDesc($sort)->values();
        }
    }

    $totalRegistos = $clientes->count();
    $totalPaginas = max(1, ceil($totalRegistos / $rows));
    $paginaAtual = max(1, min($page, $totalPaginas)); //página atual dentro dos limites
    $clientesPaginados = $clientes->forPage($paginaAtual, $rows)->values(); //extrai registos da página atual

    return view('clientes.listaClientes', [
        'clientes' => $clientesPaginados->toArray(),
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


