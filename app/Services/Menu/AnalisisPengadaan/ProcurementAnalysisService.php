<?php

namespace App\Services\Menu\AnalisisPengadaan;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementAnalysisService
{
    public const PO_STATUSES = [
        'draft', 'waiting_approval', 'approved', 'diterima_sebagian', 'selesai', 'rejected',
    ];

    public const RECEIPT_STATUSES = ['none', 'partial', 'complete'];

    private const STATUS_LABELS = [
        'draft' => 'Draft',
        'waiting_approval' => 'Menunggu',
        'approved' => 'Approved',
        'diterima_sebagian' => 'Partial',
        'selesai' => 'Selesai',
        'rejected' => 'Batal / Ditolak',
    ];

    private const MONTHS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    public function build(User $user, array $filters): array
    {
        $context = $this->context($user, $filters['branch_id'] ?? null);
        [$previousStart, $previousEnd] = $this->previousPeriod($filters['start'], $filters['end']);
        $lines = $this->orderLines($context['branch_ids'], $filters, $filters['start'], $filters['end']);
        $previousLines = $this->orderLines($context['branch_ids'], $filters, $previousStart, $previousEnd);
        $activeLines = $lines->where('is_cancelled', false)->values();
        $previousActiveLines = $previousLines->where('is_cancelled', false)->values();
        $leadTimes = $this->leadTimes($activeLines);
        $previousLeadTimes = $this->leadTimes($previousActiveLines);
        $summary = $this->summary($activeLines, $previousActiveLines, $leadTimes, $previousLeadTimes);
        $medicineRows = $this->medicineRows($activeLines, $context['branch_ids'], $filters);
        $supplierRows = $this->supplierRows($activeLines, $leadTimes);
        $categoryRows = $this->categoryRows($activeLines);
        $options = $this->options($context['branch_ids'], $context['branches']);

        return [
            'meta' => [
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'previous_date_start' => $previousStart->toDateString(),
                'previous_date_end' => $previousEnd->toDateString(),
                'period_label' => $this->dateLabel($filters['start']).' - '.$this->dateLabel($filters['end']),
                'previous_period_label' => $this->dateLabel($previousStart).' - '.$this->dateLabel($previousEnd),
                'branch_label' => $context['branch_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'granularity' => $filters['granularity'],
                'generated_at' => now()->format('Y-m-d H:i:s'),
                'active_filters' => $this->activeFilterLabels($filters, $options),
                'methodology' => [
                    'quantity' => 'Qty pembelian dinormalisasi ke satuan stok menggunakan konversi pada item PO.',
                    'cancelled' => 'PO berstatus rejected dikelompokkan sebagai order dibatalkan.',
                    'need' => 'Kebutuhan = penjualan 30 hari + stok minimum - estimasi stok saat order terakhir.',
                    'value' => 'Nilai dan harga pada analisis berasal dari estimasi item PO. Harga aktual berasal dari penerimaan posted dan tersedia pada Laporan Realisasi Pembelian.',
                ],
            ],
            'summary' => $summary,
            'trend' => $this->trend($activeLines, $previousActiveLines, $context['branch_ids'], $filters, $previousStart, $previousEnd),
            'medicines' => $medicineRows,
            'top_frequency' => $medicineRows->sortByDesc('order_count')->take(10)->values()->all(),
            'top_quantity' => $medicineRows->sortByDesc('ordered_qty')->take(10)->values()->all(),
            'top_value' => $medicineRows->sortByDesc('order_value')->take(10)->values()->all(),
            'suppliers' => $supplierRows,
            'categories' => $categoryRows,
            'status_distribution' => $this->statusDistribution($lines),
            'outstanding' => $this->outstandingRows($activeLines),
            'cancelled_orders' => $this->cancelledRows($lines),
            'lead_time_suppliers' => $supplierRows->whereNotNull('lead_time_days')->sortBy('lead_time_days')->values()->all(),
            'reorder' => $medicineRows->where('order_count', '>', 1)->sortBy('reorder_days')->values()->all(),
            'price_changes' => $medicineRows->filter(fn (array $row) => $row['order_count'] > 1)->sortByDesc(fn (array $row) => abs($row['price_change_percent']))->values()->all(),
            'need_analysis' => $medicineRows->sortBy(fn (array $row) => $this->needStatusPriority($row['need_status']))->values()->all(),
            'moving_distribution' => $this->movingDistribution($medicineRows),
            'insights' => $this->insights($summary, $medicineRows, $supplierRows),
            'options' => $options,
        ];
    }

    public function medicineDetail(User $user, int $medicineId, array $filters): array
    {
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $medicine = MasterObatModel::query()
            ->with(['satuan', 'kategori', 'golongan', 'pabrikan'])
            ->findOrFail($medicineId);
        $filters['medicine_id'] = $medicineId;
        $lines = $this->orderLines($context['branch_ids'], $filters, $filters['start'], $filters['end']);
        $activeLines = $lines->where('is_cancelled', false)->values();
        $leadTimes = $this->leadTimes($activeLines);
        $row = $this->medicineRows($activeLines, $context['branch_ids'], $filters)->first();
        $stock = $this->currentStocks($context['branch_ids'], [$medicineId])->get($medicineId, 0);
        $sales30 = $this->salesByMedicine(
            $context['branch_ids'],
            [$medicineId],
            $filters['end']->copy()->subDays(29)->startOfDay(),
            $filters['end'],
        )->get($medicineId, 0);

        $prices = $activeLines->pluck('unit_price')->filter(fn ($price) => $price !== null)->map(fn ($price) => (float) $price);
        $orders = $activeLines->groupBy('order_id');
        $mainSupplier = $activeLines->groupBy('supplier_name')->sortByDesc(fn (Collection $items) => $items->sum('line_value'))->keys()->first();

        return [
            'id' => (int) $medicine->id,
            'code' => $medicine->kode_obat ?: '-',
            'name' => $medicine->nama_obat ?: 'Obat #'.$medicine->id,
            'unit' => $medicine->satuan?->nama ?: 'unit',
            'category' => $medicine->kategori?->name ?: 'Tanpa kategori',
            'classification' => $medicine->golongan?->nama ?: '-',
            'manufacturer' => $medicine->pabrikan?->nama ?: '-',
            'summary' => [
                'ordered_qty' => round((float) $activeLines->sum('ordered_qty'), 2),
                'order_value' => round((float) $activeLines->sum('line_value'), 2),
                'order_count' => $orders->count(),
                'average_per_order' => $orders->count() > 0 ? round((float) $activeLines->sum('ordered_qty') / $orders->count(), 2) : 0,
                'main_supplier' => $mainSupplier ?: '-',
                'last_price' => $prices->isNotEmpty() ? round((float) $activeLines->sortByDesc(fn ($line) => $line->order_date.'-'.$line->line_id)->first()->unit_price, 2) : 0,
                'lowest_price' => round((float) ($prices->min() ?? 0), 2),
                'highest_price' => round((float) ($prices->max() ?? 0), 2),
                'average_price' => round((float) ($prices->avg() ?? 0), 2),
                'lead_time_days' => $leadTimes->isNotEmpty() ? round((float) $leadTimes->avg('days'), 1) : null,
                'sales_30_days' => round((float) $sales30, 2),
                'current_stock' => round((float) $stock, 2),
                'need_status' => $row['need_status'] ?? 'evaluation',
                'need_status_label' => $row['need_status_label'] ?? 'Perlu Evaluasi',
            ],
            'history' => $lines->sortByDesc(fn ($line) => $line->order_date.'-'.$line->line_id)->map(fn ($line) => [
                'id' => (int) $line->line_id,
                'no_po' => $line->no_po,
                'date' => $line->order_date,
                'supplier' => $line->supplier_name,
                'qty' => round((float) $line->ordered_qty, 2),
                'received_qty' => round((float) $line->received_qty, 2),
                'unit' => $line->stock_unit ?: 'unit',
                'price' => round((float) $line->unit_price, 2),
                'value' => round((float) $line->line_value, 2),
                'status' => $line->status,
                'status_label' => self::STATUS_LABELS[$line->status] ?? ucfirst($line->status),
                'receipt_status' => $line->receipt_status,
            ])->values()->all(),
            'price_trend' => $activeLines->sortBy(fn ($line) => $line->order_date.'-'.$line->line_id)->map(fn ($line) => [
                'date' => $line->order_date,
                'label' => Carbon::parse($line->order_date)->format('d M Y'),
                'price' => round((float) $line->unit_price, 2),
            ])->values()->all(),
        ];
    }

    private function context(User $user, ?int $selectedBranchId): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds === [] ? [-1] : $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        if ($selectedBranchId !== null && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak tersedia untuk akun ini.']);
        }

        return [
            'branches' => $branches,
            'branch_ids' => $selectedBranchId !== null ? [$selectedBranchId] : $allowedIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId !== null
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function previousPeriod(Carbon $start, Carbon $end): array
    {
        $days = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();

        return [$previousEnd->copy()->subDays($days - 1)->startOfDay(), $previousEnd];
    }

    private function orderLines(array $branchIds, array $filters, Carbon $start, Carbon $end): Collection
    {
        if ($branchIds === []) {
            return collect();
        }

        $query = DB::table('purchase_order_details as details')
            ->join('purchase_orders as orders', 'orders.id', '=', 'details.purchase_order_id')
            ->join('distributors as suppliers', 'suppliers.id', '=', 'orders.distributor_id')
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->leftJoin('golongan_obats as classifications', 'classifications.id', '=', 'medicines.golongan_id')
            ->leftJoin('pabrikan as manufacturers', 'manufacturers.id', '=', 'medicines.pabrikan_id')
            ->leftJoin('satuans as stock_units', 'stock_units.id', '=', 'medicines.satuan_id')
            ->leftJoin('obat_satuan_conversions as conversions', 'conversions.id', '=', 'details.satuan_konversi')
            ->whereIn('orders.branch_id', $branchIds)
            ->whereBetween('orders.tanggal_po', [$start->toDateString(), $end->toDateString()])
            ->when($filters['supplier_id'], fn ($builder, $id) => $builder->where('orders.distributor_id', $id))
            ->when($filters['medicine_id'], fn ($builder, $id) => $builder->where('details.obat_id', $id))
            ->when($filters['category_id'], fn ($builder, $id) => $builder->where('medicines.category_id', $id))
            ->when($filters['golongan_id'], fn ($builder, $id) => $builder->where('medicines.golongan_id', $id))
            ->when($filters['manufacturer_id'], fn ($builder, $id) => $builder->where('medicines.pabrikan_id', $id))
            ->when($filters['po_status'], fn ($builder, $status) => $builder->where('orders.status', $status))
            ->when($filters['created_by'], fn ($builder, $id) => $builder->where('orders.created_by', $id))
            ->select([
                'details.id as line_id', 'details.obat_id as medicine_id', 'details.qty as purchase_qty',
                'details.harga_estimasi as purchase_price', 'details.subtotal',
                'orders.id as order_id', 'orders.no_po', 'orders.branch_id', 'orders.distributor_id as supplier_id',
                'orders.tanggal_po as order_date', 'orders.status', 'orders.created_by',
                'suppliers.nama as supplier_name', 'medicines.kode_obat as medicine_code',
                'medicines.nama_obat as medicine_name', 'medicines.stok_minimum as minimum_stock',
                'medicines.category_id', 'medicines.golongan_id', 'medicines.pabrikan_id as manufacturer_id',
                'categories.name as category_name', 'classifications.nama as classification_name',
                'manufacturers.nama as manufacturer_name', 'stock_units.nama as stock_unit',
                'conversions.konversi as conversion_factor',
            ])
            ->orderBy('orders.tanggal_po')
            ->orderBy('orders.id')
            ->get();

        if ($query->isEmpty()) {
            return collect();
        }

        $received = DB::table('penerimaan_barang_detail as receipt_details')
            ->join('penerimaan_barang as receipts', 'receipts.id', '=', 'receipt_details.penerimaan_barang_id')
            ->where('receipts.status', 'posted')
            ->whereIn('receipt_details.purchase_order_detail_id', $query->pluck('line_id')->all())
            ->groupBy('receipt_details.purchase_order_detail_id')
            ->select('receipt_details.purchase_order_detail_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN receipt_details.qty_diterima_stok > 0 THEN receipt_details.qty_diterima_stok ELSE receipt_details.qty_diterima * COALESCE(NULLIF(receipt_details.konversi_satuan, 0), 1) END), 0) as received_qty')
            ->get()
            ->keyBy('purchase_order_detail_id');

        $lines = $query->map(function ($line) use ($received) {
            $factor = max(0.0001, (float) ($line->conversion_factor ?: 1));
            $line->ordered_qty = round((float) $line->purchase_qty * $factor, 2);
            $line->received_qty = round((float) ($received->get($line->line_id)?->received_qty ?? 0), 2);
            $line->outstanding_qty = round(max(0, $line->ordered_qty - $line->received_qty), 2);
            $line->unit_price = round((float) $line->purchase_price / $factor, 2);
            $line->line_value = round((float) ($line->subtotal > 0 ? $line->subtotal : $line->purchase_qty * $line->purchase_price), 2);
            $line->outstanding_value = $line->ordered_qty > 0
                ? round($line->line_value * ($line->outstanding_qty / $line->ordered_qty), 2)
                : 0;
            $line->is_cancelled = $line->status === 'rejected';

            return $line;
        });

        $orderReceiptStatuses = $lines->groupBy('order_id')->map(function (Collection $items) {
            $ordered = (float) $items->sum('ordered_qty');
            $receivedQty = (float) $items->sum('received_qty');

            return match (true) {
                $receivedQty <= 0 => 'none',
                $ordered > 0 && $receivedQty + 0.0001 < $ordered => 'partial',
                default => 'complete',
            };
        });
        $lines->each(fn ($line) => $line->receipt_status = $orderReceiptStatuses->get($line->order_id, 'none'));

        return $filters['receipt_status']
            ? $lines->where('receipt_status', $filters['receipt_status'])->values()
            : $lines->values();
    }

    private function summary(Collection $lines, Collection $previous, Collection $leadTimes, Collection $previousLeadTimes): array
    {
        $current = $this->summaryValues($lines, $leadTimes);
        $before = $this->summaryValues($previous, $previousLeadTimes);
        $result = [];

        foreach ($current as $key => $value) {
            $result[$key] = $value;
            $result[$key.'_previous'] = $before[$key];
            $result[$key.'_change'] = $this->growth((float) $value, (float) $before[$key]);
        }

        return $result;
    }

    private function summaryValues(Collection $lines, Collection $leadTimes): array
    {
        return [
            'order_value' => round((float) $lines->sum('line_value'), 2),
            'total_po' => $lines->pluck('order_id')->unique()->count(),
            'total_items' => $lines->count(),
            'ordered_qty' => round((float) $lines->sum('ordered_qty'), 2),
            'outstanding_value' => round((float) $lines->sum('outstanding_value'), 2),
            'lead_time_days' => $leadTimes->isNotEmpty() ? round((float) $leadTimes->avg('days'), 1) : 0,
            'active_suppliers' => $lines->pluck('supplier_id')->unique()->count(),
        ];
    }

    private function leadTimes(Collection $lines): Collection
    {
        $orders = $lines->groupBy('order_id');
        if ($orders->isEmpty()) {
            return collect();
        }

        $receipts = DB::table('penerimaan_barang')
            ->where('status', 'posted')
            ->whereIn('purchase_order_id', $orders->keys()->all())
            ->groupBy('purchase_order_id')
            ->select('purchase_order_id')
            ->selectRaw('MIN(tanggal_penerimaan) as first_receipt_date')
            ->get()
            ->keyBy('purchase_order_id');

        return $orders->map(function (Collection $items, $orderId) use ($receipts) {
            $receiptDate = $receipts->get($orderId)?->first_receipt_date;
            if (! $receiptDate) {
                return null;
            }
            $first = $items->first();

            return [
                'order_id' => (int) $orderId,
                'supplier_id' => (int) $first->supplier_id,
                'supplier_name' => $first->supplier_name,
                'days' => max(0, Carbon::parse($first->order_date)->diffInDays(Carbon::parse($receiptDate))),
            ];
        })->filter()->values();
    }

    private function medicineRows(Collection $lines, array $branchIds, array $filters): Collection
    {
        if ($lines->isEmpty()) {
            return collect();
        }

        $medicineIds = $lines->pluck('medicine_id')->unique()->values()->all();
        $sales = $this->salesByMedicine($branchIds, $medicineIds, $filters['start'], $filters['end']);
        $sales30 = $this->salesByMedicine($branchIds, $medicineIds, $filters['end']->copy()->subDays(29)->startOfDay(), $filters['end']);
        $stocks = $this->currentStocks($branchIds, $medicineIds);
        $lastSales = $this->lastSales($branchIds, $medicineIds, $filters['end']);
        $leadTimes = $this->leadTimes($lines)->keyBy('order_id');

        return $lines->groupBy('medicine_id')->map(function (Collection $items, $medicineId) use ($sales, $sales30, $stocks, $lastSales, $leadTimes, $filters) {
            $items = $items->sortBy(fn ($line) => $line->order_date.'-'.str_pad((string) $line->line_id, 12, '0', STR_PAD_LEFT));
            $first = $items->first();
            $last = $items->last();
            $orders = $items->groupBy('order_id');
            $prices = $items->pluck('unit_price')->map(fn ($price) => (float) $price);
            $orderDates = $orders->map(fn (Collection $orderItems) => Carbon::parse($orderItems->first()->order_date))->sort()->values();
            $gaps = $orderDates->sliding(2)->map(fn (Collection $pair) => $pair->first()->diffInDays($pair->last()));
            $orderedQty = round((float) $items->sum('ordered_qty'), 2);
            $receivedQty = round((float) $items->sum('received_qty'), 2);
            $salesQty = round((float) $sales->get($medicineId, 0), 2);
            $sales30Qty = round((float) $sales30->get($medicineId, 0), 2);
            $currentStock = round((float) $stocks->get($medicineId, 0), 2);
            $stockAtOrder = $this->stockAtOrder((int) $medicineId, (int) $last->branch_id, Carbon::parse($last->order_date), $currentStock);
            $need = max(0, $sales30Qty + (float) $last->minimum_stock - $stockAtOrder);
            $lastOrderQty = (float) $last->ordered_qty;
            [$needStatus, $needLabel] = $this->needStatus($lastOrderQty, $need);
            $lastSale = $lastSales->get($medicineId);
            $daysSinceSale = $lastSale ? Carbon::parse($lastSale)->startOfDay()->diffInDays($filters['end']->copy()->startOfDay()) : null;
            $movement = match (true) {
                $daysSinceSale !== null && $daysSinceSale <= 30 => 'fast',
                $daysSinceSale !== null && $daysSinceSale <= 90 => 'slow',
                default => 'non_moving',
            };
            $mainSupplier = $items->groupBy('supplier_name')->sortByDesc(fn (Collection $supplierItems) => $supplierItems->sum('line_value'))->keys()->first();
            $itemLeadTimes = $orders->keys()->map(fn ($orderId) => $leadTimes->get($orderId)['days'] ?? null)->filter(fn ($days) => $days !== null);

            return [
                'id' => (int) $medicineId,
                'code' => $first->medicine_code ?: '-',
                'name' => $first->medicine_name ?: 'Obat #'.$medicineId,
                'unit' => $first->stock_unit ?: 'unit',
                'category' => $first->category_name ?: 'Tanpa kategori',
                'classification' => $first->classification_name ?: '-',
                'manufacturer' => $first->manufacturer_name ?: '-',
                'main_supplier' => $mainSupplier ?: '-',
                'order_count' => $orders->count(),
                'ordered_qty' => $orderedQty,
                'received_qty' => $receivedQty,
                'outstanding_qty' => round(max(0, $orderedQty - $receivedQty), 2),
                'fulfillment_percent' => $orderedQty > 0 ? round(min(100, $receivedQty / $orderedQty * 100), 1) : 0,
                'order_value' => round((float) $items->sum('line_value'), 2),
                'outstanding_value' => round((float) $items->sum('outstanding_value'), 2),
                'average_per_order' => $orders->count() > 0 ? round($orderedQty / $orders->count(), 2) : 0,
                'first_price' => round((float) $prices->first(), 2),
                'last_price' => round((float) $prices->last(), 2),
                'lowest_price' => round((float) $prices->min(), 2),
                'highest_price' => round((float) $prices->max(), 2),
                'average_price' => round((float) $prices->avg(), 2),
                'price_change_percent' => $this->growth((float) $prices->last(), (float) $prices->first()),
                'reorder_days' => $gaps->isNotEmpty() ? round((float) $gaps->avg(), 1) : null,
                'lead_time_days' => $itemLeadTimes->isNotEmpty() ? round((float) $itemLeadTimes->avg(), 1) : null,
                'sales_qty' => $salesQty,
                'sales_30_days' => $sales30Qty,
                'order_sales_ratio' => $salesQty > 0 ? round($orderedQty / $salesQty, 2) : null,
                'current_stock' => $currentStock,
                'stock_at_order' => round($stockAtOrder, 2),
                'minimum_stock' => round((float) $last->minimum_stock, 2),
                'estimated_need' => round($need, 2),
                'last_order_qty' => round($lastOrderQty, 2),
                'need_ratio' => $need > 0 ? round($lastOrderQty / $need, 2) : null,
                'need_status' => $needStatus,
                'need_status_label' => $needLabel,
                'last_order_date' => $last->order_date,
                'last_sale_date' => $lastSale,
                'days_since_sale' => $daysSinceSale,
                'movement' => $movement,
                'movement_label' => match ($movement) {
                    'fast' => 'Fast Moving', 'slow' => 'Slow Moving', default => 'Non Moving'
                },
            ];
        })->sortByDesc('order_value')->values();
    }

    private function supplierRows(Collection $lines, Collection $leadTimes): Collection
    {
        $leadBySupplier = $leadTimes->groupBy('supplier_id');

        return $lines->groupBy('supplier_id')->map(function (Collection $items, $supplierId) use ($leadBySupplier) {
            $first = $items->first();
            $ordered = (float) $items->sum('ordered_qty');
            $received = (float) $items->sum('received_qty');
            $lead = $leadBySupplier->get($supplierId, collect());

            return [
                'id' => (int) $supplierId,
                'name' => $first->supplier_name,
                'po_count' => $items->pluck('order_id')->unique()->count(),
                'item_count' => $items->count(),
                'ordered_qty' => round($ordered, 2),
                'received_qty' => round($received, 2),
                'outstanding_qty' => round(max(0, $ordered - $received), 2),
                'order_value' => round((float) $items->sum('line_value'), 2),
                'outstanding_value' => round((float) $items->sum('outstanding_value'), 2),
                'fulfillment_percent' => $ordered > 0 ? round(min(100, $received / $ordered * 100), 1) : 0,
                'lead_time_days' => $lead->isNotEmpty() ? round((float) $lead->avg('days'), 1) : null,
            ];
        })->sortByDesc('order_value')->values();
    }

    private function categoryRows(Collection $lines): Collection
    {
        $total = max(0.0001, (float) $lines->sum('line_value'));

        return $lines->groupBy(fn ($line) => $line->category_name ?: 'Tanpa kategori')->map(function (Collection $items, string $name) use ($total) {
            $value = (float) $items->sum('line_value');

            return [
                'name' => $name,
                'po_count' => $items->pluck('order_id')->unique()->count(),
                'item_count' => $items->count(),
                'ordered_qty' => round((float) $items->sum('ordered_qty'), 2),
                'order_value' => round($value, 2),
                'contribution_percent' => round($value / $total * 100, 1),
            ];
        })->sortByDesc('order_value')->values();
    }

    private function statusDistribution(Collection $lines): array
    {
        $orders = $lines->groupBy('order_id');
        $total = max(1, $orders->count());

        return collect(self::STATUS_LABELS)->map(function (string $label, string $status) use ($orders, $total) {
            $matching = $orders->filter(fn (Collection $items) => $items->first()->status === $status);

            return [
                'key' => $status,
                'label' => $label,
                'count' => $matching->count(),
                'value' => round((float) $matching->sum(fn (Collection $items) => $items->sum('line_value')), 2),
                'percent' => round($matching->count() / $total * 100, 1),
            ];
        })->values()->all();
    }

    private function outstandingRows(Collection $lines): array
    {
        return $lines->where('outstanding_qty', '>', 0)->map(fn ($line) => [
            'id' => (int) $line->line_id,
            'medicine_id' => (int) $line->medicine_id,
            'no_po' => $line->no_po,
            'date' => $line->order_date,
            'medicine' => $line->medicine_name,
            'unit' => $line->stock_unit ?: 'unit',
            'supplier' => $line->supplier_name,
            'ordered_qty' => round((float) $line->ordered_qty, 2),
            'received_qty' => round((float) $line->received_qty, 2),
            'outstanding_qty' => round((float) $line->outstanding_qty, 2),
            'outstanding_value' => round((float) $line->outstanding_value, 2),
            'status' => $line->status,
            'status_label' => self::STATUS_LABELS[$line->status] ?? ucfirst($line->status),
        ])->sortByDesc('outstanding_value')->values()->all();
    }

    private function cancelledRows(Collection $lines): array
    {
        return $lines->where('is_cancelled', true)->groupBy('order_id')->map(function (Collection $items) {
            $first = $items->first();

            return [
                'id' => (int) $first->order_id,
                'no_po' => $first->no_po,
                'date' => $first->order_date,
                'supplier' => $first->supplier_name,
                'item_count' => $items->count(),
                'ordered_qty' => round((float) $items->sum('ordered_qty'), 2),
                'order_value' => round((float) $items->sum('line_value'), 2),
                'status_label' => 'Batal / Ditolak',
            ];
        })->sortByDesc('date')->values()->all();
    }

    private function trend(Collection $lines, Collection $previousLines, array $branchIds, array $filters, Carbon $previousStart, Carbon $previousEnd): array
    {
        $medicineIds = $lines->pluck('medicine_id')->unique()->values()->all();
        $sales = $this->salesByDate($branchIds, $medicineIds, $filters['start'], $filters['end']);
        $current = $this->bucketSeries($lines, $sales, $filters['start'], $filters['end'], $filters['granularity']);
        $previousSales = $this->salesByDate($branchIds, $previousLines->pluck('medicine_id')->unique()->values()->all(), $previousStart, $previousEnd);
        $previous = $this->bucketSeries($previousLines, $previousSales, $previousStart, $previousEnd, $filters['granularity']);

        return [
            'granularity' => $filters['granularity'],
            'labels' => $current->pluck('label')->all(),
            'order_value' => $current->pluck('order_value')->all(),
            'previous_order_value' => $current->keys()->map(fn ($index) => (float) ($previous->get($index)['order_value'] ?? 0))->all(),
            'ordered_qty' => $current->pluck('ordered_qty')->all(),
            'received_qty' => $current->pluck('received_qty')->all(),
            'sales_qty' => $current->pluck('sales_qty')->all(),
        ];
    }

    private function bucketSeries(Collection $lines, Collection $sales, Carbon $start, Carbon $end, string $granularity): Collection
    {
        $buckets = collect();
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end->copy()->startOfDay())) {
            $key = $this->bucketKey($cursor, $granularity);
            if (! $buckets->has($key)) {
                $buckets->put($key, [
                    'key' => $key,
                    'label' => $this->bucketLabel($cursor, $granularity),
                    'order_value' => 0.0,
                    'ordered_qty' => 0.0,
                    'received_qty' => 0.0,
                    'sales_qty' => 0.0,
                ]);
            }
            $cursor->addDay();
        }

        foreach ($lines as $line) {
            $key = $this->bucketKey(Carbon::parse($line->order_date), $granularity);
            if (! $buckets->has($key)) {
                continue;
            }
            $row = $buckets->get($key);
            $row['order_value'] += (float) $line->line_value;
            $row['ordered_qty'] += (float) $line->ordered_qty;
            $row['received_qty'] += (float) $line->received_qty;
            $buckets->put($key, $row);
        }
        foreach ($sales as $date => $qty) {
            $key = $this->bucketKey(Carbon::parse($date), $granularity);
            if (! $buckets->has($key)) {
                continue;
            }
            $row = $buckets->get($key);
            $row['sales_qty'] += (float) $qty;
            $buckets->put($key, $row);
        }

        return $buckets->values()->map(function (array $row) {
            foreach (['order_value', 'ordered_qty', 'received_qty', 'sales_qty'] as $field) {
                $row[$field] = round((float) $row[$field], 2);
            }

            return $row;
        });
    }

    private function salesByMedicine(array $branchIds, array $medicineIds, Carbon $start, Carbon $end): Collection
    {
        if ($branchIds === [] || $medicineIds === []) {
            return collect();
        }

        $sales = DB::table('penjualan_transaction_details as details')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->groupBy('details.obat_id')
            ->select('details.obat_id')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->get()
            ->keyBy('obat_id');
        $returns = DB::table('retur_penjualan_details as details')
            ->join('retur_penjualan as returns', 'returns.id', '=', 'details.retur_penjualan_id')
            ->whereIn('returns.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->where('returns.status', 'posted')
            ->whereBetween('returns.tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->groupBy('details.obat_id')
            ->select('details.obat_id')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->get()
            ->keyBy('obat_id');

        return collect($medicineIds)->mapWithKeys(fn ($id) => [
            (int) $id => round(max(0, (float) ($sales->get($id)?->qty ?? 0) - (float) ($returns->get($id)?->qty ?? 0)), 2),
        ]);
    }

    private function salesByDate(array $branchIds, array $medicineIds, Carbon $start, Carbon $end): Collection
    {
        if ($branchIds === [] || $medicineIds === []) {
            return collect();
        }
        $dateExpression = DB::connection()->getDriverName() === 'sqlite'
            ? 'DATE(sales.tanggal_transaksi)'
            : 'DATE(sales.tanggal_transaksi)';

        $sales = DB::table('penjualan_transaction_details as details')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end])
            ->groupByRaw($dateExpression)
            ->selectRaw($dateExpression.' as event_date')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->event_date => round((float) $row->qty, 2)]);

        $returnDateExpression = 'DATE(returns.tanggal_retur)';
        $returns = DB::table('retur_penjualan_details as details')
            ->join('retur_penjualan as returns', 'returns.id', '=', 'details.retur_penjualan_id')
            ->whereIn('returns.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->where('returns.status', 'posted')
            ->whereBetween('returns.tanggal_retur', [$start->toDateString(), $end->toDateString()])
            ->groupByRaw($returnDateExpression)
            ->selectRaw($returnDateExpression.' as event_date')
            ->selectRaw('COALESCE(SUM(details.qty_stok), 0) as qty')
            ->get()
            ->keyBy('event_date');

        return $sales->keys()->merge($returns->keys())->unique()->mapWithKeys(fn ($date) => [
            $date => round((float) $sales->get($date, 0) - (float) ($returns->get($date)?->qty ?? 0), 2),
        ]);
    }

    private function currentStocks(array $branchIds, array $medicineIds): Collection
    {
        if ($branchIds === [] || $medicineIds === []) {
            return collect();
        }

        return DB::table('stok_batches')
            ->whereIn('branch_id', $branchIds)
            ->whereIn('obat_id', $medicineIds)
            ->groupBy('obat_id')
            ->select('obat_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty > 0 THEN qty ELSE 0 END), 0) as qty')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->obat_id => round((float) $row->qty, 2)]);
    }

    private function lastSales(array $branchIds, array $medicineIds, Carbon $end): Collection
    {
        if ($branchIds === [] || $medicineIds === []) {
            return collect();
        }

        return DB::table('penjualan_transaction_details as details')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'details.penjualan_transaction_id')
            ->whereIn('sales.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->where('sales.status', 'completed')
            ->where('sales.tanggal_transaksi', '<=', $end)
            ->groupBy('details.obat_id')
            ->select('details.obat_id')
            ->selectRaw('MAX(sales.tanggal_transaksi) as last_sale')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->obat_id => $row->last_sale]);
    }

    private function stockAtOrder(int $medicineId, int $branchId, Carbon $orderDate, float $fallbackCurrentStock): float
    {
        $snapshot = DB::table('kartu_stok')
            ->where('branch_id', $branchId)
            ->where('obat_id', $medicineId)
            ->where('tanggal_mutasi', '<=', $orderDate->copy()->endOfDay())
            ->orderByDesc('tanggal_mutasi')
            ->orderByDesc('id')
            ->value('saldo_total');

        if ($snapshot === null) {
            $branchStock = DB::table('stok_batches')
                ->where('branch_id', $branchId)
                ->where('obat_id', $medicineId)
                ->selectRaw('COALESCE(SUM(CASE WHEN qty > 0 THEN qty ELSE 0 END), 0) as qty')
                ->value('qty');
            $snapshot = $branchStock ?? $fallbackCurrentStock;
        }

        return round(max(0, (float) $snapshot), 2);
    }

    private function needStatus(float $ordered, float $need): array
    {
        if ($need <= 0) {
            return $ordered > 0 ? ['over', 'Over Order'] : ['optimal', 'Optimal'];
        }
        $ratio = $ordered / $need;

        return match (true) {
            $ratio < 0.70 => ['under', 'Under Order'],
            $ratio < 0.85 => ['evaluation', 'Perlu Evaluasi'],
            $ratio <= 1.15 => ['optimal', 'Optimal'],
            $ratio <= 1.30 => ['evaluation', 'Perlu Evaluasi'],
            default => ['over', 'Over Order'],
        };
    }

    private function movingDistribution(Collection $rows): array
    {
        $total = max(0.0001, (float) $rows->sum('order_value'));
        $labels = ['fast' => 'Fast Moving', 'slow' => 'Slow Moving', 'non_moving' => 'Non Moving'];

        return collect($labels)->map(function (string $label, string $key) use ($rows, $total) {
            $matching = $rows->where('movement', $key);
            $value = (float) $matching->sum('order_value');

            return [
                'key' => $key,
                'label' => $label,
                'medicine_count' => $matching->count(),
                'ordered_qty' => round((float) $matching->sum('ordered_qty'), 2),
                'order_value' => round($value, 2),
                'percent' => round($value / $total * 100, 1),
            ];
        })->values()->all();
    }

    private function options(array $branchIds, Collection $branches): array
    {
        $scope = DB::table('purchase_orders')->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds);
        $supplierIds = (clone $scope)->distinct()->pluck('distributor_id');
        $creatorIds = (clone $scope)->whereNotNull('created_by')->distinct()->pluck('created_by');

        return [
            'branches' => $branches->map(fn ($branch) => ['id' => (int) $branch->id, 'code' => $branch->code, 'name' => $branch->name])->values()->all(),
            'suppliers' => DB::table('distributors')->whereIn('id', $supplierIds)->orderBy('nama')->get(['id', 'kode', 'nama as name'])->map(fn ($row) => (array) $row)->all(),
            'medicines' => DB::table('master_obats')->where('is_active', true)->orderBy('nama_obat')->get(['id', 'kode_obat as code', 'nama_obat as name'])->map(fn ($row) => (array) $row)->all(),
            'categories' => DB::table('categories')->orderBy('name')->get(['id', 'code', 'name'])->map(fn ($row) => (array) $row)->all(),
            'classifications' => DB::table('golongan_obats')->orderBy('nama')->get(['id', 'kode as code', 'nama as name'])->map(fn ($row) => (array) $row)->all(),
            'manufacturers' => DB::table('pabrikan')->orderBy('nama')->get(['id', 'kode as code', 'nama as name'])->map(fn ($row) => (array) $row)->all(),
            'creators' => DB::table('users')->whereIn('id', $creatorIds)->orderBy('name')->get(['id', 'name'])->map(fn ($row) => (array) $row)->all(),
            'po_statuses' => collect(self::STATUS_LABELS)->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values()->all(),
            'receipt_statuses' => [
                ['key' => 'none', 'label' => 'Belum diterima'],
                ['key' => 'partial', 'label' => 'Diterima sebagian'],
                ['key' => 'complete', 'label' => 'Diterima lengkap'],
            ],
        ];
    }

    private function activeFilterLabels(array $filters, array $options): array
    {
        $maps = [
            'supplier_id' => ['suppliers', 'Supplier'],
            'medicine_id' => ['medicines', 'Obat'],
            'category_id' => ['categories', 'Kategori'],
            'golongan_id' => ['classifications', 'Golongan'],
            'manufacturer_id' => ['manufacturers', 'Pabrikan'],
            'created_by' => ['creators', 'Pembuat'],
        ];
        $labels = collect();
        foreach ($maps as $key => [$optionKey, $prefix]) {
            if (! $filters[$key]) {
                continue;
            }
            $row = collect($options[$optionKey])->firstWhere('id', $filters[$key]);
            $labels->push($prefix.': '.($row['name'] ?? '#'.$filters[$key]));
        }
        if ($filters['po_status']) {
            $labels->push('Status PO: '.(self::STATUS_LABELS[$filters['po_status']] ?? $filters['po_status']));
        }
        if ($filters['receipt_status']) {
            $label = collect($options['receipt_statuses'])->firstWhere('key', $filters['receipt_status']);
            $labels->push('Penerimaan: '.($label['label'] ?? $filters['receipt_status']));
        }

        return $labels->all();
    }

    private function insights(array $summary, Collection $medicines, Collection $suppliers): array
    {
        $over = $medicines->where('need_status', 'over')->count();
        $under = $medicines->where('need_status', 'under')->count();
        $slowValue = (float) $medicines->whereIn('movement', ['slow', 'non_moving'])->sum('order_value');
        $bestSupplier = $suppliers->whereNotNull('lead_time_days')->sortBy('lead_time_days')->first();
        $fulfillment = $summary['ordered_qty'] > 0
            ? round(($summary['ordered_qty'] - $medicines->sum('outstanding_qty')) / $summary['ordered_qty'] * 100, 1)
            : 0;

        return [
            ['tone' => $over > 0 ? 'danger' : 'success', 'icon' => 'mdi-cart-arrow-up', 'title' => $over.' obat terindikasi over order', 'text' => $over > 0 ? 'Tinjau stok saat order dan kecepatan penjualan sebelum PO berikutnya.' : 'Belum ada indikasi pembelian berlebih pada periode aktif.'],
            ['tone' => $under > 0 ? 'warning' : 'success', 'icon' => 'mdi-cart-arrow-down', 'title' => $under.' obat terindikasi under order', 'text' => $under > 0 ? 'Prioritaskan obat dengan kebutuhan yang belum tercukupi.' : 'Kuantitas order telah mencukupi estimasi kebutuhan.'],
            ['tone' => $slowValue > 0 ? 'warning' : 'info', 'icon' => 'mdi-timer-sand', 'title' => 'Rp '.number_format($slowValue, 0, ',', '.').' untuk slow/non moving', 'text' => 'Nilai estimasi PO pada produk yang bergerak lambat atau belum terjual dalam 90 hari.'],
            ['tone' => 'info', 'icon' => 'mdi-truck-check-outline', 'title' => 'Fulfillment '.$fulfillment.'%', 'text' => $bestSupplier ? $bestSupplier['name'].' memiliki lead time tercepat ('.$bestSupplier['lead_time_days'].' hari).' : 'Lead time akan muncul setelah penerimaan posted tersedia.'],
        ];
    }

    private function growth(float $current, float $previous): float
    {
        if (abs($previous) < 0.0001) {
            return abs($current) < 0.0001 ? 0 : 100;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    private function bucketKey(Carbon $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            'month' => $date->format('Y-m'),
            'year' => $date->format('Y'),
            default => $date->format('Y-m-d'),
        };
    }

    private function bucketLabel(Carbon $date, string $granularity): string
    {
        return match ($granularity) {
            'week' => 'Minggu '.$date->copy()->startOfWeek(Carbon::MONDAY)->format('d M'),
            'month' => self::MONTHS[(int) $date->month].' '.$date->year,
            'year' => $date->format('Y'),
            default => $date->format('d M'),
        };
    }

    private function dateLabel(Carbon $date): string
    {
        return $date->format('d').' '.self::MONTHS[(int) $date->month].' '.$date->format('Y');
    }

    private function needStatusPriority(string $status): int
    {
        return match ($status) {
            'over', 'under' => 0, 'evaluation' => 1, default => 2
        };
    }
}
