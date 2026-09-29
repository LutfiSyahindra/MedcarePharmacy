<?php

namespace App\Http\Controllers\Medcare\Settings\PurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\DistributorModel;
use App\Support\SidebarPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSettingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        $distributors = DistributorModel::query()
            ->orderByDesc('is_active')
            ->orderBy('nama')
            ->get();

        return view('medcare.settings.purchaseOrder.index', [
            'distributors' => $distributors,
            'manualCount' => $distributors->where('uses_manual_po_number', true)->count(),
            'automaticCount' => $distributors->where('uses_manual_po_number', false)->count(),
            'activeCount' => $distributors->where('is_active', true)->count(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeAccess($request);

        $validated = $request->validate([
            'manual_distributor_ids' => ['present', 'array', 'max:5000'],
            'manual_distributor_ids.*' => ['integer', 'distinct', 'exists:distributors,id'],
        ]);
        $manualIds = collect($validated['manual_distributor_ids'])
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values();

        DB::transaction(function () use ($manualIds): void {
            DistributorModel::query()
                ->where('uses_manual_po_number', true)
                ->update(['uses_manual_po_number' => false]);

            if ($manualIds->isNotEmpty()) {
                DistributorModel::query()
                    ->whereKey($manualIds)
                    ->update(['uses_manual_po_number' => true]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Setting nomor PO berhasil disimpan.',
            'summary' => [
                'manual' => $manualIds->count(),
                'automatic' => DistributorModel::query()->where('uses_manual_po_number', false)->count(),
            ],
        ]);
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->can(SidebarPermissions::SETTINGS_PURCHASE_ORDER), 403);
    }
}
