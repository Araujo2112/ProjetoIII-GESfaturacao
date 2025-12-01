<?php

namespace App\Http\Controllers\produtos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class RankingProdutosController extends Controller
{
    public function topProdutos(Request $request)
    {
        $token = session('user.token');
        if (!$token) {
            return redirect('/')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        $validacao = $this->validateToken($token);
        if (!$validacao) {
            return redirect('/')->withErrors(['error' => 'Token inválido ou expirado.']);
        }

        [$inicio, $fim, $periodoTexto, $periodo] = $this->definirPeriodo($request);
        $produtos = $this->fetchProdutos();

        if (!$produtos) {
            return view('produtos.topProdutos', ['periodoTexto' => $periodoTexto]);
        }

        $todosProdutos = $this->formatarProdutos($produtos);

        return view('produtos.topProdutos', [
            'top5ProdutosQtd' => collect($todosProdutos)->sortByDesc('qtd')->take(5)->values(),
            'top5ProdutosQtdBaixo' => collect($todosProdutos)->sortBy('qtd')->take(5)->values(),
            'top5ProdutosLucro' => collect($todosProdutos)->sortByDesc('lucro')->take(5)->values(),
            'produtosStockBaixo' => collect($todosProdutos)
                ->where(fn($p) => $p['stock_atual'] < $p['stock_minimo'])
                ->take(5)
                ->values(),
            'periodoTexto' => $periodoTexto,
        ]);
    }

    private function definirPeriodo(Request $request): array
    {
        $hoje = Carbon::today()->format('Y-m-d');
        $ontem = Carbon::yesterday()->format('Y-m-d');
        $primeiroDoMes = Carbon::now()->startOfMonth()->format('Y-m-d');
        $ultimoMesInicio = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $ultimoMesFim = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $primeiroDoAno = Carbon::now()->startOfYear()->format('Y-m-d');
        $ultimoAnoInicio = Carbon::now()->subYear()->startOfYear()->format('Y-m-d');
        $ultimoAnoFim = Carbon::now()->subYear()->endOfYear()->format('Y-m-d');

        $periodo = $request->input('periodo', 'geral');
        $inicio = null;
        $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'geral':
                $inicio = null;
                $fim = null;
                $periodoTexto = 'Todos os dados disponíveis';
                break;
            case 'hoje':
                $inicio = $hoje;
                $fim = $hoje;
                $periodoTexto = "Hoje: $hoje";
                break;
            case 'ontem':
                $inicio = $ontem;
                $fim = $ontem;
                $periodoTexto = "Ontem: $ontem";
                break;
            case 'mes':
                $inicio = $primeiroDoMes;
                $fim = $hoje;
                $periodoTexto = "Mês atual: $primeiroDoMes a $hoje";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio;
                $fim = $ultimoMesFim;
                $periodoTexto = "Mês anterior: $ultimoMesInicio a $ultimoMesFim";
                break;
            case 'ano':
                $inicio = $primeiroDoAno;
                $fim = $hoje;
                $periodoTexto = "Ano atual: $primeiroDoAno a $hoje";
                break;
            case 'ultimo_ano':
                $inicio = $ultimoAnoInicio;
                $fim = $ultimoAnoFim;
                $periodoTexto = "Ano anterior: $ultimoAnoInicio a $ultimoAnoFim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
            default:
                $inicio = null;
                $fim = null;
                $periodoTexto = 'Todos os dados disponíveis';
                break;
        }

        return [$inicio, $fim, $periodoTexto, $periodo];
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

    private function formatarProdutos(array $produtos): array
    {
        return array_map(function($produto) {
            return [
                'id' => $produto['id'] ?? '',
                'cod' => $produto['code'] ?? '',
                'nome' => $produto['description'] ?? '',
                'categoria' => $produto['category']['name'] ?? 'Sem Categoria',
                'preco_c_iva' => (float) ($produto['pricePvp'] ?? 0),
                'preco_s_iva' => (float) ($produto['price'] ?? 0),
                'custo' => (float) ($produto['initialPrice'] ?? 0),
                'qtd' => 0,
                'lucro' => (float) ($produto['price'] ?? 0) - (float) ($produto['initialPrice'] ?? 0),
                'stock_atual' => (float) ($produto['stock'] ?? 0),
                'stock_minimo' => (float) ($produto['minStock'] ?? 0),
            ];
        }, $produtos);
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
