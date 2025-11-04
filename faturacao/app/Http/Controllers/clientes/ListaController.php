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

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get("https://api.gesfaturacao.pt/api/v1.0.4/clients/{$rows}/{$page}");


        $clientes = [];
        $totalPaginas = 1;
        $paginaAtual = $page;
        $totalRegistos = 0;

        if ($response->successful()) {
            $dados = $response->json();
            $clientes = $dados['data'] ?? [];
            $totalPaginas = $dados['pagination']['lastPage'] ?? 1;
            $paginaAtual = $dados['pagination']['currentPage'] ?? $page;
            $totalRegistos = $dados['pagination']['total'] ?? 0;
        }

        return view('clientes.lista', compact('clientes', 'rows', 'paginaAtual', 'totalPaginas', 'totalRegistos'));
    }
}
