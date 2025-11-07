<?php

namespace App\Http\Controllers\clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RankingController extends Controller
{
    private string $base = 'https://api.gesfaturacao.pt/api/v1.0.4';

    private function headers(): array
    {
        return [
            // EXATAMENTE como na Lista (sem "Bearer")
            'Authorization' => session('user.token'),
            'Accept'        => 'application/json',
        ];
    }

    public function topEuros(Request $request)
    {
        if (!session()->has('user.token')) return redirect()->route('login');

        [$rows, $dbg] = $this->buildRanking();
        $top = $rows->sortByDesc('total_euros')->take(5)->values();

        return view('clientes.top_vendas_euros', [
            'top5ClientesEuros' => $top,
            'debugInfo'         => $dbg,
        ]);
    }

    public function topQuantidade(Request $request)
    {
        if (!session()->has('user.token')) return redirect()->route('login');

        [$rows, $dbg] = $this->buildRanking();
        $top = $rows->sortByDesc('num_vendas')->take(5)->values();

        return view('clientes.top_vendas_quantidade', [
            'top5ClientesQtd' => $top,
            'debugInfo'       => $dbg,
        ]);
    }

    /** ===================== NÚCLEO ===================== */

    private function buildRanking(): array
    {
        // 1) clientes (pode falhar — não é fatal)
        $clientes = $this->getAllClients(); // Collection keyed by id

        // 2) documentos globais
        $docs = $this->getAllSalesDocsGlobal();

        // 3) se não há globais, tenta por cliente
        if ($docs->isEmpty()) {
            $docs = $this->getAllSalesDocsByClient($clientes->keys()->all());
        }

        // 4) agrega
        $acc = [];
        foreach ($docs as $d) {
            // id do cliente em várias variantes
            $cid = Arr::get($d, 'clientId')
                ?? Arr::get($d, 'client_id')
                ?? Arr::get($d, 'client.id')
                ?? Arr::get($d, 'cliente_id')
                ?? Arr::get($d, 'cliente.id')
                ?? Arr::get($d, '__client_id'); // injetado no fallback por cliente

            if (!$cid) {
                // última tentativa: se vier um código único no próprio doc
                $cid = Arr::get($d, 'client.code') ?? Arr::get($d, 'customer.code');
            }
            if (!$cid) continue;

            // total
            $total = (float) (
                Arr::get($d, 'total')
                ?? Arr::get($d, 'totalNet')
                ?? Arr::get($d, 'total_liquido')
                ?? Arr::get($d, 'valor_total')
                ?? Arr::get($d, 'amount')
                ?? 0
            );

            if (!isset($acc[$cid])) {
                // procura nome/nif no mapa de clientes; se não houver, usa o doc
                $cli = $clientes->get($cid, []);
                $nome = Arr::get($cli, 'name') ?? Arr::get($cli, 'nome')
                      ?? Arr::get($d, 'client.name') ?? Arr::get($d, 'clientName')
                      ?? Arr::get($d, 'customer.name') ?? 'Desconhecido';

                $nif  = Arr::get($cli, 'vat')  ?? Arr::get($cli, 'nif')
                      ?? Arr::get($d, 'client.vat') ?? Arr::get($d, 'vatNumber') ?? '—';

                $acc[$cid] = (object) [
                    'cliente_id'  => $cid,
                    'cliente'     => $nome,
                    'nif'         => $nif,
                    'num_vendas'  => 0,
                    'total_euros' => 0.0,
                ];
            }

            $acc[$cid]->num_vendas  += 1;
            $acc[$cid]->total_euros += $total;
        }

        $dbg = [
            'clients_ok'   => $this->lastClientsOk,
            'clients_cnt'  => $clientes->count(),
            'docs_global'  => $this->lastDocsGlobalTried,
            'docs_by_cli'  => $this->lastDocsByClientTried,
            'docs_cnt'     => $docs->count(),
        ];

        return [collect(array_values($acc)), $dbg];
    }

    /** ===================== FETCHERS ===================== */

    private bool $lastClientsOk = false;
    private array $lastDocsGlobalTried = [];
    private array $lastDocsByClientTried = [];

    private function getAllClients(): Collection
    {
        $rowsPerPage = 200;
        $page = 1;
        $headers = $this->headers();
        $all = collect();

        while (true) {
            $url = "{$this->base}/clients/{$rowsPerPage}/{$page}";
            $r = Http::withHeaders($headers)->get($url);
            if (!$r->successful()) {
                Log::error('GET /clients falhou', ['status'=>$r->status(),'body'=>$r->body()]);
                break;
            }

            $this->lastClientsOk = true;

            $j = $r->json();
            $data = $j['data'] ?? [];
            if (empty($data)) break;

            foreach ($data as $c) {
                $id = Arr::get($c, 'id') ?? Arr::get($c, 'code') ?? Arr::get($c, 'codigo');
                if ($id !== null) $all->put($id, $c);
            }

            $last = (int) ($j['pagination']['lastPage'] ?? $page);
            if ($page >= $last) break;
            $page++;
        }

        return $all;
    }

    /** tenta endpoints globais com paginação {rows}/{page} */
    private function getAllSalesDocsGlobal(): Collection
    {
        $candidates = [
            'invoices',
            'sales',
            'documents',
            'sales-documents',
            'sales/invoices',
            'documentos-venda',
            'faturas',
            'facturas',
        ];
        $this->lastDocsGlobalTried = $candidates;

        foreach ($candidates as $path) {
            $docs = $this->fetchPagedList("{$this->base}/{$path}");
            if ($docs->isNotEmpty()) {
                return $docs;
            }
        }
        return collect();
    }

    /** se globais falharem: itera clientes e chama sub-recursos */
    private function getAllSalesDocsByClient(array $clientIds): Collection
    {
        if (empty($clientIds)) return collect();

        $paths = ['invoices', 'sales', 'documents', 'documentos', 'documentos-venda'];
        $this->lastDocsByClientTried = $paths;

        $headers = $this->headers();
        $rowsPerPage = 200;
        $all = collect();

        foreach ($clientIds as $cid) {
            foreach ($paths as $p) {
                $page = 1;
                while (true) {
                    $url = "{$this->base}/clients/{$cid}/{$p}/{$rowsPerPage}/{$page}";
                    $r = Http::withHeaders($headers)->get($url);
                    if (!$r->successful()) break;

                    $j = $r->json();
                    $data = $j['data'] ?? [];
                    if (empty($data)) break;

                    // marca o clientId caso o doc não traga
                    foreach ($data as &$d) {
                        if (!isset($d['clientId']) && !isset($d['client_id']) && !isset($d['client']['id'])) {
                            $d['__client_id'] = $cid;
                        }
                    }

                    $all = $all->concat($data);

                    $last = (int) ($j['pagination']['lastPage'] ?? $page);
                    if ($page >= $last) break;
                    $page++;
                }
            }
        }
        return $all;
    }

    /** paginação no formato {rows}/{page} devolvendo {data, pagination} */
    private function fetchPagedList(string $baseUrl): Collection
    {
        $rowsPerPage = 200;
        $page = 1;
        $headers = $this->headers();
        $all = collect();

        while (true) {
            $url = "{$baseUrl}/{$rowsPerPage}/{$page}";
            $r = Http::withHeaders($headers)->get($url);
            if (!$r->successful()) break;

            $j = $r->json();
            $data = $j['data'] ?? [];
            if (empty($data)) break;

            $all = $all->concat($data);

            $last = (int) ($j['pagination']['lastPage'] ?? $page);
            if ($page >= $last) break;
            $page++;
        }
        return $all;
    }
}
