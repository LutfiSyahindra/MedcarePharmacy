<?php

namespace App\Services\Menu\AnalisisProfitabilitas;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProfitabilityMetricsService
{
    /**
     * Build the canonical daily profitability query used by both the operational
     * sales report and the profitability analysis dashboard.
     */
    public function dailyQuery(array $branchIds, Carbon $start, Carbon $end): Builder
    {
        $saleDate = $this->dateExpression('sales.tanggal_transaksi');
        $returnDate = $this->dateExpression('returns.tanggal_retur');

        $saleEvents = $this->sales($branchIds, $start, $end)
            ->leftJoinSub($this->transactionCosts(), 'transaction_costs', 'transaction_costs.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw("{$saleDate} as event_date")
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as gross_sales')
            ->selectRaw('0 as returns_value')
            ->selectRaw('COALESCE(SUM(transaction_costs.hpp), 0) as sales_hpp')
            ->selectRaw('0 as return_hpp')
            ->groupByRaw($saleDate);

        $returnEvents = $this->returns($branchIds, $start, $end)
            ->leftJoinSub($this->returnDocumentCosts(), 'return_costs', 'return_costs.retur_penjualan_id', '=', 'returns.id')
            ->selectRaw("{$returnDate} as event_date")
            ->selectRaw('0 as transactions')
            ->selectRaw('0 as gross_sales')
            ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as returns_value')
            ->selectRaw('0 as sales_hpp')
            ->selectRaw('COALESCE(SUM(return_costs.hpp), 0) as return_hpp')
            ->groupByRaw($returnDate);

        $events = $saleEvents->unionAll($returnEvents);
        $netSales = '(SUM(gross_sales) - SUM(returns_value))';
        $netHpp = '(SUM(sales_hpp) - SUM(return_hpp))';
        $grossProfit = "({$netSales} - {$netHpp})";

        return DB::query()
            ->fromSub($events, 'profit_events')
            ->selectRaw('event_date as sale_date')
            ->selectRaw('SUM(transactions) as transactions')
            ->selectRaw('ROUND(SUM(gross_sales), 2) as gross_sales')
            ->selectRaw('ROUND(SUM(returns_value), 2) as returns_value')
            ->selectRaw("ROUND({$netSales}, 2) as net_sales")
            ->selectRaw('ROUND(SUM(sales_hpp), 2) as sales_hpp')
            ->selectRaw('ROUND(SUM(return_hpp), 2) as return_hpp')
            ->selectRaw("ROUND({$netHpp}, 2) as net_hpp")
            ->selectRaw("ROUND({$grossProfit}, 2) as gross_profit")
            ->selectRaw("CASE WHEN {$netSales} != 0 THEN ROUND(((1.0 * {$grossProfit}) / {$netSales}) * 100, 2) ELSE 0 END as gross_margin")
            ->groupBy('event_date');
    }

    /**
     * Return profitability at product grain. Revenue, transaction discount,
     * returns and batch costs use the same accounting basis as dailyQuery().
     */
    public function productRows(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        $lineWeight = $this->lineWeightExpression();
        $sales = $this->sales($branchIds, $start, $end)
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->detailTotals(), 'detail_totals', 'detail_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->detailCosts(), 'detail_costs', 'detail_costs.penjualan_transaction_detail_id', '=', 'details.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->select('details.obat_id', 'details.kode_obat', 'details.nama_obat')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as sold_qty')
            ->selectRaw("COALESCE(SUM(sales.grand_total * {$lineWeight}), 0) as sales_value")
            ->selectRaw('COALESCE(SUM(detail_costs.hpp), 0) as sales_hpp')
            ->groupBy('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'categories.name')
            ->get()
            ->groupBy(fn ($row) => $this->productKey($row->obat_id, $row->kode_obat, $row->nama_obat))
            ->map(function (Collection $rows) {
                $source = $rows->last();

                return (object) [
                    'obat_id' => $source->obat_id,
                    'kode_obat' => $source->kode_obat,
                    'nama_obat' => $source->nama_obat,
                    'category' => $source->category,
                    'transactions' => (int) $rows->sum('transactions'),
                    'sold_qty' => (float) $rows->sum('sold_qty'),
                    'sales_value' => (float) $rows->sum('sales_value'),
                    'sales_hpp' => (float) $rows->sum('sales_hpp'),
                ];
            });

        $returns = $this->returns($branchIds, $start, $end)
            ->join('retur_penjualan_details as return_details', 'return_details.retur_penjualan_id', '=', 'returns.id')
            ->leftJoinSub($this->returnDetailCosts(), 'return_costs', 'return_costs.retur_penjualan_detail_id', '=', 'return_details.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'return_details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->select('return_details.obat_id', 'return_details.kode_obat', 'return_details.nama_obat')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->selectRaw('COALESCE(SUM(return_details.qty_jual), 0) as return_qty')
            ->selectRaw('COALESCE(SUM(return_details.total), 0) as returns_value')
            ->selectRaw('COALESCE(SUM(return_costs.hpp), 0) as return_hpp')
            ->groupBy('return_details.obat_id', 'return_details.kode_obat', 'return_details.nama_obat', 'categories.name')
            ->get()
            ->groupBy(fn ($row) => $this->productKey($row->obat_id, $row->kode_obat, $row->nama_obat))
            ->map(function (Collection $rows) {
                $source = $rows->last();

                return (object) [
                    'obat_id' => $source->obat_id,
                    'kode_obat' => $source->kode_obat,
                    'nama_obat' => $source->nama_obat,
                    'category' => $source->category,
                    'return_qty' => (float) $rows->sum('return_qty'),
                    'returns_value' => (float) $rows->sum('returns_value'),
                    'return_hpp' => (float) $rows->sum('return_hpp'),
                ];
            });

        return $sales->keys()
            ->merge($returns->keys())
            ->unique()
            ->map(function (string $key) use ($sales, $returns) {
                $sale = $sales->get($key);
                $return = $returns->get($key);
                $source = $sale ?? $return;
                $salesValue = (float) ($sale->sales_value ?? 0);
                $returnsValue = (float) ($return->returns_value ?? 0);
                $salesHpp = (float) ($sale->sales_hpp ?? 0);
                $returnHpp = (float) ($return->return_hpp ?? 0);
                $netSales = $salesValue - $returnsValue;
                $netHpp = $salesHpp - $returnHpp;
                $grossProfit = $netSales - $netHpp;

                return [
                    'key' => $key,
                    'id' => $source->obat_id === null ? null : (int) $source->obat_id,
                    'code' => $source->kode_obat ?: '-',
                    'name' => $source->nama_obat ?: 'Produk tanpa nama',
                    'category' => $source->category ?: 'Tanpa kategori',
                    'transactions' => (int) ($sale->transactions ?? 0),
                    'sold_qty' => round((float) ($sale->sold_qty ?? 0), 2),
                    'return_qty' => round((float) ($return->return_qty ?? 0), 2),
                    'net_qty' => round((float) ($sale->sold_qty ?? 0) - (float) ($return->return_qty ?? 0), 2),
                    'sales_value' => round($salesValue, 2),
                    'returns_value' => round($returnsValue, 2),
                    'net_sales' => round($netSales, 2),
                    'sales_hpp' => round($salesHpp, 2),
                    'return_hpp' => round($returnHpp, 2),
                    'net_hpp' => round($netHpp, 2),
                    'gross_profit' => round($grossProfit, 2),
                    'gross_margin' => $netSales != 0.0 ? round(($grossProfit / $netSales) * 100, 2) : null,
                ];
            })
            ->values();
    }

    /**
     * Calculate simple average inventory investment at cost from the last batch
     * ledger balance immediately before the period and at the end of the period.
     */
    public function inventoryInvestment(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        $opening = $this->inventoryAt($branchIds, $start->copy()->startOfDay(), false);
        $closing = $this->inventoryAt($branchIds, $end->copy()->endOfDay(), true);

        return $opening->keys()
            ->merge($closing->keys())
            ->unique()
            ->mapWithKeys(function ($medicineId) use ($opening, $closing) {
                $openingValue = round((float) $opening->get($medicineId, 0), 2);
                $closingValue = round((float) $closing->get($medicineId, 0), 2);

                return [(int) $medicineId => [
                    'opening_inventory_cost' => $openingValue,
                    'closing_inventory_cost' => $closingValue,
                    'average_inventory_cost' => round(($openingValue + $closingValue) / 2, 2),
                ]];
            });
    }

    private function inventoryAt(array $branchIds, Carbon $boundary, bool $inclusive): Collection
    {
        $operator = $inclusive ? '<=' : '<';
        $branchIds = $branchIds === [] ? [-1] : $branchIds;

        return DB::table('kartu_stok as movements')
            ->whereIn('movements.branch_id', $branchIds)
            ->whereNotNull('movements.stok_batch_id')
            ->where('movements.tanggal_mutasi', $operator, $boundary)
            ->whereNotExists(function (Builder $query) use ($boundary, $operator) {
                $query->selectRaw('1')
                    ->from('kartu_stok as later')
                    ->whereColumn('later.branch_id', 'movements.branch_id')
                    ->whereColumn('later.stok_batch_id', 'movements.stok_batch_id')
                    ->where('later.tanggal_mutasi', $operator, $boundary)
                    ->where(function (Builder $query) {
                        $query->whereColumn('later.tanggal_mutasi', '>', 'movements.tanggal_mutasi')
                            ->orWhere(function (Builder $query) {
                                $query->whereColumn('later.tanggal_mutasi', 'movements.tanggal_mutasi')
                                    ->whereColumn('later.id', '>', 'movements.id');
                            });
                    });
            })
            ->get(['movements.obat_id', 'movements.saldo_batch', 'movements.harga_beli'])
            ->groupBy('obat_id')
            ->map(fn (Collection $rows) => round((float) $rows->sum(
                fn ($row) => max(0, (float) $row->saldo_batch) * max(0, (float) $row->harga_beli)
            ), 2));
    }

    private function sales(array $branchIds, Carbon $start, Carbon $end): Builder
    {
        return DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
    }

    private function returns(array $branchIds, Carbon $start, Carbon $end): Builder
    {
        return DB::table('retur_penjualan as returns')
            ->whereIn('returns.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('returns.status', 'posted')
            ->whereBetween(DB::raw($this->dateExpression('returns.tanggal_retur')), [$start->toDateString(), $end->toDateString()]);
    }

    private function detailTotals(): Builder
    {
        return DB::table('penjualan_transaction_details')
            ->select('penjualan_transaction_id')
            ->selectRaw('COUNT(id) as item_count')
            ->selectRaw('COALESCE(SUM(subtotal_net), 0) as subtotal_net')
            ->groupBy('penjualan_transaction_id');
    }

    private function detailCosts(): Builder
    {
        return DB::table('penjualan_transaction_batches')
            ->select('penjualan_transaction_detail_id')
            ->selectRaw('COALESCE(SUM(qty_stok * harga_beli), 0) as hpp')
            ->groupBy('penjualan_transaction_detail_id');
    }

    private function transactionCosts(): Builder
    {
        return DB::table('penjualan_transaction_batches as sale_batches')
            ->join('penjualan_transaction_details as details', 'details.id', '=', 'sale_batches.penjualan_transaction_detail_id')
            ->select('details.penjualan_transaction_id')
            ->selectRaw('COALESCE(SUM(sale_batches.qty_stok * sale_batches.harga_beli), 0) as hpp')
            ->groupBy('details.penjualan_transaction_id');
    }

    private function returnDetailCosts(): Builder
    {
        return DB::table('retur_penjualan_batches as return_batches')
            ->join('penjualan_transaction_batches as sale_batches', 'sale_batches.id', '=', 'return_batches.penjualan_transaction_batch_id')
            ->select('return_batches.retur_penjualan_detail_id')
            ->selectRaw('COALESCE(SUM(return_batches.qty_stok * sale_batches.harga_beli), 0) as hpp')
            ->groupBy('return_batches.retur_penjualan_detail_id');
    }

    private function returnDocumentCosts(): Builder
    {
        return DB::table('retur_penjualan_batches as return_batches')
            ->join('retur_penjualan_details as return_details', 'return_details.id', '=', 'return_batches.retur_penjualan_detail_id')
            ->join('penjualan_transaction_batches as sale_batches', 'sale_batches.id', '=', 'return_batches.penjualan_transaction_batch_id')
            ->select('return_details.retur_penjualan_id')
            ->selectRaw('COALESCE(SUM(return_batches.qty_stok * sale_batches.harga_beli), 0) as hpp')
            ->groupBy('return_details.retur_penjualan_id');
    }

    private function lineWeightExpression(): string
    {
        return '(CASE WHEN COALESCE(detail_totals.subtotal_net, 0) > 0 '
            .'THEN (1.0 * details.subtotal_net) / detail_totals.subtotal_net '
            .'ELSE 1.0 / NULLIF(detail_totals.item_count, 0) END)';
    }

    private function productKey(mixed $id, mixed $code, mixed $name): string
    {
        return $id !== null ? 'id:'.(int) $id : 'snapshot:'.trim((string) $code).'|'.trim((string) $name);
    }

    private function dateExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "date({$column})"
            : "DATE({$column})";
    }
}
