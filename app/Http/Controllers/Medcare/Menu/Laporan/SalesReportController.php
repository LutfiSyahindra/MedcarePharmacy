<?php

namespace App\Http\Controllers\Medcare\Menu\Laporan;

use App\Http\Controllers\Controller;
use App\Services\Menu\Laporan\SalesReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function __construct(private readonly SalesReportService $reports) {}

    public function index(string $report = 'ringkasan'): View
    {
        return view('medcare.menu.laporan.penjualan.index', [
            'reportType' => $report,
            'reportDefinition' => $this->reports->definition($report),
            'reportTypes' => SalesReportService::TYPES,
        ]);
    }

    public function data(Request $request, string $report): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'report' => $this->reports->build($request->user(), $report, $this->filters($request)),
        ]);
    }

    private function filters(Request $request): array
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

        $end = isset($validated['date_end']) ? Carbon::parse($validated['date_end'])->startOfDay() : today();
        $start = isset($validated['date_start']) ? Carbon::parse($validated['date_start'])->startOfDay() : $end->copy()->subDays(29);

        if ($start->diffInDays($end) > 365) {
            throw ValidationException::withMessages([
                'date_start' => 'Rentang laporan maksimal 366 hari.',
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
