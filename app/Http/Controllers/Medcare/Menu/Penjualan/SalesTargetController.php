<?php

namespace App\Http\Controllers\Medcare\Menu\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Menu\Analisis\OmzetTargetModel;
use App\Services\Menu\Penjualan\SalesTargetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesTargetController extends Controller
{
    public function __construct(private readonly SalesTargetService $targets) {}

    public function index(Request $request): View
    {
        $branches = $this->targets->branches($request->user());
        $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
        $defaultBranchId = $branches->contains('id', $activeBranchId)
            ? $activeBranchId
            : (int) ($branches->first()?->id ?? 0);

        return view('medcare.menu.penjualan.salesTarget.index', [
            'branches' => $branches,
            'defaultBranchId' => $defaultBranchId ?: null,
            'defaultYear' => (int) now()->year,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'branch_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'status' => 'success',
            'dashboard' => $this->targets->dashboard(
                $request->user(),
                (int) ($validated['year'] ?? now()->year),
                isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'period' => ['required', 'date_format:Y-m'],
            'target_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
        ]);
        $target = $this->targets->save(
            $request->user(),
            (int) $validated['branch_id'],
            $validated['period'],
            (float) $validated['target_amount'],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Target penjualan bulanan berhasil disimpan.',
            'target' => [
                'id' => (int) $target->id,
                'branch_id' => (int) $target->branch_id,
                'branch_name' => $target->branch?->name,
                'period' => $target->period_start->format('Y-m'),
                'amount' => (float) $target->target_amount,
            ],
        ]);
    }

    public function destroy(Request $request, OmzetTargetModel $target): JsonResponse
    {
        $this->targets->delete($request->user(), $target);

        return response()->json([
            'status' => 'success',
            'message' => 'Target penjualan bulanan berhasil dihapus.',
        ]);
    }
}
