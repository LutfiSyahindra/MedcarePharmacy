<?php

namespace App\Http\Middleware;

use App\Models\BranchModel;
use App\Services\Menu\Penjualan\CashierShiftService;
use App\Services\Settings\Auth\RoleSettingService;
use App\Support\StockOpnameAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCashierIsAvailable
{
    public function __construct(
        private readonly StockOpnameAccess $access,
        private readonly RoleSettingService $roleSettings,
        private readonly CashierShiftService $shiftService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requestedBranch = $request->input('branch_id');
        $allowedBranchIds = $this->roleSettings->posBranchIds($request->user(), true);
        $branchIds = $requestedBranch
            ? (in_array((int) $requestedBranch, $allowedBranchIds, true) ? [(int) $requestedBranch] : [])
            : $allowedBranchIds;

        // Pengguna multi-cabang tetap boleh membuka layar POS untuk memilih cabang.
        if (! $requestedBranch && count($branchIds) > 1) {
            return $next($request);
        }

        $branch = BranchModel::query()->whereIn('id', $branchIds)->first();

        if ($branch) {
            $operational = $this->shiftService->operationalState($branch);

            if (! $operational['is_open']) {
                $message = $this->shiftService->operationalClosureMessage($branch, $operational);

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                        'errors' => ['operational_hours' => [$message]],
                        'branch_id' => $branch->id,
                        'operational' => $operational,
                        'redirect' => route('penjualan.pos.shifts'),
                    ], 423);
                }

                return redirect()->route('penjualan.pos.shifts')->with('cashier_closed', $message);
            }
        }

        $opname = $this->access->activeLockForBranches($branchIds);

        if (! $opname) {
            return $next($request);
        }

        $message = 'Kasir/POS '.$opname->branch?->name.' dikunci selama penghitungan fisik '
            .$opname->nomor.'. Stok tidak ditampilkan dan transaksi dapat dilanjutkan setelah hasil fisik disubmit.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'stock_opname_id' => $opname->id,
                'branch_id' => $opname->branch_id,
                'redirect' => route('stockOpname.index'),
            ], 423);
        }

        return redirect()->route('stockOpname.index')->with('stock_menu_locked', $message);
    }
}
