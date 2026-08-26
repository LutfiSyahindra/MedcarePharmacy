<?php

namespace App\Services\Dashboard;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\User;
use App\Services\Menu\Penjualan\PenjualanPosService;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DashboardCommandCenterService
{
    private const EXPIRY_WARNING_DAYS = 90;

    public function build(User $user, array $filters): array
    {
        $context = $this->context($user, $filters);
        $branchIds = $context['branch_ids'];
        $start = $filters['start'];
        $end = $filters['end'];

        if ($branchIds === []) {
            return $this->emptyPayload($context, $filters);
        }

        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousStart = $start->copy()->subDays($days);
        $previousEnd = $end->copy()->subDays($days);
        $current = $this->financialSnapshot($branchIds, $start, $end);
        $previous = $this->financialSnapshot($branchIds, $previousStart, $previousEnd);
        $inventory = $this->inventoryRows($branchIds);
        $outstanding = $this->outstandingSnapshot($branchIds);
        $alerts = $this->alerts($branchIds, $inventory);

        return [
            'meta' => $this->meta($context, $filters),
            'kpis' => $this->kpis($current, $previous, $inventory, $outstanding),
            'trend' => $this->salesTrend($branchIds, $start, $end),
            'payment_mix' => $this->paymentMix($branchIds, $start, $end),
            'inventory' => $this->inventorySummary($inventory),
            'top_products' => $this->topProducts($branchIds, $start, $end),
            'branches' => $this->branchPerformance($context['branches'], $branchIds, $start, $end),
            'alerts' => $alerts,
            'action_center' => $this->actionCenter($alerts),
            'recent_activity' => $this->recentActivity($branchIds, $start, $end),
        ];
    }

    private function context(User $user, array $filters): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);
        $selectedBranchId = $filters['branch_id'] ?? null;

        if ($selectedBranchId && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        $branchIds = $selectedBranchId ? [$selectedBranchId] : $allowedIds;
        $branchLabel = $selectedBranchId
            ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
            : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang'));

        return compact('branches', 'branchIds', 'branchLabel', 'selectedBranchId') + [
            'branch_ids' => $branchIds,
            'branch_label' => $branchLabel,
            'selected_branch_id' => $selectedBranchId,
        ];
    }

    private function financialSnapshot(array $branchIds, Carbon $start, Carbon $end): array
    {
        $sales = DB::table('penjualan_transactions')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->selectRaw('COUNT(*) as transaction_count')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(total_bayar - kembalian), 0) as cash_in')
            ->first();

        $returns = DB::table('retur_penjualan')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereBetween('tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->sum('grand_total');

        $salesHpp = DB::table('penjualan_transaction_batches as sale_batches')
            ->join('penjualan_transaction_details as details', 'details.id', '=', 'sale_batches.penjualan_transaction_detail_id')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->selectRaw('COALESCE(SUM(sale_batches.qty_stok * sale_batches.harga_beli), 0) as total')
            ->value('total');

        $returnHpp = DB::table('retur_penjualan_batches as return_batches')
            ->join('retur_penjualan_details as return_details', 'return_details.id', '=', 'return_batches.retur_penjualan_detail_id')
            ->join('retur_penjualan as sales_returns', 'sales_returns.id', '=', 'return_details.retur_penjualan_id')
            ->join('penjualan_transaction_batches as sale_batches', 'sale_batches.id', '=', 'return_batches.penjualan_transaction_batch_id')
            ->whereIn('sales_returns.branch_id', $branchIds)
            ->where('sales_returns.status', 'posted')
            ->whereBetween('sales_returns.tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COALESCE(SUM(return_batches.qty_stok * sale_batches.harga_beli), 0) as total')
            ->value('total');

        $grossSales = (float) ($sales->gross_sales ?? 0);
        $netSales = $grossSales - (float) $returns;
        $hpp = (float) $salesHpp - (float) $returnHpp;
        $transactionCount = (int) ($sales->transaction_count ?? 0);
        $grossProfit = $netSales - $hpp;

        return [
            'net_sales' => round($netSales, 2),
            'gross_sales' => round($grossSales, 2),
            'returns' => round((float) $returns, 2),
            'hpp' => round($hpp, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_margin' => $netSales != 0.0 ? round(($grossProfit / $netSales) * 100, 2) : 0,
            'transaction_count' => $transactionCount,
            'average_basket' => $transactionCount > 0 ? round($netSales / $transactionCount, 2) : 0,
            'cash_in' => round((float) ($sales->cash_in ?? 0) - (float) $returns, 2),
        ];
    }

    private function outstandingSnapshot(array $branchIds): array
    {
        $receivables = DB::table('penjualan_transactions')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->where('sisa_tagihan', '>', 0)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(sisa_tagihan), 0) as total')
            ->first();

        $payables = DB::table('penerimaan_barang as receipts')
            ->join('purchase_orders as purchase_orders', 'purchase_orders.id', '=', 'receipts.purchase_order_id')
            ->whereIn('purchase_orders.branch_id', $branchIds)
            ->where('receipts.status', 'posted')
            ->where('receipts.sisa_hutang', '>', 0)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(receipts.sisa_hutang), 0) as total')
            ->first();

        return [
            'receivables' => ['count' => (int) ($receivables->count ?? 0), 'total' => (float) ($receivables->total ?? 0)],
            'payables' => ['count' => (int) ($payables->count ?? 0), 'total' => (float) ($payables->total ?? 0)],
        ];
    }

    private function inventoryRows(array $branchIds): Collection
    {
        $today = today()->toDateString();
        $warningDate = today()->addDays(self::EXPIRY_WARNING_DAYS)->toDateString();

        return DB::table('stok_batches as batches')
            ->join('master_obats as medicines', 'medicines.id', '=', 'batches.obat_id')
            ->join('branches', 'branches.id', '=', 'batches.branch_id')
            ->whereIn('batches.branch_id', $branchIds)
            ->where('medicines.is_active', true)
            ->groupBy(
                'batches.branch_id',
                'branches.name',
                'batches.obat_id',
                'medicines.kode_obat',
                'medicines.nama_obat',
                'medicines.stok_minimum'
            )
            ->select([
                'batches.branch_id',
                'branches.name as branch_name',
                'batches.obat_id',
                'medicines.kode_obat',
                'medicines.nama_obat',
                'medicines.stok_minimum',
            ])
            ->selectRaw('COALESCE(SUM(batches.qty), 0) as total_stock')
            ->selectRaw('COALESCE(SUM(batches.qty * batches.harga_beli), 0) as stock_value')
            ->selectRaw('SUM(CASE WHEN batches.qty > 0 AND batches.expired_date IS NOT NULL AND batches.expired_date < ? THEN 1 ELSE 0 END) as expired_batches', [$today])
            ->selectRaw('SUM(CASE WHEN batches.qty > 0 AND batches.expired_date IS NOT NULL AND batches.expired_date >= ? AND batches.expired_date <= ? THEN 1 ELSE 0 END) as near_expiry_batches', [$today, $warningDate])
            ->selectRaw('MIN(CASE WHEN batches.qty > 0 AND batches.expired_date IS NOT NULL THEN batches.expired_date ELSE NULL END) as nearest_expiry')
            ->get();
    }

    private function kpis(array $current, array $previous, Collection $inventory, array $outstanding): array
    {
        $stockValue = (float) $inventory->sum(fn ($row) => (float) $row->stock_value);

        return [
            $this->kpi('net_sales', 'Omzet bersih', $current['net_sales'], 'currency', 'mdi-chart-line', 'primary', $current['net_sales'], $previous['net_sales'], 'Setelah retur penjualan'),
            $this->kpi('gross_profit', 'Laba kotor', $current['gross_profit'], 'currency', 'mdi-finance', 'success', $current['gross_profit'], $previous['gross_profit'], 'Margin '.$this->number($current['gross_margin']).'%'),
            $this->kpi('transactions', 'Transaksi selesai', $current['transaction_count'], 'number', 'mdi-receipt-text-check-outline', 'info', $current['transaction_count'], $previous['transaction_count'], 'Rata-rata '.$this->money($current['average_basket'])),
            $this->kpi('cash_in', 'Kas penjualan', $current['cash_in'], 'currency', 'mdi-cash-multiple', 'teal', $current['cash_in'], $previous['cash_in'], 'Pembayaran bersih setelah retur'),
            $this->kpi('stock_value', 'Nilai persediaan', $stockValue, 'currency', 'mdi-warehouse', 'violet', null, null, $this->number($inventory->count()).' produk-cabang'),
            $this->kpi('receivables', 'Piutang aktif', $outstanding['receivables']['total'], 'currency', 'mdi-account-cash-outline', 'warning', null, null, $this->number($outstanding['receivables']['count']).' transaksi'),
            $this->kpi('payables', 'Hutang supplier', $outstanding['payables']['total'], 'currency', 'mdi-file-document-alert-outline', 'danger', null, null, $this->number($outstanding['payables']['count']).' faktur'),
            $this->kpi('returns', 'Retur penjualan', $current['returns'], 'currency', 'mdi-keyboard-return', 'orange', null, null, 'Mengurangi omzet periode'),
        ];
    }

    private function kpi(string $key, string $label, float|int $value, string $format, string $icon, string $tone, float|int|null $current, float|int|null $previous, string $meta): array
    {
        $change = $current === null ? null : $this->change((float) $current, (float) $previous);

        return compact('key', 'label', 'value', 'format', 'icon', 'tone', 'change', 'meta');
    }

    private function change(float $current, float $previous): array
    {
        $percent = $previous == 0.0
            ? ($current == 0.0 ? 0.0 : 100.0)
            : (($current - $previous) / abs($previous)) * 100;

        return [
            'value' => round(abs($percent), 1),
            'direction' => $percent > 0.05 ? 'up' : ($percent < -0.05 ? 'down' : 'flat'),
        ];
    }

    private function salesTrend(array $branchIds, Carbon $start, Carbon $end): array
    {
        $sales = DB::table('penjualan_transactions')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->selectRaw('DATE(tanggal_transaksi) as date_key, COALESCE(SUM(grand_total), 0) as total, COUNT(*) as transactions')
            ->groupByRaw('DATE(tanggal_transaksi)')
            ->pluck('total', 'date_key');
        $transactions = DB::table('penjualan_transactions')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->selectRaw('DATE(tanggal_transaksi) as date_key, COUNT(*) as total')
            ->groupByRaw('DATE(tanggal_transaksi)')
            ->pluck('total', 'date_key');
        $returns = DB::table('retur_penjualan')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereBetween('tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('tanggal_retur as date_key, COALESCE(SUM(grand_total), 0) as total')
            ->groupBy('tanggal_retur')
            ->pluck('total', 'date_key');

        $labels = [];
        $values = [];
        $counts = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $key;
            $values[] = round((float) ($sales[$key] ?? 0) - (float) ($returns[$key] ?? 0), 2);
            $counts[] = (int) ($transactions[$key] ?? 0);
            $cursor->addDay();
        }

        return compact('labels', 'values', 'counts');
    }

    private function paymentMix(array $branchIds, Carbon $start, Carbon $end): array
    {
        $rows = DB::table('penjualan_payments as payments')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'payments.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->groupBy('payments.metode')
            ->select('payments.metode')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as total')
            ->orderByDesc('total')
            ->get();

        $cashChange = DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('penjualan_payments as cash_payments')
                    ->whereColumn('cash_payments.penjualan_transaction_id', 'sales.id')
                    ->where('cash_payments.metode', 'tunai');
            })
            ->sum('sales.kembalian');

        $labels = PenjualanPosService::PAYMENT_METHODS;

        return $rows->map(function ($row) use ($labels, $cashChange) {
            $total = (float) $row->total;
            if ($row->metode === 'tunai') {
                $total = max(0, $total - (float) $cashChange);
            }

            return [
                'key' => $row->metode,
                'label' => $labels[$row->metode] ?? ucfirst(str_replace('_', ' ', $row->metode)),
                'value' => round($total, 2),
            ];
        })->values()->all();
    }

    private function inventorySummary(Collection $rows): array
    {
        $summary = [
            'healthy' => 0,
            'low' => 0,
            'empty' => 0,
            'near_expiry' => 0,
            'expired' => 0,
            'stock_value' => round((float) $rows->sum(fn ($row) => (float) $row->stock_value), 2),
        ];

        foreach ($rows as $row) {
            $stock = (float) $row->total_stock;
            $minimum = (float) $row->stok_minimum;

            if ((int) $row->expired_batches > 0) {
                $summary['expired']++;
            } elseif ((int) $row->near_expiry_batches > 0) {
                $summary['near_expiry']++;
            } elseif ($stock <= 0) {
                $summary['empty']++;
            } elseif ($minimum > 0 && $stock <= $minimum) {
                $summary['low']++;
            } else {
                $summary['healthy']++;
            }
        }

        return $summary;
    }

    private function topProducts(array $branchIds, Carbon $start, Carbon $end): array
    {
        $sales = DB::table('penjualan_transaction_details as details')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->groupBy('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'details.satuan_jual')
            ->select('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'details.satuan_jual')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.total_line), 0) as revenue')
            ->get();
        $returns = DB::table('retur_penjualan_details as details')
            ->join('retur_penjualan as sales_returns', 'sales_returns.id', '=', 'details.retur_penjualan_id')
            ->whereIn('sales_returns.branch_id', $branchIds)
            ->where('sales_returns.status', 'posted')
            ->whereBetween('sales_returns.tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->groupBy('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'details.satuan_jual')
            ->select('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'details.satuan_jual')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.total), 0) as revenue')
            ->get()
            ->keyBy(fn ($row) => $this->productKey($row));

        $net = $sales->map(function ($row) use ($returns) {
            $returned = $returns->get($this->productKey($row));

            return [
                'obat_id' => $row->obat_id ? (int) $row->obat_id : null,
                'code' => $row->kode_obat ?: '-',
                'name' => $row->nama_obat,
                'unit' => $row->satuan_jual ?: 'unit',
                'qty' => round((float) $row->qty - (float) ($returned->qty ?? 0), 2),
                'revenue' => round((float) $row->revenue - (float) ($returned->revenue ?? 0), 2),
            ];
        })->sortByDesc('revenue')->take(7)->values();
        $maximum = max(1, (float) ($net->max('revenue') ?? 1));

        return $net->map(function (array $row, int $index) use ($maximum) {
            $row['rank'] = $index + 1;
            $row['progress'] = round(max(0, $row['revenue']) / $maximum * 100, 1);

            return $row;
        })->all();
    }

    private function productKey(object $row): string
    {
        return $row->obat_id ? 'id:'.$row->obat_id : 'name:'.$row->kode_obat.'|'.$row->nama_obat;
    }

    private function branchPerformance(Collection $branches, array $branchIds, Carbon $start, Carbon $end): array
    {
        $sales = DB::table('penjualan_transactions')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->groupBy('branch_id')
            ->select('branch_id')
            ->selectRaw('COUNT(*) as transactions, COALESCE(SUM(grand_total), 0) as total')
            ->get()
            ->keyBy('branch_id');
        $returns = DB::table('retur_penjualan')
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereBetween('tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->groupBy('branch_id')
            ->select('branch_id')
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total')
            ->pluck('total', 'branch_id');

        $rows = $branches
            ->whereIn('id', $branchIds)
            ->map(function (BranchModel $branch) use ($sales, $returns) {
                $sale = $sales->get($branch->id);
                $net = (float) ($sale->total ?? 0) - (float) ($returns[$branch->id] ?? 0);

                return [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                    'active' => (bool) $branch->is_active,
                    'transactions' => (int) ($sale->transactions ?? 0),
                    'net_sales' => round($net, 2),
                ];
            })
            ->sortByDesc('net_sales')
            ->values();
        $total = (float) $rows->sum('net_sales');

        return $rows->map(function (array $row, int $index) use ($total) {
            $row['rank'] = $index + 1;
            $row['share'] = $total != 0.0 ? round(($row['net_sales'] / $total) * 100, 1) : 0;

            return $row;
        })->all();
    }

    private function alerts(array $branchIds, Collection $inventory): array
    {
        $alerts = collect();

        foreach ($inventory as $row) {
            $stock = (float) $row->total_stock;
            $minimum = (float) $row->stok_minimum;
            $base = [
                'title' => $row->nama_obat,
                'branch' => $row->branch_name,
                'action_url' => route('stok.stok'),
                'action_label' => 'Buka stok',
            ];

            if ((int) $row->expired_batches > 0) {
                $alerts->push($base + [
                    'kind' => 'expired', 'severity' => 'critical', 'icon' => 'mdi-calendar-remove-outline',
                    'description' => $this->number($row->expired_batches).' batch sudah kedaluwarsa di '.$row->branch_name.'.',
                    'metric' => 'ED '.$this->date($row->nearest_expiry),
                ]);
            }

            if ((int) $row->near_expiry_batches > 0) {
                $alerts->push($base + [
                    'kind' => 'near_expiry', 'severity' => 'warning', 'icon' => 'mdi-calendar-clock-outline',
                    'description' => $this->number($row->near_expiry_batches).' batch mendekati kedaluwarsa di '.$row->branch_name.'.',
                    'metric' => 'ED terdekat '.$this->date($row->nearest_expiry),
                ]);
            }

            if ($stock <= 0 || ($minimum > 0 && $stock <= $minimum)) {
                $alerts->push($base + [
                    'kind' => $stock <= 0 ? 'empty_stock' : 'low_stock',
                    'severity' => $stock <= 0 ? 'critical' : 'warning',
                    'icon' => $stock <= 0 ? 'mdi-package-variant-remove' : 'mdi-package-variant-closed-minus',
                    'description' => ($stock <= 0 ? 'Stok kosong' : 'Stok menyentuh batas minimum').' di '.$row->branch_name.'.',
                    'metric' => $this->number($stock).' / min '.$this->number($minimum),
                ]);
            }
        }

        $overdueInvoices = DB::table('penerimaan_barang as receipts')
            ->join('purchase_orders as purchase_orders', 'purchase_orders.id', '=', 'receipts.purchase_order_id')
            ->join('distributors', 'distributors.id', '=', 'receipts.distributor_id')
            ->join('branches', 'branches.id', '=', 'purchase_orders.branch_id')
            ->whereIn('purchase_orders.branch_id', $branchIds)
            ->where('receipts.status', 'posted')
            ->where('receipts.sisa_hutang', '>', 0)
            ->whereNotNull('receipts.tanggal_jatuh_tempo')
            ->whereDate('receipts.tanggal_jatuh_tempo', '<=', today()->addDays(7))
            ->orderBy('receipts.tanggal_jatuh_tempo')
            ->limit(10)
            ->get([
                'receipts.nomor_faktur', 'receipts.tanggal_jatuh_tempo', 'receipts.sisa_hutang',
                'distributors.nama as distributor_name', 'branches.name as branch_name',
            ]);

        foreach ($overdueInvoices as $invoice) {
            $overdue = Carbon::parse($invoice->tanggal_jatuh_tempo)->lt(today());
            $alerts->push([
                'kind' => 'payable',
                'severity' => $overdue ? 'critical' : 'warning',
                'icon' => 'mdi-file-document-alert-outline',
                'title' => 'Faktur '.$invoice->nomor_faktur,
                'description' => $invoice->distributor_name.' · '.$invoice->branch_name,
                'metric' => $this->money($invoice->sisa_hutang).' · '.($overdue ? 'Terlambat' : 'Jatuh tempo').' '.$this->date($invoice->tanggal_jatuh_tempo),
                'branch' => $invoice->branch_name,
                'action_url' => route('faktur.faktur'),
                'action_label' => 'Buka faktur',
            ]);
        }

        $oldReceivables = DB::table('penjualan_transactions as sales')
            ->join('branches', 'branches.id', '=', 'sales.branch_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->where('sales.sisa_tagihan', '>', 0)
            ->where('sales.tanggal_transaksi', '<=', now()->subDays(14))
            ->orderBy('sales.tanggal_transaksi')
            ->limit(8)
            ->get(['sales.nomor_transaksi', 'sales.customer_name', 'sales.sisa_tagihan', 'sales.tanggal_transaksi', 'branches.name as branch_name']);

        foreach ($oldReceivables as $sale) {
            $age = Carbon::parse($sale->tanggal_transaksi)->diffInDays(today());
            $alerts->push([
                'kind' => 'receivable',
                'severity' => $age >= 30 ? 'critical' : 'warning',
                'icon' => 'mdi-account-clock-outline',
                'title' => 'Piutang '.$sale->nomor_transaksi,
                'description' => ($sale->customer_name ?: 'Pelanggan umum').' · '.$sale->branch_name,
                'metric' => $this->money($sale->sisa_tagihan).' · '.$age.' hari',
                'branch' => $sale->branch_name,
                'action_url' => route('penjualan.pos.history'),
                'action_label' => 'Buka transaksi',
            ]);
        }

        $pendingOrders = DB::table('purchase_orders')
            ->join('distributors', 'distributors.id', '=', 'purchase_orders.distributor_id')
            ->join('branches', 'branches.id', '=', 'purchase_orders.branch_id')
            ->whereIn('purchase_orders.branch_id', $branchIds)
            ->where('purchase_orders.status', 'waiting_approval')
            ->oldest('purchase_orders.created_at')
            ->limit(8)
            ->get(['purchase_orders.no_po', 'purchase_orders.total_estimasi', 'purchase_orders.created_at', 'distributors.nama as distributor_name', 'branches.name as branch_name']);

        foreach ($pendingOrders as $order) {
            $alerts->push([
                'kind' => 'approval',
                'severity' => 'info',
                'icon' => 'mdi-clipboard-check-outline',
                'title' => $order->no_po.' menunggu approval',
                'description' => $order->distributor_name.' · '.$order->branch_name,
                'metric' => $this->money($order->total_estimasi),
                'branch' => $order->branch_name,
                'action_url' => route('pembelian.pembelian'),
                'action_label' => 'Review PO',
            ]);
        }

        $opnames = DB::table('stock_opnames')
            ->join('branches', 'branches.id', '=', 'stock_opnames.branch_id')
            ->whereIn('stock_opnames.branch_id', $branchIds)
            ->whereIn('stock_opnames.status', [StockOpnameModel::STATUS_COUNTING, StockOpnameModel::STATUS_AWAITING_VERIFICATION, StockOpnameModel::STATUS_AWAITING_APPROVAL])
            ->oldest('stock_opnames.created_at')
            ->limit(6)
            ->get(['stock_opnames.nomor', 'stock_opnames.status', 'stock_opnames.created_at', 'branches.name as branch_name']);

        foreach ($opnames as $opname) {
            $alerts->push([
                'kind' => 'stock_opname',
                'severity' => $opname->status === StockOpnameModel::STATUS_COUNTING ? 'warning' : 'info',
                'icon' => 'mdi-clipboard-list-outline',
                'title' => 'Stock opname '.$opname->nomor,
                'description' => (StockOpnameModel::statusLabels()[$opname->status] ?? $opname->status).' · '.$opname->branch_name,
                'metric' => Carbon::parse($opname->created_at)->diffForHumans(),
                'branch' => $opname->branch_name,
                'action_url' => route('stockOpname.index'),
                'action_label' => 'Buka opname',
            ]);
        }

        $licenses = DB::table('apotek_profiles')
            ->join('branches', 'branches.id', '=', 'apotek_profiles.branch_id')
            ->whereIn('apotek_profiles.branch_id', $branchIds)
            ->whereNotNull('apotek_profiles.license_expired_at')
            ->whereDate('apotek_profiles.license_expired_at', '<=', today()->addDays(60))
            ->orderBy('apotek_profiles.license_expired_at')
            ->get(['apotek_profiles.name', 'apotek_profiles.license_expired_at', 'branches.name as branch_name']);

        foreach ($licenses as $license) {
            $expired = Carbon::parse($license->license_expired_at)->lt(today());
            $alerts->push([
                'kind' => 'license',
                'severity' => $expired ? 'critical' : 'warning',
                'icon' => 'mdi-certificate-outline',
                'title' => 'Izin '.$license->name,
                'description' => $expired ? 'Masa berlaku izin sudah berakhir.' : 'Masa berlaku izin segera berakhir.',
                'metric' => $this->date($license->license_expired_at),
                'branch' => $license->branch_name,
                'action_url' => route('settings.apotek-profile.index'),
                'action_label' => 'Perbarui profil',
            ]);
        }

        $weight = ['critical' => 0, 'warning' => 1, 'info' => 2];
        $all = $alerts->sortBy(fn ($alert) => ($weight[$alert['severity']] ?? 9).'|'.$alert['title'])->values();

        return [
            'items' => $all->take(40)->all(),
            'total' => $all->count(),
            'critical' => $all->where('severity', 'critical')->count(),
            'warning' => $all->where('severity', 'warning')->count(),
            'info' => $all->where('severity', 'info')->count(),
            'kind_counts' => $all->countBy('kind')->all(),
        ];
    }

    private function actionCenter(array $alerts): array
    {
        $counts = collect($alerts['kind_counts'] ?? []);
        $sum = fn (array $kinds): int => (int) $counts->only($kinds)->sum();

        return [
            [
                'key' => 'stock', 'label' => 'Risiko persediaan', 'icon' => 'mdi-package-variant-closed-minus', 'tone' => 'danger',
                'count' => $sum(['expired', 'near_expiry', 'empty_stock', 'low_stock']),
                'description' => 'Stok kosong, menipis, dan kedaluwarsa', 'url' => route('stok.stok'),
            ],
            [
                'key' => 'finance', 'label' => 'Tagihan prioritas', 'icon' => 'mdi-cash-clock', 'tone' => 'warning',
                'count' => $sum(['payable', 'receivable']),
                'description' => 'Hutang jatuh tempo dan piutang lama', 'url' => route('faktur.faktur'),
            ],
            [
                'key' => 'approval', 'label' => 'Approval transaksi', 'icon' => 'mdi-shield-check-outline', 'tone' => 'info',
                'count' => $sum(['approval']),
                'description' => 'Purchase order menunggu keputusan', 'url' => route('pembelian.pembelian'),
            ],
            [
                'key' => 'opname', 'label' => 'Stock opname aktif', 'icon' => 'mdi-clipboard-list-outline', 'tone' => 'violet',
                'count' => $sum(['stock_opname']),
                'description' => 'Penghitungan dan verifikasi berjalan', 'url' => route('stockOpname.index'),
            ],
        ];
    }

    private function recentActivity(array $branchIds, Carbon $start, Carbon $end): array
    {
        $activities = collect();
        $sales = PenjualanTransactionModel::query()
            ->with(['branch:id,name', 'completedBy:id,name'])
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('tanggal_transaksi', [$start, $end])
            ->latest('tanggal_transaksi')
            ->limit(5)
            ->get();

        foreach ($sales as $sale) {
            $activities->push([
                'type' => 'sale', 'icon' => 'mdi-cart-check', 'tone' => 'success',
                'title' => $sale->nomor_transaksi,
                'description' => ($sale->customer_name ?: 'Pelanggan umum').' · '.($sale->branch?->name ?: '-'),
                'amount' => (float) $sale->grand_total,
                'status' => 'Penjualan selesai',
                'at' => $sale->tanggal_transaksi?->toIso8601String(),
                'time' => $sale->tanggal_transaksi?->diffForHumans(),
                'url' => route('penjualan.pos.history'),
            ]);
        }

        $receipts = DB::table('penerimaan_barang as receipts')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'receipts.purchase_order_id')
            ->join('branches', 'branches.id', '=', 'purchase_orders.branch_id')
            ->join('distributors', 'distributors.id', '=', 'receipts.distributor_id')
            ->whereIn('purchase_orders.branch_id', $branchIds)
            ->where('receipts.status', 'posted')
            ->whereBetween('receipts.posted_at', [$start, $end])
            ->latest('receipts.posted_at')
            ->limit(4)
            ->get(['receipts.nomor_penerimaan', 'receipts.grand_total', 'receipts.posted_at', 'branches.name as branch_name', 'distributors.nama as distributor_name']);

        foreach ($receipts as $receipt) {
            $at = Carbon::parse($receipt->posted_at);
            $activities->push([
                'type' => 'receipt', 'icon' => 'mdi-truck-check-outline', 'tone' => 'primary',
                'title' => $receipt->nomor_penerimaan,
                'description' => $receipt->distributor_name.' · '.$receipt->branch_name,
                'amount' => (float) $receipt->grand_total,
                'status' => 'Penerimaan diposting', 'at' => $at->toIso8601String(), 'time' => $at->diffForHumans(),
                'url' => route('penerimaan.penerimaan'),
            ]);
        }

        return $activities
            ->filter(fn ($activity) => $activity['at'])
            ->sortByDesc('at')
            ->take(8)
            ->values()
            ->all();
    }

    private function meta(array $context, array $filters): array
    {
        return [
            'period' => $filters['period'],
            'start_date' => $filters['start']->toDateString(),
            'end_date' => $filters['end']->toDateString(),
            'range_label' => $filters['start']->translatedFormat('d M Y').' – '.$filters['end']->translatedFormat('d M Y'),
            'branch_id' => $context['selected_branch_id'],
            'branch_label' => $context['branch_label'],
            'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                'id' => (int) $branch->id,
                'code' => $branch->code,
                'name' => $branch->name,
                'active' => (bool) $branch->is_active,
            ])->values()->all(),
            'generated_at' => now()->toIso8601String(),
            'generated_label' => now()->translatedFormat('d M Y, H:i'),
            'empty_scope' => $context['branch_ids'] === [],
        ];
    }

    private function emptyPayload(array $context, array $filters): array
    {
        return [
            'meta' => $this->meta($context, $filters),
            'kpis' => [],
            'trend' => ['labels' => [], 'values' => [], 'counts' => []],
            'payment_mix' => [],
            'inventory' => ['healthy' => 0, 'low' => 0, 'empty' => 0, 'near_expiry' => 0, 'expired' => 0, 'stock_value' => 0],
            'top_products' => [],
            'branches' => [],
            'alerts' => ['items' => [], 'total' => 0, 'critical' => 0, 'warning' => 0, 'info' => 0, 'kind_counts' => []],
            'action_center' => [],
            'recent_activity' => [],
        ];
    }

    private function date(mixed $value): string
    {
        return $value ? Carbon::parse($value)->translatedFormat('d M Y') : '-';
    }

    private function money(mixed $value): string
    {
        return 'Rp '.number_format((float) $value, 0, ',', '.');
    }

    private function number(mixed $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }
}
