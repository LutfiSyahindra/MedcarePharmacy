<?php

namespace App\Http\Controllers\Medcare\Menu\AnalisisProfitabilitas;

use App\Http\Controllers\Controller;
use App\Services\Menu\AnalisisProfitabilitas\ProfitabilityAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfitabilityAnalysisController extends Controller
{
    public function __construct(private readonly ProfitabilityAnalysisService $analysis) {}

    public function index(): View
    {
        return view('medcare.menu.analisisProfitabilitas.index');
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'analysis' => $this->analysis->build($request->user(), $this->filters($request)),
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => ['nullable', Rule::in(ProfitabilityAnalysisService::SORTS)],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'top' => ['nullable', Rule::in(['10', '25', '50', 'all'])],
        ]);

        $end = isset($validated['date_end'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_end'])->endOfDay()
            : today()->endOfDay();
        $start = isset($validated['date_start'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_start'])->startOfDay()
            : $end->copy()->subDays(29)->startOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages(['date_end' => 'Tanggal akhir harus setelah tanggal mulai.']);
        }

        if ($start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['date_end' => 'Rentang analisis maksimal 366 hari.']);
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'start' => $start,
            'end' => $end,
            'period_days' => $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1,
            'search' => trim((string) ($validated['search'] ?? '')),
            'sort' => (string) ($validated['sort'] ?? 'gross_profit'),
            'direction' => (string) ($validated['direction'] ?? 'desc'),
            'top' => (string) ($validated['top'] ?? '25'),
        ];
    }
}
