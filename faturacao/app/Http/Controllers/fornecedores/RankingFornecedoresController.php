<?php

namespace App\Http\Controllers\fornecedores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RankingFornecedoresController extends Controller
{
    private function fornecedores($inicio = null, $fim = null)
    {
        if (!session()->has('user.token')) {
            return null;
        }

        $token = session('user.token');

        // (Opcional mas recomendado) validar token
        if (!$this->validateToken($token)) {
            return null;
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->get('https://api.gesfaturacao.pt/api/v1.0.4/purchases/invoices');

        if (!$response->successful()) {
            return null;
        }

        $invoices = $response->json();
        $data = $invoices['data'] ?? $invoices;

        $ranking = [];
        foreach ($data as $invoice) {
            if (empty($invoice['supplier']['id']) || empty($invoice['supplier']['name'])) {
                continue;
            }
            if (empty($invoice['status']['id']) || intval($invoice['status']['id']) !== 2) {
                continue;
            }

            $data_fatura = $invoice['date'] ?? null;
            if ($inicio && $fim && $data_fatura) {
                if ($data_fatura < $inicio || $data_fatura > $fim) continue;
            }

            $supplierId = $invoice['supplier']['id'];
            $supplierName = $invoice['supplier']['name'];
            $vatNumber = $invoice['supplier']['vatNumber'] ?? '';
            $total = (float)($invoice['total'] ?? 0);

            if (!isset($ranking[$supplierId])) {
                $ranking[$supplierId] = [
                    'id' => $supplierId,
                    'fornecedor' => $supplierName,
                    'nif' => $vatNumber,
                    'total_euros' => 0.0,
                    'num_compras' => 0,
                ];
            }

            $ranking[$supplierId]['total_euros'] += $total;
            $ranking[$supplierId]['num_compras'] += 1;
        }

        return collect($ranking);
    }

    private function validateToken($token): bool
    {
        $response = Http::withHeaders([
            'Authorization' => $token,
            'Accept' => 'application/json',
        ])->post('https://api.gesfaturacao.pt/api/v1.0.4/validate-token', []);

        return $response->successful();
    }

    /**
     * Centraliza a lógica do período (para não duplicar no export)
     */
    private function getPeriodoInfo(Request $request): array
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
                $periodoTexto = "Mês: $primeiroDoMes a $hoje";
                break;
            case 'ultimo_mes':
                $inicio = $ultimoMesInicio;
                $fim = $ultimoMesFim;
                $periodoTexto = "Último Mês: $ultimoMesInicio a $ultimoMesFim";
                break;
            case 'ano':
                $inicio = $primeiroDoAno;
                $fim = $hoje;
                $periodoTexto = "Ano: $primeiroDoAno a $hoje";
                break;
            case 'ultimo_ano':
                $inicio = $ultimoAnoInicio;
                $fim = $ultimoAnoFim;
                $periodoTexto = "Último Ano: $ultimoAnoInicio a $ultimoAnoFim";
                break;
            case 'personalizado':
                $inicio = $request->input('data_inicio');
                $fim = $request->input('data_fim');
                $periodoTexto = "Personalizado: $inicio a $fim";
                break;
        }

        return [$inicio, $fim, $periodoTexto, $periodo];
    }

    public function topFornecedores(Request $request)
    {
        [$inicio, $fim, $periodoTexto] = $this->getPeriodoInfo($request);

        $ranking = $this->fornecedores($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5Qtd = $ranking->sortByDesc('num_compras')->take(5)->values();
        $top5Euros = $ranking->sortByDesc('total_euros')->take(5)->values();

        return view('fornecedores.topFornecedores', [
            'top5FornecedoresQtd' => $top5Qtd,
            'top5FornecedoresEuros' => $top5Euros,
            'periodoTexto' => $periodoTexto,
        ]);
    }

    /**
     * Export PDF (com imagem do gráfico enviada do browser)
     * mode = qtd | euros
     */
    public function exportPdf(Request $request)
    {
        [$inicio, $fim, $periodoTexto] = $this->getPeriodoInfo($request);

        $mode = $request->input('mode', 'qtd');
        $chartImg = $request->input('chart_img');

        if (!$chartImg) {
            return redirect()->back()->withErrors(['error' => 'Imagem do gráfico não foi enviada.']);
        }

        $ranking = $this->fornecedores($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5 = ($mode === 'euros')
            ? $ranking->sortByDesc('total_euros')->take(5)->values()
            : $ranking->sortByDesc('num_compras')->take(5)->values();

        $tituloModo = ($mode === 'euros') ? '€ (Total)' : 'Qtd (Nº Compras)';

        $pdf = Pdf::loadView('exports.fornecedores_top5_pdf', [
            'top5' => $top5,
            'periodoTexto' => $periodoTexto,
            'mode' => $mode,
            'tituloModo' => $tituloModo,
            'chartImg' => $chartImg,
            'geradoEm' => now()->format('Y-m-d H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = ($mode === 'euros')
            ? 'top_5_fornecedores_euros.pdf'
            : 'top_5_fornecedores_qtd.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export CSV (dados da tabela) — respeita o mode e o filtro
     * mode = qtd | euros
     */
    public function exportCsv(Request $request)
    {
        [$inicio, $fim, $periodoTexto] = $this->getPeriodoInfo($request);

        $mode = $request->input('mode', 'qtd');

        $ranking = $this->fornecedores($inicio, $fim);
        if ($ranking === null) {
            return redirect()->route('login');
        }

        $top5 = ($mode === 'euros')
            ? $ranking->sortByDesc('total_euros')->take(5)->values()
            : $ranking->sortByDesc('num_compras')->take(5)->values();

        $filename = ($mode === 'euros')
            ? 'top_5_fornecedores_euros.csv'
            : 'top_5_fornecedores_qtd.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($top5, $periodoTexto, $mode) {
            $out = fopen('php://output', 'w');

            // BOM para Excel (PT) abrir bem UTF-8
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Cabeçalho "meta"
            fputcsv($out, ['Relatório', 'Top 5 Fornecedores'], ';');
            fputcsv($out, ['Período', $periodoTexto], ';');
            fputcsv($out, ['Modo', $mode], ';');
            fputcsv($out, [], ';');

            // Cabeçalhos da tabela
            fputcsv($out, ['#', 'Fornecedor', 'NIF', 'Nº Compras', 'Total (€)'], ';');

            $i = 1;
            foreach ($top5 as $f) {
                fputcsv($out, [
                    $i++,
                    $f['fornecedor'] ?? '',
                    $f['nif'] ?? '',
                    (int)($f['num_compras'] ?? 0),
                    number_format((float)($f['total_euros'] ?? 0), 2, ',', ''),
                ], ';');
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
