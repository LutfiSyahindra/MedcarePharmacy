<?php

namespace App\Http\Controllers\Medcare\Menu\Laporan;

use App\Http\Controllers\Controller;
use App\Services\Menu\Laporan\PurchaseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseReportController extends Controller
{
    public function __construct(private readonly PurchaseReportService $reports) {}

    public function index(string $report = 'pembelian'): View
    {
        $period = $this->reports->defaultPeriod($report);

        return view('medcare.menu.laporan.penjualan.index', [
            'reportType' => $report,
            'reportDefinition' => $this->reports->definition($report),
            'reportTypes' => PurchaseReportService::TYPES,
            'defaultDateStart' => $period['start']->toDateString(),
            'defaultDateEnd' => $period['end']->toDateString(),
            'showSupplierFilter' => true,
            'reportModule' => [
                'key' => 'pembelian',
                'data_route' => 'laporan.pembelian.data',
                'index_route' => 'laporan.pembelian.index',
                'eyebrow' => 'Procurement Intelligence',
                'switcher_title' => 'Jelajahi laporan pembelian',
                'nav_label' => 'Jenis laporan pembelian',
                'summary_loading' => 'Menghitung data pembelian...',
                'chart_title' => 'Tren pembelian',
                'chart_aria' => 'Grafik tren laporan pembelian',
                'chart_loading' => 'Menyusun visual pembelian...',
                'health_eyebrow' => 'Kesehatan pembelian',
                'health_title' => 'Setelah retur',
                'health_copy' => 'rasio retur terhadap pembelian aktual periode aktif.',
                'analysis_url' => route('analisisPengadaan.index'),
                'basis_label' => 'PO: estimasi · Pembelian: penerimaan posted',
            ],
        ]);
    }

    public function data(Request $request, string $report): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'report' => $this->reports->build($request->user(), $report, $this->filters($request, $report)),
        ]);
    }

    private function filters(Request $request, string $report): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'supplier_id' => ['nullable', 'integer', 'exists:distributors,id'],
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date', 'after_or_equal:date_start'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', 'string', 'max:80'],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', Rule::in([10, 25, 50, 100])],
        ]);

        $default = $this->reports->defaultPeriod($report);
        $start = isset($validated['date_start'])
            ? Carbon::parse($validated['date_start'])->startOfDay()
            : $default['start'];
        $end = isset($validated['date_end'])
            ? Carbon::parse($validated['date_end'])->startOfDay()
            : $default['end'];

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'date_end' => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            ]);
        }

        if ($start->diffInDays($end) > 731) {
            throw ValidationException::withMessages([
                'date_start' => 'Rentang laporan pembelian maksimal 732 hari.',
            ]);
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'supplier_id' => isset($validated['supplier_id']) ? (int) $validated['supplier_id'] : null,
            'start' => $start,
            'end' => $end,
            'search' => trim((string) ($validated['search'] ?? '')),
            'sort' => $validated['sort'] ?? null,
            'direction' => $validated['direction'] ?? null,
            'page' => (int) ($validated['page'] ?? 1),
            'per_page' => (int) ($validated['per_page'] ?? 25),
        ];
    }
}
