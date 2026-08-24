<?php

namespace App\Http\Middleware;

use App\Support\StockOpnameAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStockMenusAreAvailable
{
    public function __construct(private readonly StockOpnameAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $opname = $this->access->activeLock($request->user());

        if (! $opname) {
            return $next($request);
        }

        if ($this->access->userCanViewStockDuringOpname($request->user())
            && in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $request->attributes->set('stock_opname_read_only', true);
            $request->attributes->set('active_stock_opname', $opname);

            return $next($request);
        }

        $message = 'Menu Stok dan Kartu Stok dikunci selama penghitungan fisik '.$opname->nomor
            .' di '.$opname->branch?->name.'. Modul otomatis dibuka setelah stok fisik pertama kali disubmit.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'stock_opname_id' => $opname->id,
                'redirect' => route('stockOpname.index'),
            ], 423);
        }

        return redirect()
            ->route('stockOpname.index')
            ->with('stock_menu_locked', $message);
    }
}
