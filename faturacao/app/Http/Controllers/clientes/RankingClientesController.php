<?php

namespace App\Http\Controllers\clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RankingClientesController extends Controller
{
    private function validateToken($token): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->successful();
    }

    private function clientes($inicio = null, $fim = null)
    {
        $token = session('user.token');
        if (!$token) {
            return null;
        }

        if (!$this->validateToken($token)) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/sales/invoices');

        $invoices = $response->json();
        $data = $invoices['data'] ?? $invoices;

        $ranking = [];
        foreach ($data as $invoice) {
            if (empty($invoice['client']['id']) || empty($invoice['client']['name'])) {
                continue;
            }
            // Apenas faturas pagas/emitidas (status id=2) conforme o teu código
            if (empty($invoice['status']['id']) || intval($invoice['status']['id']) !== 2) {
                continue;
            }

            $data_fatura = $invoice['date'] ?? null;
            if ($inicio && $fim && $data_fatura) {
                if ($data_fatura < $inicio || $data_fatura > $fim) continue;
            }

            $clientId = $invoice['client']['id'];
            $clientName = $invoice['client']['name'];
            $vatNumber = $invoice['client']['vatNumber'] ?? '';
            $total = (float)($invoice['total'] ?? 0);

            if (!isset($ranking[$clientId])) {
                $ranking[$clientId] = [
                    'id' => $clientId,
                    'cliente' => $clientName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_vendas' => 0,
                ];
            }

            $ranking[$clientId]['total_euros'] += $total;
            $ranking[$clientId]['num_vendas'] += 1;
        }

        return collect($ranking);
    }

    /**
     * Resolve período e devolve: [$inicio, $fim, $periodoTexto]
     */
    private function resolverPeriodo(Request $request): array
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
        $inicio = $fim = null;
        $periodoTexto = '';

        switch ($periodo) {
            case 'geral':
                $periodoTexto = "Todos os dados disponíveis";
                $inicio = null;
                $fim = null;
                break;
            case 'hoje':
                $inicio = $hoje; $fim = $hoje;
                $periodoTexto = "Hoje: $hoje";
                break;
            case 'ontem':
                $inicio = $ontem; $fim = $ontem;
                $periodoTexto = "Ontem: $ontem";
                break;
            case 'mes':
                $inicio = $primeiroDoMes; $fim = $hoje;
                $periodoTexto = "Mês: $primeiroDoMes a $hoje";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio; $fim = $ultimoMesFim;
                $periodoTexto = "Último Mês: $ultimoMesInicio a $ultimoMesFim";
                break;
            case 'ano':
                $inicio = $primeiroDoAno; $fim = $hoje;
                $periodoTexto = "Ano: $primeiroDoAno a $hoje";
                break;
            case 'ultimo_ano':
                $inicio = $ultimoAnoInicio; $fim = $ultimoAnoFim;
                $periodoTexto = "Último Ano: $ultimoAnoInicio a $ultimoAnoFim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
            default:
                $periodoTexto = "Todos os dados disponíveis";
                $inicio = null; $fim = null;
                break;
        }

        return [$inicio, $fim, $periodoTexto];
    }

    /**
     * Devolve os dois top5 (vendas e euros)
     */
    private function obterTop5(Request $request): array
    {
        [$inicio, $fim, $periodoTexto] = $this->resolverPeriodo($request);

        $ranking = $this->clientes($inicio, $fim);
        if ($ranking === null) {
            return [null, null, $periodoTexto];
        }

        $top5Vendas = $ranking->sortByDesc('num_vendas')->take(5)->values();
        $top5Euros  = $ranking->sortByDesc('total_euros')->take(5)->values();

        return [$top5Vendas, $top5Euros, $periodoTexto];
    }

    public function topClientes(Request $request)
    {
        [$top5Vendas, $top5Euros, $periodoTexto] = $this->obterTop5($request);

        if ($top5Vendas === null) {
            return redirect()->route('login')->withErrors(['error' => 'Por favor, faça login novamente.']);
        }

        return view('clientes.topClientes', [
            'top5ClientesVendas' => $top5Vendas,
            'top5ClientesEuros' => $top5Euros,
            'periodoTexto' => $periodoTexto,
        ]);
    }

    /**
     * Exporta PDF do modo atual (qtd ou euros)
     */
    public function exportPdf(Request $request)
{
    [$top5Vendas, $top5Euros, $periodoTexto] = $this->obterTop5($request);
    if ($top5Vendas === null) {
        return redirect()->route('login')->withErrors(['error' => 'Por favor, faça login novamente.']);
    }

    $modo = $request->input('mode', 'qtd'); // 'qtd' ou 'euros'
    $chartImg = $request->input('chart_img');

    if (!$chartImg || !\Illuminate\Support\Str::startsWith($chartImg, 'data:image')) {
        return back()->withErrors(['error' => 'Não foi possível obter a imagem do gráfico para exportação.']);
    }

    $dados = ($modo === 'euros') ? $top5Euros : $top5Vendas;

    $titulo = ($modo === 'euros')
        ? 'Top 5 Clientes — Total (€)'
        : 'Top 5 Clientes — Nº Vendas';

    $modoTexto = ($modo === 'euros') ? 'Total (€)' : 'Nº Vendas';

    $pdf = Pdf::loadView('exports.clientes_top5_pdf', [
        'titulo' => $titulo,
        'periodoTexto' => $periodoTexto,
        'modoTexto' => $modoTexto,
        'clientes' => $dados,
        'chartImg' => $chartImg,
        'geradoEm' => now(),
    ])->setPaper('a4', 'portrait');

    $nome = ($modo === 'euros')
        ? 'top_5_clientes_total_euros.pdf'
        : 'top_5_clientes_num_vendas.pdf';

    return $pdf->download($nome);
    }

    /**
 * Export CSV (dados da tabela) — respeita o mode e o filtro
 * mode = qtd | euros
 */
    public function exportCsv(Request $request)
    {
        [$inicio, $fim, $periodoTexto] = $this->resolverPeriodo($request);

        $mode = $request->input('mode', 'qtd'); // qtd ou euros

        $ranking = $this->clientes($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5 = ($mode === 'euros')
            ? $ranking->sortByDesc('total_euros')->take(5)->values()
            : $ranking->sortByDesc('num_vendas')->take(5)->values();

        $filename = ($mode === 'euros')
            ? 'top_5_clientes_euros.csv'
            : 'top_5_clientes_qtd.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($top5, $periodoTexto, $mode) {
            $out = fopen('php://output', 'w');

            // BOM para Excel abrir UTF-8 corretamente
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalho "meta"
            fputcsv($out, ['Relatório', 'Top 5 Clientes'], ';');
            fputcsv($out, ['Período', $periodoTexto], ';');
            fputcsv($out, ['Modo', $mode], ';');
            fputcsv($out, [], ';');

            // Cabeçalhos da tabela
            fputcsv($out, ['#', 'Cliente', 'NIF', 'Nº Vendas', 'Total (€)'], ';');

            $i = 1;
            foreach ($top5 as $c) {
                fputcsv($out, [
                    $i++,
                    $c['cliente'] ?? '',
                    $c['nif'] ?? '',
                    (int)($c['num_vendas'] ?? 0),
                    number_format((float)($c['total_euros'] ?? 0), 2, ',', ''),
                ], ';');
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
