<?php

namespace App\Http\Controllers\Medcare\Menu\AnalisisOmzet;

use App\Exports\Menu\AnalisisOmzet\RevenueAnalysisExport;
use App\Http\Controllers\Controller;
use App\Services\Menu\AnalisisOmzet\RevenueAnalysisService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class RevenueAnalysisController extends Controller
{
    public function __construct(private readonly RevenueAnalysisService $analysis) {}

    public function index(): View
    {
        return view('medcare.menu.analisisOmzet.index', [
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

    public function storeTarget(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'date_start' => ['required', 'date_format:Y-m-d'],
            'date_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:date_start'],
            'target_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
        ]);
        $start = Carbon::createFromFormat('Y-m-d', $validated['date_start'])->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $validated['date_end'])->endOfDay();

        if ($start->diffInDays($end) > 3660) {
            throw ValidationException::withMessages(['date_end' => 'Rentang target maksimal 10 tahun.']);
        }

        $target = $this->analysis->saveTarget(
            $request->user(),
            (int) $validated['branch_id'],
            $start,
            $end,
            (float) $validated['target_amount'],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Target omzet berhasil disimpan untuk periode aktif.',
            'target' => [
                'id' => (int) $target->id,
                'amount' => (float) $target->target_amount,
            ],
        ]);
    }

    public function excel(Request $request)
    {
        $filters = $this->filters($request);
        $analysis = $this->analysis->build($request->user(), $filters, false);
        $filename = 'analisis-penjualan-'.$filters['start']->format('Ymd').'-'.$filters['end']->format('Ymd').'.xlsx';

        return Excel::download(new RevenueAnalysisExport($analysis), $filename);
    }

    public function pdf(Request $request): Response
    {
        $filters = $this->filters($request);
        $analysis = $this->analysis->build($request->user(), $filters, false);
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('medcare.menu.analisisOmzet.pdf', compact('analysis'))->render());
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'analisis-penjualan-'.$filters['start']->format('Ymd').'-'.$filters['end']->format('Ymd').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
            'cashier_id' => ['nullable', 'integer'],
            'shift_id' => ['nullable', 'integer'],
            'medicine_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'golongan_id' => ['nullable', 'integer'],
            'transaction_type' => ['nullable', Rule::in(array_keys(\App\Services\Menu\Penjualan\PenjualanPosService::TRANSACTION_TYPES))],
            'payment_method' => ['nullable', Rule::in(array_keys(\App\Services\Menu\Penjualan\PenjualanPosService::PAYMENT_METHODS))],
            'granularity' => ['nullable', Rule::in(['day', 'week', 'month', 'year'])],
            'top' => ['nullable', Rule::in(['10', '20', 'all', 10, 20])],
            'product_metric' => ['nullable', Rule::in(['revenue', 'qty', 'transactions'])],
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
            'cashier_id' => isset($validated['cashier_id']) ? (int) $validated['cashier_id'] : null,
            'shift_id' => isset($validated['shift_id']) ? (int) $validated['shift_id'] : null,
            'medicine_id' => isset($validated['medicine_id']) ? (int) $validated['medicine_id'] : null,
            'category_id' => isset($validated['category_id']) ? (int) $validated['category_id'] : null,
            'golongan_id' => isset($validated['golongan_id']) ? (int) $validated['golongan_id'] : null,
            'transaction_type' => $validated['transaction_type'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'granularity' => $validated['granularity'] ?? 'day',
            'top' => (string) ($validated['top'] ?? '10'),
            'product_metric' => $validated['product_metric'] ?? 'revenue',
        ];
    }
}
