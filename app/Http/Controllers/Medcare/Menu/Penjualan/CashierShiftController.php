<?php

namespace App\Http\Controllers\Medcare\Menu\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Services\Menu\Penjualan\CashierShiftService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CashierShiftController extends Controller
{
    public function __construct(private readonly CashierShiftService $shiftService) {}

    public function index(Request $request)
    {
        return view('medcare.menu.penjualan.pos.shifts', [
            'branches' => $this->shiftService->accessibleBranches($request->user()),
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
        ]);
        $branch = $this->shiftService->accessibleBranch((int) $validated['branch_id'], $request->user());
        $shift = $this->shiftService->currentShift($request->user(), $branch->id);

        return response()->json([
            'branch_id' => $branch->id,
            'operational' => $this->shiftService->operationalState($branch),
            'shift' => $this->shiftService->payload($shift),
            'movements' => $this->movementPayloads($shift, true),
        ]);
    }

    public function open(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'opening_amount' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $shift = $this->shiftService->openShift(
            (int) $validated['branch_id'],
            (float) $validated['opening_amount'],
            $validated['opening_notes'] ?? null,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Kasir berhasil dibuka. Shift '.$shift->shift_number.' aktif.',
            'shift' => $this->shiftService->payload($shift),
            'movements' => [],
        ]);
    }

    public function movement(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['cash_in', 'cash_out'])],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'description' => ['required', 'string', 'max:500'],
        ]);
        $shift = $this->shiftService->addMovement(
            (int) $validated['branch_id'],
            $validated['type'],
            (float) $validated['amount'],
            $validated['description'],
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => $validated['type'] === 'cash_in' ? 'Kas masuk berhasil dicatat.' : 'Kas keluar berhasil dicatat.',
            'shift' => $this->shiftService->payload($shift),
            'movements' => $this->movementPayloads($shift, true),
        ]);
    }

    public function close(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'actual_cash' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'closing_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $shift = $this->shiftService->closeShift(
            (int) $validated['branch_id'],
            (float) $validated['actual_cash'],
            $validated['closing_notes'] ?? null,
            $request->user()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Kasir berhasil ditutup. Selisih kas telah dihitung.',
            'shift' => $this->shiftService->payload($shift),
        ]);
    }

    public function table(Request $request)
    {
        $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in(['open', 'closed'])],
            'date_start' => ['nullable', 'date'],
            'date_end' => ['nullable', 'date', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
        ]);

        $query = $this->shiftService->historyQuery($request->user())
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', (int) $request->branch_id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('date_start'), fn ($query) => $query->whereDate('opened_at', '>=', $request->date_start))
            ->when($request->filled('date_end'), fn ($query) => $query->whereDate('opened_at', '<=', $request->date_end));

        $metrics = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'closed' THEN cash_difference ELSE 0 END), 0) as cash_difference")
            ->first();

        $query->latest('opened_at')->latest('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('cash_summary', fn (CashierShiftModel $shift) => $this->shiftService->summary($shift))
            ->addColumn('branch_name', fn (CashierShiftModel $shift) => $shift->branch?->name ?? '-')
            ->addColumn('cashier_name', fn (CashierShiftModel $shift) => $shift->user?->name ?? '-')
            ->addColumn('status_label', fn (CashierShiftModel $shift) => $shift->status === 'open' ? 'Aktif' : 'Ditutup')
            ->addColumn('opened_at_label', fn (CashierShiftModel $shift) => $this->localTime($shift, $shift->opened_at))
            ->addColumn('closed_at_label', fn (CashierShiftModel $shift) => $this->localTime($shift, $shift->closed_at))
            ->addColumn('duration_label', function (CashierShiftModel $shift) {
                $end = $shift->closed_at ?: now();
                $minutes = $shift->opened_at ? (int) $shift->opened_at->diffInMinutes($end) : 0;

                return intdiv($minutes, 60).'j '.($minutes % 60).'m';
            })
            ->addColumn('detail_url', fn (CashierShiftModel $shift) => route('penjualan.pos.shifts.show', $shift->id))
            ->with([
                'summary' => [
                    'total' => (int) ($metrics->total ?? 0),
                    'open' => (int) ($metrics->open_count ?? 0),
                    'closed' => (int) ($metrics->closed_count ?? 0),
                    'cash_difference' => (float) ($metrics->cash_difference ?? 0),
                ],
            ])
            ->make(true);
    }

    public function show(Request $request, CashierShiftModel $shift)
    {
        abort_unless(
            $this->shiftService->historyQuery($request->user())->whereKey($shift->id)->exists(),
            404
        );
        $shift->load(['branch', 'user']);

        return response()->json([
            'shift' => $this->shiftService->payload($shift),
            'movements' => $this->movementPayloads($shift),
        ]);
    }

    private function movementPayloads(?CashierShiftModel $shift, bool $latestFirst = false): array
    {
        if (! $shift) {
            return [];
        }

        $shift->loadMissing('branch');
        $direction = $latestFirst ? 'desc' : 'asc';
        $timezone = $shift->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE;

        return $shift->movements()
            ->with('createdBy')
            ->orderBy('occurred_at', $direction)
            ->orderBy('id', $direction)
            ->get()
            ->map(function ($movement) use ($timezone) {
                $occurredAt = $movement->occurred_at?->copy()->setTimezone($timezone);

                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'type_label' => $movement->type === 'cash_in' ? 'Kas Masuk' : 'Kas Keluar',
                    'amount' => (float) $movement->amount,
                    'description' => $movement->description,
                    'created_by' => $movement->createdBy?->name ?? '-',
                    'occurred_at' => $occurredAt?->format('Y-m-d H:i:s'),
                    'occurred_at_label' => $occurredAt?->format('d/m/Y · H:i'),
                ];
            })
            ->values()
            ->all();
    }

    private function localTime(CashierShiftModel $shift, $date): string
    {
        if (! $date) {
            return '-';
        }

        return $date->copy()
            ->setTimezone($shift->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE)
            ->format('d/m/Y H:i');
    }
}
