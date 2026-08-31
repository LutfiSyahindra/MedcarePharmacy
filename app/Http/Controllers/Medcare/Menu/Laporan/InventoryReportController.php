<?php

namespace App\Http\Controllers\Medcare\Menu\Laporan;

use App\Http\Controllers\Controller;
use App\Services\Menu\Laporan\InventoryReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    public function __construct(private readonly InventoryReportService $reports) {}

    public function index(string $report = 'posisi-stok'): View
    {
        $period = $this->reports->defaultPeriod($report);

        return view('medcare.menu.laporan.penjualan.index', [
            'reportType' => $report,
            'reportDefinition' => $this->reports->definition($report),
            'reportTypes' => InventoryReportService::TYPES,
            'defaultDateStart' => $period['start']->toDateString(),
            'defaultDateEnd' => $period['end']->toDateString(),
            'reportModule' => [
                'key' => 'persediaan',
                'data_route' => 'laporan.persediaan.data',
                'index_route' => 'laporan.persediaan.index',
                'eyebrow' => 'Inventory Intelligence',
                'switcher_title' => 'Jelajahi laporan persediaan',
                'nav_label' => 'Jenis laporan persediaan',
                'summary_loading' => 'Menghitung posisi dan nilai persediaan...',
                'chart_title' => 'Profil persediaan',
                'chart_aria' => 'Grafik laporan persediaan',
                'chart_loading' => 'Menyusun visual persediaan...',
                'health_eyebrow' => 'Kesehatan persediaan',
                'health_title' => 'Nilai stok aktif',
                'health_copy' => 'nilai stok berisiko terhadap total modal persediaan.',
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

        if ($start->diffInDays($end) > 3653) {
            throw ValidationException::withMessages([
                'date_start' => 'Rentang laporan persediaan maksimal 10 tahun.',
            ]);
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
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
