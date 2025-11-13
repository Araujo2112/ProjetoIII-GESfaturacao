<?php

namespace App\Http\Controllers\clientes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ListaController extends Controller
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

    // Busca todos os clientes, SEM paginar nem pesquisar
    $response = Http::withHeaders([
        'Authorization' => $token,
        'Accept' => 'application/json',
    ])->get('https://api.gesfaturacao.pt/api/v1.0.4/clients');

    $clientes = collect();
    if ($response->successful()) {
        $dados = $response->json();
        $clientes = collect($dados['data'] ?? []);

        // Pesquisa local: filtra em vários campos se houver termo
        if ($search) {
            $searchLower = mb_strtolower($search);
            $clientes = $clientes->filter(function($cli) use ($searchLower) {
                return false !== stripos($cli['name'] ?? '', $searchLower)
                    || false !== stripos($cli['code'] ?? '', $searchLower)
                    || false !== stripos($cli['internalCode'] ?? '', $searchLower)
                    || false !== stripos($cli['vatNumber'] ?? '', $searchLower)
                    || false !== stripos($cli['zipCode'] ?? '', $searchLower)
                    || false !== stripos($cli['city'] ?? '', $searchLower)
                    || false !== stripos($cli['email'] ?? '', $searchLower)
                    || false !== stripos($cli['phone'] ?? '', $searchLower);
            })->values(); // .values() para resetar as keys
        }

        // Ordenação local
        if ($direction === 'asc') {
            $clientes = $clientes->sortBy($sort)->values();
        } else {
            $clientes = $clientes->sortByDesc($sort)->values();
        }
    }

    // Paginação local
    $totalRegistos = $clientes->count();
    $totalPaginas = max(1, ceil($totalRegistos / $rows));
    $paginaAtual = max(1, min($page, $totalPaginas));
    $clientesPaginados = $clientes->forPage($paginaAtual, $rows)->values();

    return view('clientes.lista', [
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


