<?php

namespace App\Http\Middleware;

use App\Services\Settings\Auth\RoleSettingService;
use App\Support\StockOpnameAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCashierIsAvailable
{
    public function __construct(
        private readonly StockOpnameAccess $access,
        private readonly RoleSettingService $roleSettings
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $requestedBranch = $request->input('branch_id');
        $branchIds = $requestedBranch
            ? [(int) $requestedBranch]
            : $this->roleSettings->posBranchIds($request->user(), true);

        // Pengguna multi-cabang tetap boleh membuka layar POS untuk memilih cabang.
        if (! $requestedBranch && count($branchIds) > 1) {
            return $next($request);
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
