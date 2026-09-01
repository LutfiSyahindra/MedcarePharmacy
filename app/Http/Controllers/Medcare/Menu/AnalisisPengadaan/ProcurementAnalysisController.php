<?php

namespace App\Http\Controllers\Medcare\Menu\AnalisisPengadaan;

use App\Http\Controllers\Controller;
use App\Services\Menu\AnalisisPengadaan\ProcurementAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProcurementAnalysisController extends Controller
{
    public function __construct(private readonly ProcurementAnalysisService $analysis) {}

    public function index(): View
    {
        return view('medcare.menu.analisisPengadaan.index', [
            'defaultDateStart' => today()->subDays(29)->toDateString(),
            'defaultDateEnd' => today()->toDateString(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'analysis' => $this->analysis->build($request->user(), $this->filters($request)),
        ]);
    }

    public function medicine(Request $request, int $medicine): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'medicine' => $this->analysis->medicineDetail(
                $request->user(),
                $medicine,
                $this->filters($request),
            ),
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
            'supplier_id' => ['nullable', 'integer'],
            'medicine_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'golongan_id' => ['nullable', 'integer'],
            'manufacturer_id' => ['nullable', 'integer'],
            'po_status' => ['nullable', Rule::in(ProcurementAnalysisService::PO_STATUSES)],
            'receipt_status' => ['nullable', Rule::in(ProcurementAnalysisService::RECEIPT_STATUSES)],
            'created_by' => ['nullable', 'integer'],
            'granularity' => ['nullable', Rule::in(['day', 'week', 'month', 'year'])],
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
        if ($start->diffInDays($end) > 3660) {
            throw ValidationException::withMessages(['date_end' => 'Rentang analisis maksimal 10 tahun.']);
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'start' => $start,
            'end' => $end,
            'supplier_id' => isset($validated['supplier_id']) ? (int) $validated['supplier_id'] : null,
            'medicine_id' => isset($validated['medicine_id']) ? (int) $validated['medicine_id'] : null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'golongan_id' => isset($validated['golongan_id']) ? (int) $validated['golongan_id'] : null,
            'manufacturer_id' => isset($validated['manufacturer_id']) ? (int) $validated['manufacturer_id'] : null,
            'po_status' => $validated['po_status'] ?? null,
            'receipt_status' => $validated['receipt_status'] ?? null,
            'created_by' => isset($validated['created_by']) ? (int) $validated['created_by'] : null,
            'granularity' => $validated['granularity'] ?? 'day',
        ];
    }
}
