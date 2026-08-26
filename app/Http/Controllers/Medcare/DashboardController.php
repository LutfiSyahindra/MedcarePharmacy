<?php

namespace App\Http\Controllers\Medcare;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardCommandCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardCommandCenterService $dashboard) {}

    public function index(Request $request): View
    {
        return view('medcare.dashboard', [
            'dashboard' => $this->dashboard->build($request->user(), $this->filters($request)),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'dashboard' => $this->dashboard->build($request->user(), $this->filters($request)),
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['today', '7', '30', '90', 'mtd', 'custom'])],
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d', Rule::requiredIf($request->input('period') === 'custom')],
            'date_end' => [
                'nullable',
                'date_format:Y-m-d',
                Rule::requiredIf($request->input('period') === 'custom'),
                Rule::when($request->filled('date_start'), ['after_or_equal:date_start']),
            ],
        ]);

        $period = (string) ($validated['period'] ?? '30');
        $today = today();

        [$start, $end] = match ($period) {
            'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            '7' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            '90' => [$today->copy()->subDays(89)->startOfDay(), $today->copy()->endOfDay()],
            'mtd' => [$today->copy()->startOfMonth()->startOfDay(), $today->copy()->endOfDay()],
            'custom' => [
                Carbon::createFromFormat('Y-m-d', $validated['date_start'])->startOfDay(),
                Carbon::createFromFormat('Y-m-d', $validated['date_end'])->endOfDay(),
            ],
            default => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay()],
        };

        if ($start->diffInDays($end) > 365) {
            abort(422, 'Rentang dashboard maksimal 366 hari.');
        }

        return [
            'period' => $period,
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'start' => $start,
            'end' => $end,
        ];
    }
}
