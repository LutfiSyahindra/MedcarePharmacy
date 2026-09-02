<?php

namespace App\Services\Menu\AnalisisPersediaan;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAnalysisService
{
    public const TYPES = [
        'pergerakan-stok' => [
            'title' => 'Pergerakan Stok',
            'short_title' => 'Pergerakan Stok',
            'description' => 'Pantau arus stok masuk dan keluar untuk menemukan produk dengan aktivitas tertinggi.',
            'icon' => 'mdi-swap-horizontal-bold',
            'tone' => 'blue',
        ],
        'pareto-abc' => [
            'title' => 'Pareto ABC',
            'short_title' => 'Pareto ABC',
            'description' => 'Kelompokkan produk berdasarkan kontribusi omzet bersih: A hingga 80%, B hingga 95%, dan C sisanya.',
            'icon' => 'mdi-chart-donut-variant',
            'tone' => 'violet',
        ],
        'stok-hampir-habis' => [
            'title' => 'Stok Hampir Habis',
            'short_title' => 'Stok Hampir Habis',
            'description' => 'Prioritaskan produk yang saldo terkininya sudah mencapai atau berada di bawah stok minimum.',
            'icon' => 'mdi-package-variant-minus',
            'tone' => 'amber',
            'menu_visible' => false,
        ],
        'slow-moving' => [
            'title' => 'Slow Moving',
            'short_title' => 'Slow Moving',
            'description' => 'Temukan persediaan yang masih bernilai tetapi lama tidak memiliki pergerakan keluar.',
            'icon' => 'mdi-timer-sand',
            'tone' => 'orange',
        ],
        'dead-stock' => [
            'title' => 'Dead Stock',
            'short_title' => 'Dead Stock',
            'description' => 'Identifikasi nilai modal yang tertahan pada produk tanpa pergerakan keluar dalam waktu lama.',
            'icon' => 'mdi-package-variant-remove',
            'tone' => 'red',
        ],
        'saran-pembelian' => [
            'title' => 'Saran Pembelian',
            'short_title' => 'Saran Pembelian',
            'description' => 'Hitung kebutuhan pembelian dari tren penjualan, target hari persediaan, dan stok pengaman minimum.',
            'icon' => 'mdi-cart-arrow-down',
            'tone' => 'green',
        ],
    ];

    public static function menuTypes(): array
    {
        return array_filter(
            self::TYPES,
            fn (array $definition): bool => $definition['menu_visible'] ?? true,
        );
    }

    public function definition(string $type): array
    {
        return self::TYPES[$type] ?? throw ValidationException::withMessages([
            'analysis' => 'Jenis analisis persediaan tidak tersedia.',
        ]);
    }

    public function build(User $user, string $type, array $filters): array
    {
        $definition = $this->definition($type);
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $baseRows = $context['branch_ids'] === []
            ? collect()
            : $this->baseRows($context['branch_ids'], $filters, $type === 'saran-pembelian');

        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        if ($search !== '') {
            $baseRows = $baseRows->filter(function (array $row) use ($search, $type) {
                $searchable = $row['code'].' '.$row['name'].' '.$row['unit'];
                if ($type !== 'saran-pembelian') {
                    $searchable .= ' '.$row['supplier'];
                }

                return str_contains(mb_strtolower($searchable), $search);
            })->values();
        }

        $analysis = match ($type) {
            'pergerakan-stok' => $this->stockMovement($baseRows),
            'pareto-abc' => $this->pareto($baseRows),
            'stok-hampir-habis' => $this->lowStock($baseRows),
            'slow-moving' => $this->slowMoving($baseRows, $filters),
            'dead-stock' => $this->deadStock($baseRows, $filters),
            'saran-pembelian' => $this->purchaseSuggestion($baseRows, $filters),
        };

        return [
            'meta' => [
                'type' => $type,
                ...$definition,
                'branch_label' => $context['branch_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'period_days' => $filters['period_days'],
                'slow_days' => $filters['slow_days'],
                'dead_days' => $filters['dead_days'],
                'cover_days' => $filters['cover_days'],
                'lead_days' => $filters['lead_days'],
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            ...$analysis,
        ];
    }

    private function context(User $user, ?int $selectedBranchId): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        if ($selectedBranchId && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        $branchIds = $selectedBranchId ? [$selectedBranchId] : $allowedIds;
        $branchLabel = $selectedBranchId
            ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
            : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang'));

        return [
            'branches' => $branches,
            'branch_ids' => $branchIds,
            'branch_label' => $branchLabel,
            'selected_branch_id' => $selectedBranchId,
        ];
    }

    private function baseRows(array $branchIds, array $filters, bool $includePendingOrders): Collection
    {
        $todayDate = today()->toDateString();
        $stock = DB::table('stok_batches as batches')
            ->join('master_obats as medicines', 'medicines.id', '=', 'batches.obat_id')
            ->whereIn('batches.branch_id', $branchIds)
            ->where('medicines.is_active', true)
            ->groupBy('batches.obat_id')
            ->select('batches.obat_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) as current_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0) as stock_value')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 AND (batches.expired_date IS NULL OR batches.expired_date >= ?) THEN batches.qty ELSE 0 END), 0) as usable_stock', [$todayDate])
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 AND (batches.expired_date IS NULL OR batches.expired_date >= ?) THEN batches.qty * batches.harga_beli ELSE 0 END), 0) as usable_stock_value', [$todayDate])
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 AND batches.expired_date < ? THEN batches.qty ELSE 0 END), 0) as expired_stock', [$todayDate])
            ->selectRaw('SUM(CASE WHEN batches.qty > 0 THEN 1 ELSE 0 END) as batch_count')
            ->selectRaw('MIN(CASE WHEN batches.qty > 0 THEN batches.expired_date ELSE NULL END) as nearest_expiry')
            ->selectRaw('MIN(CASE WHEN batches.qty > 0 THEN batches.created_at ELSE NULL END) as first_stocked_at')
            ->selectRaw('MAX(batches.last_movement_at) as last_batch_movement_at')
            ->get()
            ->keyBy('obat_id');

        $periodMovements = DB::table('kartu_stok as movements')
            ->join('master_obats as medicines', 'medicines.id', '=', 'movements.obat_id')
            ->whereIn('movements.branch_id', $branchIds)
            ->where('medicines.is_active', true)
            ->whereBetween('movements.tanggal_mutasi', [$filters['start'], $filters['end']])
            ->groupBy('movements.obat_id')
            ->select('movements.obat_id')
            ->selectRaw('COALESCE(SUM(movements.qty_masuk), 0) as qty_in')
            ->selectRaw('COALESCE(SUM(movements.qty_keluar), 0) as qty_out')
            ->selectRaw('COUNT(movements.id) as movement_count')
            ->selectRaw('MAX(movements.tanggal_mutasi) as last_period_movement_at')
            ->get()
            ->keyBy('obat_id');

        $movementsAfterPeriod = DB::table('kartu_stok as movements')
            ->join('master_obats as medicines', 'medicines.id', '=', 'movements.obat_id')
            ->whereIn('movements.branch_id', $branchIds)
            ->where('medicines.is_active', true)
            ->where('movements.tanggal_mutasi', '>', $filters['end'])
            ->groupBy('movements.obat_id')
            ->select('movements.obat_id')
            ->selectRaw('COALESCE(SUM(movements.qty_masuk), 0) as qty_in')
            ->selectRaw('COALESCE(SUM(movements.qty_keluar), 0) as qty_out')
            ->get()
            ->keyBy('obat_id');

        $lastMovements = DB::table('kartu_stok as movements')
            ->join('master_obats as medicines', 'medicines.id', '=', 'movements.obat_id')
            ->whereIn('movements.branch_id', $branchIds)
            ->where('medicines.is_active', true)
            ->groupBy('movements.obat_id')
            ->select('movements.obat_id')
            ->selectRaw('MAX(movements.tanggal_mutasi) as last_movement_at')
            ->selectRaw('MAX(CASE WHEN movements.qty_keluar > 0 THEN movements.tanggal_mutasi ELSE NULL END) as last_out_at')
            ->get()
            ->keyBy('obat_id');

        $sales = DB::table('penjualan_transaction_details as details')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->where('sales.status', 'completed')
            ->where('medicines.is_active', true)
            ->whereBetween('sales.tanggal_transaksi', [$filters['start'], $filters['end']])
            ->groupBy('details.obat_id')
            ->select('details.obat_id')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.total_line), 0) as revenue')
            ->selectRaw('COUNT(DISTINCT sales.id) as transaction_count')
            ->selectRaw('MAX(sales.tanggal_transaksi) as last_sale_at')
            ->get()
            ->keyBy('obat_id');

        $returns = DB::table('retur_penjualan_details as details')
            ->join('retur_penjualan as returns', 'returns.id', '=', 'details.retur_penjualan_id')
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->whereIn('returns.branch_id', $branchIds)
            ->where('returns.status', 'posted')
            ->where('medicines.is_active', true)
            ->whereBetween('returns.tanggal_retur', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->groupBy('details.obat_id')
            ->select('details.obat_id')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.total), 0) as revenue')
            ->get()
            ->keyBy('obat_id');

        $pendingOrders = collect();
        if ($includePendingOrders) {
            $postedReceipts = DB::table('penerimaan_barang_detail as receipt_details')
                ->join('penerimaan_barang as receipts', 'receipts.id', '=', 'receipt_details.penerimaan_barang_id')
                ->where('receipts.status', 'posted')
                ->groupBy('receipt_details.purchase_order_detail_id')
                ->select('receipt_details.purchase_order_detail_id')
                ->selectRaw('COALESCE(SUM(receipt_details.qty_diterima), 0) as received_qty');

            $pendingOrders = DB::table('purchase_order_details as details')
                ->join('purchase_orders as orders', 'orders.id', '=', 'details.purchase_order_id')
                ->leftJoin('obat_satuan_conversions as unit_conversions', 'unit_conversions.id', '=', 'details.satuan_konversi')
                ->leftJoinSub($postedReceipts, 'received', function ($join) {
                    $join->on('received.purchase_order_detail_id', '=', 'details.id');
                })
                ->whereIn('orders.branch_id', $branchIds)
                ->whereIn('orders.status', ['waiting_approval', 'approved', 'diterima_sebagian'])
                ->groupBy('details.obat_id')
                ->select('details.obat_id')
                ->selectRaw('COALESCE(SUM(CASE
                    WHEN details.qty > COALESCE(received.received_qty, 0)
                    THEN (details.qty - COALESCE(received.received_qty, 0)) * COALESCE(NULLIF(unit_conversions.konversi, 0), 1)
                    ELSE 0
                END), 0) as pending_order_qty')
                ->get()
                ->keyBy('obat_id');
        }

        $medicineIds = collect()
            ->merge($stock->keys())
            ->merge($periodMovements->keys())
            ->merge($lastMovements->keys())
            ->merge($sales->keys())
            ->merge($returns->keys())
            ->merge($pendingOrders->keys())
            ->merge(MasterObatModel::query()->where('is_active', true)->where('stok_minimum', '>', 0)->pluck('id'))
            ->filter()
            ->unique()
            ->values();

        if ($medicineIds->isEmpty()) {
            return collect();
        }

        $medicines = MasterObatModel::query()
            ->with(['satuan', 'distributor', 'konversiSatuan.satuan'])
            ->where('is_active', true)
            ->whereIn('id', $medicineIds)
            ->orderBy('nama_obat')
            ->get();
        $periodDays = max(1, (int) $filters['period_days']);
        $today = today();

        return $medicines->map(function (MasterObatModel $medicine) use ($stock, $periodMovements, $movementsAfterPeriod, $lastMovements, $sales, $returns, $pendingOrders, $periodDays, $today) {
            $stockRow = $stock->get($medicine->id);
            $movement = $periodMovements->get($medicine->id);
            $movementAfterPeriod = $movementsAfterPeriod->get($medicine->id);
            $lastMovement = $lastMovements->get($medicine->id);
            $sale = $sales->get($medicine->id);
            $return = $returns->get($medicine->id);
            $pendingOrder = $pendingOrders->get($medicine->id);
            $currentStock = round((float) ($stockRow->current_stock ?? 0), 2);
            $stockValue = round((float) ($stockRow->stock_value ?? 0), 2);
            $usableStock = round((float) ($stockRow->usable_stock ?? 0), 2);
            $usableStockValue = round((float) ($stockRow->usable_stock_value ?? 0), 2);
            $qtyIn = round((float) ($movement->qty_in ?? 0), 2);
            $qtyOut = round((float) ($movement->qty_out ?? 0), 2);
            $qtyInAfterPeriod = (float) ($movementAfterPeriod->qty_in ?? 0);
            $qtyOutAfterPeriod = (float) ($movementAfterPeriod->qty_out ?? 0);
            $endingStock = round(max(0, $currentStock - $qtyInAfterPeriod + $qtyOutAfterPeriod), 2);
            $openingStock = round(max(0, $endingStock - $qtyIn + $qtyOut), 2);
            $averageStock = round(($openingStock + $endingStock) / 2, 2);
            $salesQty = round(max(0, (float) ($sale->qty ?? 0) - (float) ($return->qty ?? 0)), 2);
            $revenue = round(max(0, (float) ($sale->revenue ?? 0) - (float) ($return->revenue ?? 0)), 2);
            $averageDailyDemand = round($salesQty / $periodDays, 4);
            $lastOutAt = collect([$lastMovement->last_out_at ?? null, $sale->last_sale_at ?? null])
                ->filter()
                ->sortDesc()
                ->first();
            $inactivityAnchor = $lastOutAt ?? $stockRow->first_stocked_at ?? null;
            $daysSinceOut = $inactivityAnchor
                ? max(0, Carbon::parse($inactivityAnchor)->startOfDay()->diffInDays($today))
                : null;
            $purchaseConversions = $medicine->konversiSatuan
                ->filter(fn ($conversion) => (int) $conversion->konversi > 0
                    && $conversion->satuan
                    && $conversion->satuan->is_active)
                ->sortBy('konversi')
                ->values();
            $purchaseConversion = $purchaseConversions->firstWhere('is_default', true)
                ?? $purchaseConversions->first();
            $purchaseOrderIssue = match (true) {
                ! $purchaseConversion => 'Konversi satuan pembelian belum diatur.',
                default => null,
            };

            return [
                'id' => (int) $medicine->id,
                'code' => $medicine->kode_obat ?: '-',
                'name' => $medicine->nama_obat ?: 'Obat #'.$medicine->id,
                'unit' => $medicine->satuan?->nama ?: 'unit',
                'supplier' => $medicine->distributor?->nama ?: '-',
                'distributor_id' => $medicine->distributor_id ? (int) $medicine->distributor_id : null,
                'purchase_conversion_id' => $purchaseConversion ? (int) $purchaseConversion->id : null,
                'purchase_conversion_factor' => $purchaseConversion ? (int) $purchaseConversion->konversi : null,
                'purchase_unit' => $purchaseConversion?->satuan?->nama,
                'can_create_po' => $purchaseOrderIssue === null,
                'po_issue' => $purchaseOrderIssue,
                'minimum_stock' => round((float) ($medicine->stok_minimum ?? 0), 2),
                'current_stock' => $currentStock,
                'usable_stock' => $usableStock,
                'expired_stock' => round((float) ($stockRow->expired_stock ?? 0), 2),
                'opening_stock' => $openingStock,
                'ending_stock' => $endingStock,
                'average_stock' => $averageStock,
                'stock_value' => $stockValue,
                'average_purchase_price' => $currentStock > 0 ? round($stockValue / $currentStock, 2) : round((float) ($medicine->harga_beli ?? 0), 2),
                'usable_stock_value' => $usableStockValue,
                'average_usable_purchase_price' => $usableStock > 0 ? round($usableStockValue / $usableStock, 2) : round((float) ($medicine->harga_beli ?? 0), 2),
                'pending_order_qty' => round((float) ($pendingOrder->pending_order_qty ?? 0), 2),
                'batch_count' => (int) ($stockRow->batch_count ?? 0),
                'nearest_expiry' => $stockRow->nearest_expiry ?? null,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'net_movement' => round($qtyIn - $qtyOut, 2),
                'movement_count' => (int) ($movement->movement_count ?? 0),
                'last_movement_at' => $lastMovement->last_movement_at ?? $stockRow->last_batch_movement_at ?? null,
                'last_out_at' => $lastOutAt,
                'days_since_out' => $daysSinceOut,
                'sales_qty' => $salesQty,
                'revenue' => $revenue,
                'sales_transaction_count' => (int) ($sale->transaction_count ?? 0),
                'last_sale_at' => $sale->last_sale_at ?? null,
                'average_daily_demand' => $averageDailyDemand,
                'days_cover' => $averageDailyDemand > 0 ? round($currentStock / $averageDailyDemand, 1) : null,
                'stock_turnover' => $averageStock > 0 ? round($qtyOut / $averageStock, 2) : null,
            ];
        })->values();
    }

    private function stockMovement(Collection $rows): array
    {
        $rows = $rows
            ->filter(fn (array $row) => $row['movement_count'] > 0)
            ->sortByDesc(fn (array $row) => $row['qty_in'] + $row['qty_out'])
            ->values()
            ->map(fn (array $row, int $index) => ['rank' => $index + 1, ...$row]);

        return [
            'summary' => [
                $this->summary('Produk bergerak', $rows->count(), 'number', 'blue', 'mdi-pill-multiple'),
                $this->summary('Total stok masuk', $rows->sum('qty_in'), 'decimal', 'green', 'mdi-arrow-down-bold-circle-outline'),
                $this->summary('Total stok keluar', $rows->sum('qty_out'), 'decimal', 'orange', 'mdi-arrow-up-bold-circle-outline'),
                $this->summary('Pergerakan bersih', $rows->sum('net_movement'), 'decimal', 'violet', 'mdi-scale-balance'),
            ],
            'distribution' => $rows->take(8)->map(fn (array $row) => [
                'label' => $row['name'],
                'value' => round($row['qty_in'] + $row['qty_out'], 2),
                'tone' => 'blue',
            ])->values()->all(),
            'rows' => $rows->all(),
            'empty' => 'Belum ada mutasi stok pada periode yang dipilih.',
        ];
    }

    private function pareto(Collection $rows): array
    {
        $rows = $rows->filter(fn (array $row) => $row['revenue'] > 0)->sortByDesc('revenue')->values();
        $totalRevenue = max(0, (float) $rows->sum('revenue'));
        $cumulativeRevenue = 0.0;

        $rows = $rows->map(function (array $row, int $index) use ($totalRevenue, &$cumulativeRevenue) {
            $contribution = $totalRevenue > 0 ? ($row['revenue'] / $totalRevenue) * 100 : 0;
            $cumulativeRevenue += $row['revenue'];
            $cumulative = $totalRevenue > 0 ? ($cumulativeRevenue / $totalRevenue) * 100 : 0;
            $class = $index === 0 || $cumulative <= 80
                ? 'A'
                : ($cumulative <= 95 ? 'B' : 'C');

            return [
                'rank' => $index + 1,
                'abc_class' => $class,
                'contribution' => round($contribution, 2),
                'cumulative' => round(min(100, $cumulative), 2),
                ...$row,
            ];
        });

        $classRevenue = fn (string $class) => round((float) $rows->where('abc_class', $class)->sum('revenue'), 2);

        return [
            'summary' => [
                $this->summary('Omzet dianalisis', $totalRevenue, 'currency', 'violet', 'mdi-cash-multiple'),
                $this->summary('Kelas A', $rows->where('abc_class', 'A')->count(), 'number', 'green', 'mdi-alpha-a-circle'),
                $this->summary('Kelas B', $rows->where('abc_class', 'B')->count(), 'number', 'amber', 'mdi-alpha-b-circle'),
                $this->summary('Kelas C', $rows->where('abc_class', 'C')->count(), 'number', 'red', 'mdi-alpha-c-circle'),
            ],
            'distribution' => collect(['A' => 'green', 'B' => 'amber', 'C' => 'red'])->map(fn ($tone, $class) => [
                'label' => 'Kelas '.$class,
                'value' => $classRevenue($class),
                'tone' => $tone,
            ])->values()->all(),
            'rows' => $rows->all(),
            'empty' => 'Belum ada penjualan selesai pada periode yang dipilih.',
        ];
    }

    private function lowStock(Collection $rows): array
    {
        $rows = $rows
            ->filter(fn (array $row) => $row['minimum_stock'] > 0 && $row['current_stock'] <= $row['minimum_stock'])
            ->map(function (array $row) {
                $row['shortage'] = round(max(0, $row['minimum_stock'] - $row['current_stock']), 2);
                $row['stock_status'] = $row['current_stock'] <= 0 ? 'Kosong' : 'Menipis';
                $row['priority'] = $row['current_stock'] <= 0 ? 'Tinggi' : 'Sedang';

                return $row;
            })
            ->sortBy([
                fn (array $a, array $b) => ($a['current_stock'] <= 0 ? 0 : 1) <=> ($b['current_stock'] <= 0 ? 0 : 1),
                fn (array $a, array $b) => $b['shortage'] <=> $a['shortage'],
            ])->values();

        return [
            'summary' => [
                $this->summary('Perlu perhatian', $rows->count(), 'number', 'amber', 'mdi-alert-circle-outline'),
                $this->summary('Stok kosong', $rows->where('current_stock', '<=', 0)->count(), 'number', 'red', 'mdi-package-variant-remove'),
                $this->summary('Stok menipis', $rows->where('current_stock', '>', 0)->count(), 'number', 'orange', 'mdi-package-variant-minus'),
                $this->summary('Estimasi nilai tersisa', $rows->sum('stock_value'), 'currency', 'blue', 'mdi-cash-lock'),
            ],
            'distribution' => [
                ['label' => 'Kosong', 'value' => $rows->where('current_stock', '<=', 0)->count(), 'tone' => 'red'],
                ['label' => 'Menipis', 'value' => $rows->where('current_stock', '>', 0)->count(), 'tone' => 'amber'],
            ],
            'rows' => $rows->all(),
            'empty' => 'Tidak ada produk yang berada di bawah stok minimum.',
        ];
    }

    private function slowMoving(Collection $rows, array $filters): array
    {
        $rows = $rows
            ->filter(fn (array $row) => $row['current_stock'] > 0
                && $row['days_since_out'] !== null
                && $row['days_since_out'] >= $filters['slow_days']
                && $row['days_since_out'] < $filters['dead_days'])
            ->sortByDesc('days_since_out')
            ->values();

        return [
            'summary' => [
                $this->summary('Produk slow moving', $rows->count(), 'number', 'orange', 'mdi-timer-sand'),
                $this->summary('Nilai stok tertahan', $rows->sum('stock_value'), 'currency', 'red', 'mdi-cash-lock'),
                $this->summary('Total unit tersimpan', $rows->sum('current_stock'), 'decimal', 'amber', 'mdi-package-variant-closed'),
                $this->summary('Rata-rata tidak bergerak', $rows->avg('days_since_out') ?: 0, 'days', 'blue', 'mdi-calendar-clock'),
            ],
            'distribution' => $this->agingDistribution($rows, (int) $filters['slow_days'], (int) $filters['dead_days']),
            'rows' => $rows->all(),
            'empty' => 'Tidak ada slow moving untuk batas hari yang dipilih.',
        ];
    }

    private function deadStock(Collection $rows, array $filters): array
    {
        $rows = $rows
            ->filter(fn (array $row) => $row['current_stock'] > 0
                && ($row['days_since_out'] === null || $row['days_since_out'] >= $filters['dead_days']))
            ->sortByDesc('stock_value')
            ->values();

        return [
            'summary' => [
                $this->summary('Produk dead stock', $rows->count(), 'number', 'red', 'mdi-package-variant-remove'),
                $this->summary('Modal tertahan', $rows->sum('stock_value'), 'currency', 'red', 'mdi-cash-lock'),
                $this->summary('Total unit mati', $rows->sum('current_stock'), 'decimal', 'orange', 'mdi-package-variant'),
                $this->summary('Belum pernah keluar', $rows->whereNull('last_out_at')->count(), 'number', 'violet', 'mdi-history'),
            ],
            'distribution' => $rows->sortByDesc('stock_value')->take(8)->map(fn (array $row) => [
                'label' => $row['name'],
                'value' => $row['stock_value'],
                'tone' => 'red',
            ])->values()->all(),
            'rows' => $rows->all(),
            'empty' => 'Tidak ada dead stock untuk batas hari yang dipilih.',
        ];
    }

    private function purchaseSuggestion(Collection $rows, array $filters): array
    {
        $coverDays = (int) $filters['cover_days'];
        $leadDays = (int) $filters['lead_days'];
        $rows = $rows->map(function (array $row) use ($coverDays, $leadDays) {
            $averageDailyDemand = (float) $row['average_daily_demand'];
            $usableStock = (float) $row['usable_stock'];
            $pendingOrderQty = (float) $row['pending_order_qty'];
            $projectedStock = $usableStock + $pendingOrderQty;
            $demandTarget = $averageDailyDemand * $coverDays;
            $targetStock = (float) ceil($demandTarget + $row['minimum_stock']);
            $reorderPoint = (float) ceil(($averageDailyDemand * $leadDays) + $row['minimum_stock']);
            $suggestedQty = (float) max(0, ceil($targetStock - $projectedStock));
            $daysCover = $averageDailyDemand > 0 ? round($usableStock / $averageDailyDemand, 1) : null;
            $projectedDaysCover = $averageDailyDemand > 0 ? round($projectedStock / $averageDailyDemand, 1) : null;

            $row['physical_stock'] = $row['current_stock'];
            $row['current_stock'] = $usableStock;
            $row['stock_value'] = $row['usable_stock_value'];
            $row['average_purchase_price'] = $row['average_usable_purchase_price'];
            $row['days_cover'] = $daysCover;
            $row['projected_stock'] = round($projectedStock, 2);
            $row['projected_days_cover'] = $projectedDaysCover;
            $row['reorder_point'] = $reorderPoint;
            $row['target_stock'] = $targetStock;
            $row['suggested_qty'] = $suggestedQty;
            $row['estimated_purchase'] = round($suggestedQty * $row['average_purchase_price'], 2);
            $row['shortage_ratio'] = $targetStock > 0 ? round(($suggestedQty / $targetStock) * 100, 2) : 0;
            $row['purchase_eligible'] = $averageDailyDemand > 0;
            $row['priority'] = $usableStock <= 0 || ($daysCover !== null && $daysCover <= $leadDays)
                ? 'Tinggi'
                : ($projectedStock <= $reorderPoint ? 'Sedang' : 'Terencana');
            unset($row['supplier'], $row['distributor_id']);

            return $row;
        })->filter(fn (array $row) => $row['purchase_eligible'] && $row['suggested_qty'] > 0)
            ->sortBy([
                fn (array $a, array $b) => ['Tinggi' => 0, 'Sedang' => 1, 'Terencana' => 2][$a['priority']] <=> ['Tinggi' => 0, 'Sedang' => 1, 'Terencana' => 2][$b['priority']],
                fn (array $a, array $b) => $a['days_cover'] <=> $b['days_cover'],
                fn (array $a, array $b) => $b['average_daily_demand'] <=> $a['average_daily_demand'],
                fn (array $a, array $b) => $b['shortage_ratio'] <=> $a['shortage_ratio'],
            ])->values();

        return [
            'summary' => [
                $this->summary('Produk disarankan', $rows->count(), 'number', 'green', 'mdi-cart-arrow-down'),
                $this->summary('Total unit pembelian', $rows->sum('suggested_qty'), 'decimal', 'blue', 'mdi-package-variant-plus'),
                $this->summary('Estimasi anggaran', $rows->sum('estimated_purchase'), 'currency', 'violet', 'mdi-cash-multiple'),
                $this->summary('Prioritas tinggi', $rows->where('priority', 'Tinggi')->count(), 'number', 'red', 'mdi-alert-decagram-outline'),
            ],
            'distribution' => collect(['Tinggi' => 'red', 'Sedang' => 'amber', 'Terencana' => 'green'])->map(fn ($tone, $priority) => [
                'label' => $priority,
                'value' => $rows->where('priority', $priority)->count(),
                'tone' => $tone,
            ])->values()->all(),
            'rows' => $rows->all(),
            'empty' => 'Belum ada produk yang membutuhkan pembelian untuk parameter saat ini.',
        ];
    }

    private function agingDistribution(Collection $rows, int $slowDays, int $deadDays): array
    {
        $span = max(1, $deadDays - $slowDays);
        $firstLimit = $slowDays + (int) ceil($span / 2);

        return [
            [
                'label' => $slowDays.'–'.($firstLimit - 1).' hari',
                'value' => $rows->filter(fn (array $row) => $row['days_since_out'] < $firstLimit)->count(),
                'tone' => 'amber',
            ],
            [
                'label' => $firstLimit.'–'.($deadDays - 1).' hari',
                'value' => $rows->filter(fn (array $row) => $row['days_since_out'] >= $firstLimit)->count(),
                'tone' => 'orange',
            ],
        ];
    }

    private function summary(string $label, float|int $value, string $format, string $tone, string $icon): array
    {
        return compact('label', 'value', 'format', 'tone', 'icon');
    }
}
