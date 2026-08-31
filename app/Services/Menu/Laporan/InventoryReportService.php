<?php

namespace App\Services\Menu\Laporan;

use App\Models\BranchModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryReportService
{
    private const EXCESS_MULTIPLIER = 3;

    private const ADJUSTMENT_TYPES = [
        'penyesuaian_masuk',
        'penyesuaian_keluar',
        'penyesuaian_opname_masuk',
        'penyesuaian_opname_keluar',
    ];

    public const TYPES = [
        'posisi-stok' => [
            'title' => 'Posisi Stok',
            'short_title' => 'Posisi Stok',
            'description' => 'Lihat saldo seluruh obat, jumlah batch, stok minimum, dan nilai persediaan saat ini.',
            'icon' => 'mdi-warehouse',
            'tone' => 'navy',
        ],
        'stok-batch' => [
            'title' => 'Stok per Batch',
            'short_title' => 'Per Batch',
            'description' => 'Telusuri kuantitas, harga, nilai, dan tanggal kedaluwarsa pada setiap batch.',
            'icon' => 'mdi-package-variant-closed',
            'tone' => 'blue',
        ],
        'kartu-stok' => [
            'title' => 'Kartu Stok',
            'short_title' => 'Kartu Stok',
            'description' => 'Audit seluruh mutasi masuk dan keluar beserta saldo, referensi, serta petugas pencatat.',
            'icon' => 'mdi-card-bulleted-outline',
            'tone' => 'indigo',
        ],
        'stok-minimum' => [
            'title' => 'Stok Minimum',
            'short_title' => 'Stok Minimum',
            'description' => 'Prioritaskan obat dengan saldo yang sudah mencapai atau berada di bawah stok minimum.',
            'icon' => 'mdi-gauge-low',
            'tone' => 'amber',
        ],
        'stok-kosong' => [
            'title' => 'Stok Kosong',
            'short_title' => 'Stok Kosong',
            'description' => 'Temukan obat aktif tanpa saldo tersedia agar kehilangan penjualan dapat segera dicegah.',
            'icon' => 'mdi-package-variant-remove',
            'tone' => 'red',
        ],
        'stok-berlebih' => [
            'title' => 'Stok Berlebih',
            'short_title' => 'Stok Berlebih',
            'description' => 'Identifikasi saldo di atas tiga kali stok minimum dan modal yang tertahan di dalamnya.',
            'icon' => 'mdi-package-up',
            'tone' => 'violet',
        ],
        'hampir-expired' => [
            'title' => 'Hampir Expired',
            'short_title' => 'Hampir ED',
            'description' => 'Pantau batch aktif yang tanggal kedaluwarsanya masuk ke rentang tanggal terpilih.',
            'icon' => 'mdi-calendar-clock-outline',
            'tone' => 'orange',
        ],
        'expired' => [
            'title' => 'Expired',
            'short_title' => 'Expired',
            'description' => 'Audit stok yang sudah kedaluwarsa, kuantitas tersisa, dan nilai modal yang berisiko.',
            'icon' => 'mdi-calendar-remove-outline',
            'tone' => 'red',
        ],
        'stock-opname' => [
            'title' => 'Stock Opname',
            'short_title' => 'Stock Opname',
            'description' => 'Tinjau dokumen, cakupan item, hasil hitung fisik, status, dan progres stock opname.',
            'icon' => 'mdi-clipboard-check-multiple-outline',
            'tone' => 'teal',
        ],
        'selisih-opname' => [
            'title' => 'Selisih Stock Opname',
            'short_title' => 'Selisih Opname',
            'description' => 'Bandingkan stok sistem dengan stok fisik sampai tingkat obat dan batch.',
            'icon' => 'mdi-compare-horizontal',
            'tone' => 'rose',
        ],
        'adjustment' => [
            'title' => 'Adjustment Stok',
            'short_title' => 'Adjustment',
            'description' => 'Audit seluruh koreksi stok manual maupun penyesuaian hasil stock opname.',
            'icon' => 'mdi-tune-variant',
            'tone' => 'purple',
        ],
        'stok-rusak' => [
            'title' => 'Stok Rusak',
            'short_title' => 'Stok Rusak',
            'description' => 'Telusuri adjustment yang ditandai sebagai barang rusak beserta nilai kerugiannya.',
            'icon' => 'mdi-package-variant-remove',
            'tone' => 'orange',
        ],
        'stok-karantina' => [
            'title' => 'Stok Karantina',
            'short_title' => 'Karantina',
            'description' => 'Pantau adjustment barang karantina yang sementara tidak dapat dijual.',
            'icon' => 'mdi-shield-lock-outline',
            'tone' => 'amber',
        ],
        'nilai-persediaan' => [
            'title' => 'Nilai Persediaan',
            'short_title' => 'Nilai Persediaan',
            'description' => 'Ukur nilai modal dari harga beli × stok dan potensi nilai jual seluruh persediaan.',
            'icon' => 'mdi-cash-multiple',
            'tone' => 'green',
        ],
    ];

    public function definition(string $type): array
    {
        return self::TYPES[$type] ?? throw ValidationException::withMessages([
            'report' => 'Jenis laporan persediaan tidak tersedia.',
        ]);
    }

    public function defaultPeriod(string $type): array
    {
        $this->definition($type);

        return match ($type) {
            'hampir-expired' => ['start' => today(), 'end' => today()->addDays(90)],
            'expired' => ['start' => today()->subYears(10), 'end' => today()],
            'stock-opname', 'selisih-opname' => ['start' => today()->subYear(), 'end' => today()],
            'posisi-stok', 'stok-batch', 'stok-minimum', 'stok-kosong', 'stok-berlebih', 'nilai-persediaan' => ['start' => today(), 'end' => today()],
            default => ['start' => today()->subDays(29), 'end' => today()],
        };
    }

    public function build(User $user, string $type, array $filters): array
    {
        $definition = $this->definition($type);
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $report = $this->reportQuery($type, $context['branch_ids'], $filters);
        $paginator = $this->paginate($report, $filters);
        $metrics = $this->metrics($type, $context['branch_ids'], $filters);
        $chart = $this->chart($type, $context['branch_ids'], $filters);

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
                'period_days' => $filters['start']->diffInDays($filters['end']) + 1,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'metrics' => $metrics['cards'],
            'insight' => $this->insight($type, $metrics['values'], $chart),
            'chart' => $chart,
            'table' => [
                'title' => $report['title'],
                'columns' => $report['columns'],
                'rows' => $this->normalizeRows(collect($paginator->items()), $report['columns'])->values()->all(),
                'searchable' => $report['searchable'] !== [],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'default_sort' => $report['default_sort'],
                'default_direction' => $report['default_direction'],
            ],
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

        return [
            'branches' => $branches,
            'branch_ids' => $branchIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function reportQuery(string $type, array $branchIds, array $filters): array
    {
        return match ($type) {
            'posisi-stok' => $this->stockPositionReport($branchIds),
            'stok-batch' => $this->batchReport($branchIds),
            'kartu-stok' => $this->movementReport($branchIds, $filters),
            'stok-minimum' => $this->minimumStockReport($branchIds),
            'stok-kosong' => $this->emptyStockReport($branchIds),
            'stok-berlebih' => $this->excessStockReport($branchIds),
            'hampir-expired' => $this->expiryReport($branchIds, $filters, false),
            'expired' => $this->expiryReport($branchIds, $filters, true),
            'stock-opname' => $this->stockOpnameReport($branchIds, $filters),
            'selisih-opname' => $this->stockOpnameDifferenceReport($branchIds, $filters),
            'adjustment' => $this->adjustmentReport($branchIds, $filters),
            'stok-rusak' => $this->conditionReport($branchIds, $filters, 'rusak'),
            'stok-karantina' => $this->conditionReport($branchIds, $filters, 'karantina'),
            'nilai-persediaan' => $this->inventoryValueReport($branchIds),
        };
    }

    private function stockPositionReport(array $branchIds): array
    {
        return $this->report(
            'Posisi stok seluruh obat',
            $this->stockSnapshot($branchIds),
            [
                $this->column('product_code', 'Kode'),
                $this->column('product_name', 'Obat'),
                $this->column('category', 'Kategori'),
                $this->column('rack', 'Rak'),
                $this->column('batch_count', 'Batch', 'number'),
                $this->column('stock_qty', 'Stok', 'number'),
                $this->column('minimum_stock', 'Minimum', 'number'),
                $this->column('stock_status', 'Status'),
                $this->column('purchase_value', 'Nilai Modal', 'currency'),
                $this->column('selling_value', 'Potensi Jual', 'currency'),
            ],
            ['medicines.kode_obat', 'medicines.nama_obat', 'categories.name', 'racks.nama'],
            $this->stockSorts(),
            'product_name',
            'asc',
        );
    }

    private function batchReport(array $branchIds): array
    {
        $query = $this->batchBase($branchIds)
            ->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->select([
                'branches.name as branch_name',
                'medicines.kode_obat as product_code',
                'medicines.nama_obat as product_name',
                'units.nama as unit',
                'batches.no_batch as batch_number',
                'batches.expired_date',
                'batches.qty',
                'batches.harga_beli as purchase_price',
                'batches.harga_jual as selling_price',
                'batches.last_movement_at',
            ])
            ->selectRaw('batches.qty * batches.harga_beli as purchase_value')
            ->selectRaw('batches.qty * batches.harga_jual as selling_value')
            ->selectRaw($this->expiryStatusExpression('batches.expired_date').' as expiry_status');

        return $this->report(
            'Kuantitas dan nilai per batch',
            $query,
            [
                $this->column('branch_name', 'Cabang'),
                $this->column('product_code', 'Kode'),
                $this->column('product_name', 'Obat'),
                $this->column('batch_number', 'No. Batch'),
                $this->column('expired_date', 'ED', 'date', 'Tanpa ED'),
                $this->column('expiry_status', 'Status ED'),
                $this->column('qty', 'Qty', 'number'),
                $this->column('unit', 'Satuan'),
                $this->column('purchase_price', 'Harga Beli', 'currency'),
                $this->column('selling_price', 'Harga Jual', 'currency'),
                $this->column('purchase_value', 'Nilai Modal', 'currency'),
                $this->column('selling_value', 'Potensi Jual', 'currency'),
                $this->column('last_movement_at', 'Mutasi Terakhir', 'datetime', 'Belum ada mutasi'),
            ],
            ['branches.name', 'medicines.kode_obat', 'medicines.nama_obat', 'batches.no_batch'],
            [
                'branch_name' => 'branches.name', 'product_code' => 'medicines.kode_obat', 'product_name' => 'medicines.nama_obat',
                'batch_number' => 'batches.no_batch', 'expired_date' => 'batches.expired_date', 'expiry_status' => 'expiry_status',
                'qty' => 'batches.qty', 'unit' => 'units.nama', 'purchase_price' => 'batches.harga_beli',
                'selling_price' => 'batches.harga_jual', 'purchase_value' => 'purchase_value', 'selling_value' => 'selling_value',
                'last_movement_at' => 'batches.last_movement_at',
            ],
            'product_name',
            'asc',
        );
    }

    private function movementReport(array $branchIds, array $filters, ?Builder $query = null, string $title = 'Mutasi masuk dan keluar'): array
    {
        $query ??= $this->movementBase($branchIds, $filters);
        $query->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->leftJoin('users', 'users.id', '=', 'cards.created_by')
            ->select([
                'cards.tanggal_mutasi as movement_at',
                'branches.name as branch_name',
                'medicines.kode_obat as product_code',
                'medicines.nama_obat as product_name',
                'units.nama as unit',
                'cards.no_batch as batch_number',
                'cards.jenis_mutasi as movement_type',
                'cards.qty_masuk as qty_in',
                'cards.qty_keluar as qty_out',
                'cards.saldo_batch as batch_balance',
                'cards.saldo_total as total_balance',
                'cards.harga_beli as purchase_price',
                'cards.nomor_referensi as reference_number',
                'cards.keterangan as notes',
                'users.name as officer',
            ])
            ->selectRaw('(cards.qty_masuk - cards.qty_keluar) * cards.harga_beli as movement_value');

        return $this->report(
            $title,
            $query,
            [
                $this->column('movement_at', 'Tanggal', 'datetime'),
                $this->column('branch_name', 'Cabang'),
                $this->column('product_code', 'Kode'),
                $this->column('product_name', 'Obat'),
                $this->column('batch_number', 'No. Batch', 'text', 'Tanpa batch'),
                $this->column('movement_type', 'Jenis Mutasi'),
                $this->column('qty_in', 'Masuk', 'number'),
                $this->column('qty_out', 'Keluar', 'number'),
                $this->column('unit', 'Satuan'),
                $this->column('batch_balance', 'Saldo Batch', 'number'),
                $this->column('total_balance', 'Saldo Total', 'number'),
                $this->column('movement_value', 'Nilai Mutasi', 'signed_currency'),
                $this->column('reference_number', 'Referensi', 'text', 'Tanpa referensi'),
                $this->column('notes', 'Keterangan', 'text', 'Tanpa keterangan'),
                $this->column('officer', 'Petugas', 'text', 'Sistem'),
            ],
            ['branches.name', 'medicines.kode_obat', 'medicines.nama_obat', 'cards.no_batch', 'cards.nomor_referensi', 'cards.keterangan', 'users.name'],
            [
                'movement_at' => 'cards.tanggal_mutasi', 'branch_name' => 'branches.name', 'product_code' => 'medicines.kode_obat',
                'product_name' => 'medicines.nama_obat', 'batch_number' => 'cards.no_batch', 'movement_type' => 'cards.jenis_mutasi',
                'qty_in' => 'cards.qty_masuk', 'qty_out' => 'cards.qty_keluar', 'unit' => 'units.nama',
                'batch_balance' => 'cards.saldo_batch', 'total_balance' => 'cards.saldo_total', 'movement_value' => 'movement_value',
                'reference_number' => 'cards.nomor_referensi', 'notes' => 'cards.keterangan', 'officer' => 'users.name',
            ],
            'movement_at',
            'desc',
        );
    }

    private function minimumStockReport(array $branchIds): array
    {
        $query = $this->stockSnapshot($branchIds)
            ->selectRaw('CASE WHEN medicines.stok_minimum > COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) THEN medicines.stok_minimum - COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) ELSE 0 END as shortage_qty')
            ->havingRaw('medicines.stok_minimum > 0 AND COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) <= medicines.stok_minimum');

        return $this->stockAlertReport('Obat pada atau di bawah stok minimum', $query, true, false);
    }

    private function emptyStockReport(array $branchIds): array
    {
        $query = $this->stockSnapshot($branchIds)
            ->selectRaw('medicines.stok_minimum as shortage_qty')
            ->havingRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) = 0');

        return $this->stockAlertReport('Obat aktif dengan stok kosong', $query, true, false);
    }

    private function excessStockReport(array $branchIds): array
    {
        $stock = 'COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0)';
        $query = $this->stockSnapshot($branchIds)
            ->selectRaw($stock.' - (medicines.stok_minimum * '.self::EXCESS_MULTIPLIER.') as excess_qty')
            ->havingRaw('medicines.stok_minimum > 0 AND '.$stock.' > medicines.stok_minimum * '.self::EXCESS_MULTIPLIER);

        return $this->stockAlertReport('Stok di atas tiga kali minimum', $query, false, true);
    }

    private function stockAlertReport(string $title, Builder $query, bool $shortage, bool $excess): array
    {
        $columns = [
            $this->column('product_code', 'Kode'),
            $this->column('product_name', 'Obat'),
            $this->column('category', 'Kategori'),
            $this->column('rack', 'Rak'),
            $this->column('stock_qty', 'Stok', 'number'),
            $this->column('minimum_stock', 'Minimum', 'number'),
        ];
        if ($shortage) {
            $columns[] = $this->column('shortage_qty', 'Kekurangan', 'number');
        }
        if ($excess) {
            $columns[] = $this->column('excess_qty', 'Kelebihan', 'number');
        }
        $columns = [...$columns,
            $this->column('unit', 'Satuan'),
            $this->column('stock_status', 'Status'),
            $this->column('purchase_value', 'Nilai Modal', 'currency'),
            $this->column('nearest_expiry', 'ED Terdekat', 'date', 'Tanpa ED'),
        ];
        $sorts = $this->stockSorts();
        if ($shortage) {
            $sorts['shortage_qty'] = 'shortage_qty';
        }
        if ($excess) {
            $sorts['excess_qty'] = 'excess_qty';
        }

        return $this->report(
            $title,
            $query,
            $columns,
            ['medicines.kode_obat', 'medicines.nama_obat', 'categories.name', 'racks.nama'],
            $sorts,
            $shortage ? 'shortage_qty' : ($excess ? 'excess_qty' : 'product_name'),
            'desc',
        );
    }

    private function expiryReport(array $branchIds, array $filters, bool $expired): array
    {
        $daysToExpiry = $this->daysExpression('batches.expired_date');
        $query = $this->batchBase($branchIds)
            ->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->where('batches.qty', '>', 0)
            ->whereNotNull('batches.expired_date')
            ->whereBetween('batches.expired_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->select([
                'branches.name as branch_name', 'medicines.kode_obat as product_code', 'medicines.nama_obat as product_name',
                'units.nama as unit', 'batches.no_batch as batch_number', 'batches.expired_date', 'batches.qty',
                'batches.harga_beli as purchase_price', 'batches.harga_jual as selling_price',
            ])
            ->selectRaw('batches.qty * batches.harga_beli as purchase_value')
            ->selectRaw('batches.qty * batches.harga_jual as selling_value')
            ->selectRaw(($expired ? 'ABS('.$daysToExpiry.')' : $daysToExpiry).' as days_to_expiry')
            ->selectRaw($this->expiryStatusExpression('batches.expired_date').' as expiry_status');

        if ($expired) {
            $query->whereDate('batches.expired_date', '<', today()->toDateString());
        } else {
            $query->whereDate('batches.expired_date', '>=', today()->toDateString());
        }

        return $this->report(
            $expired ? 'Batch kedaluwarsa yang masih memiliki saldo' : 'Batch yang mendekati kedaluwarsa',
            $query,
            [
                $this->column('branch_name', 'Cabang'), $this->column('product_code', 'Kode'), $this->column('product_name', 'Obat'),
                $this->column('batch_number', 'No. Batch'), $this->column('expired_date', 'Tanggal ED', 'date'),
                $this->column('days_to_expiry', $expired ? 'Hari Lewat ED' : 'Sisa Hari', 'number'),
                $this->column('expiry_status', 'Status'), $this->column('qty', 'Qty', 'number'), $this->column('unit', 'Satuan'),
                $this->column('purchase_price', 'Harga Beli', 'currency'), $this->column('purchase_value', 'Nilai Modal', 'currency'),
                $this->column('selling_value', 'Potensi Jual', 'currency'),
            ],
            ['branches.name', 'medicines.kode_obat', 'medicines.nama_obat', 'batches.no_batch'],
            [
                'branch_name' => 'branches.name', 'product_code' => 'medicines.kode_obat', 'product_name' => 'medicines.nama_obat',
                'batch_number' => 'batches.no_batch', 'expired_date' => 'batches.expired_date', 'days_to_expiry' => 'days_to_expiry',
                'expiry_status' => 'expiry_status', 'qty' => 'batches.qty', 'unit' => 'units.nama',
                'purchase_price' => 'batches.harga_beli', 'purchase_value' => 'purchase_value', 'selling_value' => 'selling_value',
            ],
            'expired_date',
            'asc',
        );
    }

    private function stockOpnameReport(array $branchIds, array $filters): array
    {
        $system = 'COALESCE(details.stok_sistem_validasi, details.stok_sistem_hitung, details.stok_sistem_awal, 0)';
        $physical = 'COALESCE(details.stok_target_validasi, details.stok_fisik, '.$system.')';
        $difference = 'COALESCE(details.selisih_validasi, details.selisih, 0)';
        $query = DB::table('stock_opnames as opnames')
            ->join('branches', 'branches.id', '=', 'opnames.branch_id')
            ->leftJoin('rak_penyimpanans as racks', 'racks.id', '=', 'opnames.rak_id')
            ->leftJoin('stock_opname_details as details', 'details.stock_opname_id', '=', 'opnames.id')
            ->leftJoin('users as creators', 'creators.id', '=', 'opnames.created_by')
            ->whereIn('opnames.branch_id', $this->safeIds($branchIds))
            ->whereBetween('opnames.tanggal_opname', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->groupBy('opnames.id', 'opnames.nomor', 'opnames.tanggal_opname', 'opnames.status', 'opnames.transaction_mode', 'opnames.catatan', 'branches.name', 'racks.nama', 'creators.name')
            ->select([
                'opnames.nomor as opname_number', 'opnames.tanggal_opname as opname_date', 'branches.name as branch_name',
                'racks.nama as rack', 'opnames.status', 'opnames.transaction_mode', 'opnames.catatan as notes', 'creators.name as creator',
            ])
            ->selectRaw('COUNT(details.id) as item_count')
            ->selectRaw('COALESCE(SUM('.$system.'), 0) as system_qty')
            ->selectRaw('COALESCE(SUM('.$physical.'), 0) as physical_qty')
            ->selectRaw('COALESCE(SUM('.$difference.'), 0) as difference_qty')
            ->selectRaw('COALESCE(SUM(ABS('.$difference.')), 0) as absolute_difference')
            ->selectRaw('COALESCE(SUM(ABS('.$difference.') * COALESCE(details.hpp, 0)), 0) as difference_value');

        return $this->report(
            'Dokumen dan hasil stock opname',
            $query,
            [
                $this->column('opname_number', 'Nomor Opname'), $this->column('opname_date', 'Tanggal', 'date'),
                $this->column('branch_name', 'Cabang'), $this->column('rack', 'Rak', 'text', 'Semua rak'),
                $this->column('status', 'Status', 'status'), $this->column('transaction_mode', 'Mode'),
                $this->column('item_count', 'Item', 'number'), $this->column('system_qty', 'Stok Sistem', 'number'),
                $this->column('physical_qty', 'Stok Fisik', 'number'), $this->column('difference_qty', 'Selisih', 'number'),
                $this->column('absolute_difference', 'Selisih Absolut', 'number'), $this->column('difference_value', 'Nilai Selisih', 'currency'),
                $this->column('creator', 'Dibuat Oleh', 'text', 'Sistem'), $this->column('notes', 'Catatan', 'text', 'Tanpa catatan'),
            ],
            ['opnames.nomor', 'branches.name', 'racks.nama', 'creators.name', 'opnames.catatan'],
            [
                'opname_number' => 'opnames.nomor', 'opname_date' => 'opnames.tanggal_opname', 'branch_name' => 'branches.name',
                'rack' => 'racks.nama', 'status' => 'opnames.status', 'transaction_mode' => 'opnames.transaction_mode',
                'item_count' => 'item_count', 'system_qty' => 'system_qty', 'physical_qty' => 'physical_qty',
                'difference_qty' => 'difference_qty', 'absolute_difference' => 'absolute_difference', 'difference_value' => 'difference_value',
                'creator' => 'creators.name', 'notes' => 'opnames.catatan',
            ],
            'opname_date',
            'desc',
        );
    }

    private function stockOpnameDifferenceReport(array $branchIds, array $filters): array
    {
        $system = 'COALESCE(details.stok_sistem_validasi, details.stok_sistem_hitung, details.stok_sistem_awal, 0)';
        $physical = 'COALESCE(details.stok_target_validasi, details.stok_fisik, '.$system.')';
        $difference = 'COALESCE(details.selisih_validasi, details.selisih, 0)';
        $query = DB::table('stock_opname_details as details')
            ->join('stock_opnames as opnames', 'opnames.id', '=', 'details.stock_opname_id')
            ->join('branches', 'branches.id', '=', 'opnames.branch_id')
            ->leftJoin('users as counters', 'counters.id', '=', 'details.counted_by')
            ->whereIn('opnames.branch_id', $this->safeIds($branchIds))
            ->whereBetween('opnames.tanggal_opname', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->whereRaw($difference.' <> 0')
            ->select([
                'opnames.nomor as opname_number', 'opnames.tanggal_opname as opname_date', 'branches.name as branch_name',
                'details.kode_obat as product_code', 'details.nama_obat as product_name', 'details.no_batch as batch_number',
                'details.expired_date', 'details.satuan as unit', 'details.hpp', 'details.alasan_selisih as reason',
                'counters.name as counter',
            ])
            ->selectRaw($system.' as system_qty')
            ->selectRaw($physical.' as physical_qty')
            ->selectRaw($difference.' as difference_qty')
            ->selectRaw($difference.' * COALESCE(details.hpp, 0) as difference_value');

        return $this->report(
            'Selisih sistem dan fisik per batch',
            $query,
            [
                $this->column('opname_number', 'Nomor Opname'), $this->column('opname_date', 'Tanggal', 'date'),
                $this->column('branch_name', 'Cabang'), $this->column('product_code', 'Kode'), $this->column('product_name', 'Obat'),
                $this->column('batch_number', 'No. Batch', 'text', 'Tanpa batch'), $this->column('expired_date', 'ED', 'date', 'Tanpa ED'),
                $this->column('system_qty', 'Stok Sistem', 'number'), $this->column('physical_qty', 'Stok Fisik', 'number'),
                $this->column('difference_qty', 'Selisih', 'number'), $this->column('unit', 'Satuan'),
                $this->column('hpp', 'HPP', 'currency'), $this->column('difference_value', 'Nilai Selisih', 'signed_currency'),
                $this->column('reason', 'Alasan', 'text', 'Belum diisi'), $this->column('counter', 'Penghitung', 'text', 'Belum tercatat'),
            ],
            ['opnames.nomor', 'branches.name', 'details.kode_obat', 'details.nama_obat', 'details.no_batch', 'details.alasan_selisih', 'counters.name'],
            [
                'opname_number' => 'opnames.nomor', 'opname_date' => 'opnames.tanggal_opname', 'branch_name' => 'branches.name',
                'product_code' => 'details.kode_obat', 'product_name' => 'details.nama_obat', 'batch_number' => 'details.no_batch',
                'expired_date' => 'details.expired_date', 'system_qty' => 'system_qty', 'physical_qty' => 'physical_qty',
                'difference_qty' => 'difference_qty', 'unit' => 'details.satuan', 'hpp' => 'details.hpp',
                'difference_value' => 'difference_value', 'reason' => 'details.alasan_selisih', 'counter' => 'counters.name',
            ],
            'opname_date',
            'desc',
        );
    }

    private function adjustmentReport(array $branchIds, array $filters): array
    {
        $query = $this->movementBase($branchIds, $filters)->whereIn('cards.jenis_mutasi', self::ADJUSTMENT_TYPES);

        return $this->movementReport($branchIds, $filters, $query, 'Koreksi dan adjustment stok');
    }

    private function conditionReport(array $branchIds, array $filters, string $keyword): array
    {
        $query = $this->movementBase($branchIds, $filters)
            ->whereIn('cards.jenis_mutasi', self::ADJUSTMENT_TYPES)
            ->whereRaw('LOWER(COALESCE(cards.keterangan, ?)) LIKE ?', ['', '%'.$keyword.'%']);

        return $this->movementReport(
            $branchIds,
            $filters,
            $query,
            $keyword === 'rusak' ? 'Adjustment barang rusak' : 'Adjustment barang karantina',
        );
    }

    private function inventoryValueReport(array $branchIds): array
    {
        $query = $this->stockSnapshot($branchIds)
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_jual ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0) as potential_margin')
            ->selectRaw('CASE WHEN COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0) > 0 THEN ((COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_jual ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0)) * 100.0) / COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0) ELSE 0 END as markup_percent');

        return $this->report(
            'Nilai modal dan potensi nilai jual per obat',
            $query,
            [
                $this->column('product_code', 'Kode'), $this->column('product_name', 'Obat'),
                $this->column('category', 'Kategori'), $this->column('stock_qty', 'Stok', 'number'),
                $this->column('unit', 'Satuan'), $this->column('batch_count', 'Batch', 'number'),
                $this->column('purchase_value', 'Harga Beli × Stok', 'currency'),
                $this->column('selling_value', 'Potensi Nilai Jual', 'currency'),
                $this->column('potential_margin', 'Potensi Margin', 'signed_currency'),
                $this->column('markup_percent', 'Markup Potensial', 'percent'),
                $this->column('nearest_expiry', 'ED Terdekat', 'date', 'Tanpa ED'),
            ],
            ['medicines.kode_obat', 'medicines.nama_obat', 'categories.name'],
            $this->stockSorts() + ['potential_margin' => 'potential_margin', 'markup_percent' => 'markup_percent'],
            'purchase_value',
            'desc',
        );
    }

    private function stockSnapshot(array $branchIds): Builder
    {
        $ids = $this->safeIds($branchIds);

        return DB::table('master_obats as medicines')
            ->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->leftJoin('rak_penyimpanans as racks', 'racks.id', '=', 'medicines.rak_id')
            ->leftJoin('stok_batches as batches', function ($join) use ($ids) {
                $join->on('batches.obat_id', '=', 'medicines.id')->whereIn('batches.branch_id', $ids);
            })
            ->where('medicines.is_active', true)
            ->when($branchIds === [], fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->groupBy(
                'medicines.id', 'medicines.kode_obat', 'medicines.nama_obat', 'medicines.stok_minimum',
                'units.nama', 'categories.name', 'racks.nama',
            )
            ->select([
                'medicines.kode_obat as product_code', 'medicines.nama_obat as product_name',
                'units.nama as unit', 'categories.name as category', 'racks.nama as rack',
                'medicines.stok_minimum as minimum_stock',
            ])
            ->selectRaw('COUNT(CASE WHEN batches.qty > 0 THEN batches.id END) as batch_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) as stock_qty')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_beli ELSE 0 END), 0) as purchase_value')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty * batches.harga_jual ELSE 0 END), 0) as selling_value')
            ->selectRaw('MIN(CASE WHEN batches.qty > 0 THEN batches.expired_date END) as nearest_expiry')
            ->selectRaw("CASE WHEN COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) = 0 THEN 'Kosong' WHEN medicines.stok_minimum > 0 AND COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) <= medicines.stok_minimum THEN 'Minimum' WHEN medicines.stok_minimum > 0 AND COALESCE(SUM(CASE WHEN batches.qty > 0 THEN batches.qty ELSE 0 END), 0) > medicines.stok_minimum * ".self::EXCESS_MULTIPLIER." THEN 'Berlebih' ELSE 'Aman' END as stock_status");
    }

    private function batchBase(array $branchIds): Builder
    {
        return DB::table('stok_batches as batches')
            ->join('master_obats as medicines', 'medicines.id', '=', 'batches.obat_id')
            ->leftJoin('branches', 'branches.id', '=', 'batches.branch_id')
            ->whereIn('batches.branch_id', $this->safeIds($branchIds));
    }

    private function movementBase(array $branchIds, array $filters): Builder
    {
        return DB::table('kartu_stok as cards')
            ->join('master_obats as medicines', 'medicines.id', '=', 'cards.obat_id')
            ->leftJoin('branches', 'branches.id', '=', 'cards.branch_id')
            ->whereIn('cards.branch_id', $this->safeIds($branchIds))
            ->whereBetween('cards.tanggal_mutasi', [
                $filters['start']->copy()->startOfDay()->toDateTimeString(),
                $filters['end']->copy()->endOfDay()->toDateTimeString(),
            ]);
    }

    private function metrics(string $type, array $branchIds, array $filters): array
    {
        $snapshot = $this->stockSnapshot($branchIds)->get();
        $batches = $this->batchBase($branchIds)
            ->where('batches.qty', '>', 0)
            ->selectRaw('COUNT(batches.id) as batch_count')
            ->selectRaw('COUNT(DISTINCT batches.obat_id) as stocked_products')
            ->selectRaw('COALESCE(SUM(batches.qty), 0) as stock_qty')
            ->selectRaw('COALESCE(SUM(batches.qty * batches.harga_beli), 0) as purchase_value')
            ->selectRaw('COALESCE(SUM(batches.qty * batches.harga_jual), 0) as selling_value')
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.expired_date < ? THEN batches.qty ELSE 0 END), 0) as expired_qty', [today()->toDateString()])
            ->selectRaw('COALESCE(SUM(CASE WHEN batches.expired_date < ? THEN batches.qty * batches.harga_beli ELSE 0 END), 0) as expired_value', [today()->toDateString()])
            ->first();

        $values = [
            'product_count' => $snapshot->count(),
            'stocked_products' => (int) ($batches->stocked_products ?? 0),
            'batch_count' => (int) ($batches->batch_count ?? 0),
            'stock_qty' => (float) ($batches->stock_qty ?? 0),
            'purchase_value' => (float) ($batches->purchase_value ?? 0),
            'selling_value' => (float) ($batches->selling_value ?? 0),
            'potential_margin' => (float) ($batches->selling_value ?? 0) - (float) ($batches->purchase_value ?? 0),
            'expired_qty' => (float) ($batches->expired_qty ?? 0),
            'expired_value' => (float) ($batches->expired_value ?? 0),
            'empty_count' => $snapshot->where('stock_qty', '<=', 0)->count(),
            'low_count' => $snapshot->filter(fn ($row) => (float) $row->minimum_stock > 0 && (float) $row->stock_qty <= (float) $row->minimum_stock)->count(),
            'excess_count' => $snapshot->filter(fn ($row) => (float) $row->minimum_stock > 0 && (float) $row->stock_qty > (float) $row->minimum_stock * self::EXCESS_MULTIPLIER)->count(),
        ];

        if (in_array($type, ['kartu-stok', 'adjustment', 'stok-rusak', 'stok-karantina'], true)) {
            $movements = $this->movementMetricQuery($type, $branchIds, $filters)->first();
            $values += [
                'movement_count' => (int) ($movements->movement_count ?? 0),
                'qty_in' => (float) ($movements->qty_in ?? 0),
                'qty_out' => (float) ($movements->qty_out ?? 0),
                'movement_value' => (float) ($movements->movement_value ?? 0),
                'movement_absolute_value' => (float) ($movements->movement_absolute_value ?? 0),
                'affected_products' => (int) ($movements->affected_products ?? 0),
            ];
        }

        if (in_array($type, ['hampir-expired', 'expired'], true)) {
            $expiry = $this->reportQuery($type, $branchIds, $filters)['query']->get();
            $values += [
                'report_batch_count' => $expiry->count(),
                'report_qty' => (float) $expiry->sum('qty'),
                'report_value' => (float) $expiry->sum('purchase_value'),
                'nearest_days' => $expiry->isEmpty() ? 0 : (float) $expiry->min('days_to_expiry'),
            ];
        }

        if (in_array($type, ['stock-opname', 'selisih-opname'], true)) {
            $opnameRows = $this->reportQuery($type, $branchIds, $filters)['query']->get();
            $values += [
                'opname_count' => $type === 'stock-opname' ? $opnameRows->count() : $opnameRows->pluck('opname_number')->unique()->count(),
                'opname_item_count' => $type === 'stock-opname' ? (float) $opnameRows->sum('item_count') : $opnameRows->count(),
                'system_qty' => (float) $opnameRows->sum('system_qty'),
                'physical_qty' => (float) $opnameRows->sum('physical_qty'),
                'difference_qty' => (float) $opnameRows->sum('difference_qty'),
                'absolute_difference' => $type === 'stock-opname'
                    ? (float) $opnameRows->sum('absolute_difference')
                    : (float) $opnameRows->sum(fn ($row) => abs((float) $row->difference_qty)),
                'difference_value' => (float) $opnameRows->sum('difference_value'),
            ];
        }

        $cards = match ($type) {
            'stok-batch' => [
                $this->card('Batch aktif', $values['batch_count'], 'number', 'mdi-package-variant-closed', 'Batch dengan saldo positif', 'blue'),
                $this->card('Total unit', $values['stock_qty'], 'number', 'mdi-counter', 'Seluruh satuan stok dasar', 'teal'),
                $this->card('Nilai modal', $values['purchase_value'], 'currency', 'mdi-cash-multiple', 'Harga beli × stok', 'navy'),
                $this->card('Batch expired', $this->expiredBatchCount($branchIds), 'number', 'mdi-calendar-remove-outline', 'Batch bersaldo yang melewati ED', 'red'),
            ],
            'kartu-stok', 'adjustment', 'stok-rusak', 'stok-karantina' => [
                $this->card('Jumlah mutasi', $values['movement_count'], 'number', 'mdi-swap-horizontal', 'Baris mutasi sesuai filter', 'blue'),
                $this->card('Qty masuk', $values['qty_in'], 'number', 'mdi-package-down', 'Akumulasi penambahan stok', 'teal'),
                $this->card('Qty keluar', $values['qty_out'], 'number', 'mdi-package-up', 'Akumulasi pengurangan stok', $type === 'stok-rusak' ? 'red' : 'orange'),
                $this->card($type === 'kartu-stok' ? 'Nilai mutasi' : 'Dampak nilai', $values['movement_absolute_value'], 'currency', 'mdi-cash-sync', 'Nilai absolut berdasarkan harga beli', 'violet'),
            ],
            'stok-minimum' => [
                $this->card('Perlu perhatian', $values['low_count'], 'number', 'mdi-gauge-low', 'Produk pada/di bawah minimum', 'amber'),
                $this->card('Stok kosong', $values['empty_count'], 'number', 'mdi-package-variant-remove', 'Bagian dari stok minimum', 'red'),
                $this->card('Produk tersedia', $values['stocked_products'], 'number', 'mdi-pill-multiple', 'Produk dengan saldo positif', 'teal'),
                $this->card('Modal persediaan', $values['purchase_value'], 'currency', 'mdi-cash-multiple', 'Nilai stok seluruh produk', 'navy'),
            ],
            'stok-kosong' => [
                $this->card('Produk kosong', $values['empty_count'], 'number', 'mdi-package-variant-remove', 'Obat aktif tanpa saldo', 'red'),
                $this->card('Produk tersedia', $values['stocked_products'], 'number', 'mdi-package-check', 'Obat dengan saldo positif', 'teal'),
                $this->card('Total produk aktif', $values['product_count'], 'number', 'mdi-pill-multiple', 'Master obat aktif', 'blue'),
                $this->card('Tingkat kosong', $values['product_count'] > 0 ? $values['empty_count'] * 100 / $values['product_count'] : 0, 'percent', 'mdi-chart-donut', 'Proporsi produk aktif', 'orange'),
            ],
            'stok-berlebih' => [
                $this->card('Produk berlebih', $values['excess_count'], 'number', 'mdi-package-up', 'Di atas 3 × stok minimum', 'violet'),
                $this->card('Total unit', $values['stock_qty'], 'number', 'mdi-counter', 'Seluruh saldo persediaan', 'blue'),
                $this->card('Modal persediaan', $values['purchase_value'], 'currency', 'mdi-cash-multiple', 'Harga beli × stok', 'navy'),
                $this->card('Potensi nilai jual', $values['selling_value'], 'currency', 'mdi-storefront-outline', 'Harga jual × stok', 'green'),
            ],
            'hampir-expired', 'expired' => [
                $this->card('Batch terdampak', $values['report_batch_count'], 'number', 'mdi-package-variant-closed-alert', 'Batch bersaldo sesuai rentang ED', $type === 'expired' ? 'red' : 'orange'),
                $this->card('Qty terdampak', $values['report_qty'], 'number', 'mdi-counter', 'Satuan stok dasar', 'amber'),
                $this->card('Nilai modal berisiko', $values['report_value'], 'currency', 'mdi-cash-remove', 'Qty × harga beli', 'red'),
                $this->card($type === 'expired' ? 'Hari terlama lewat' : 'ED terdekat', abs($values['nearest_days']), 'number', 'mdi-calendar-clock', 'Dihitung dari hari ini', 'blue'),
            ],
            'stock-opname', 'selisih-opname' => [
                $this->card('Dokumen opname', $values['opname_count'], 'number', 'mdi-clipboard-check-multiple-outline', 'Sesuai cabang dan periode', 'blue'),
                $this->card('Item dihitung', $values['opname_item_count'], 'number', 'mdi-counter', 'Cakupan item/batch', 'teal'),
                $this->card('Selisih absolut', $values['absolute_difference'], 'number', 'mdi-compare-horizontal', 'Total selisih tanpa tanda', 'orange'),
                $this->card('Nilai selisih', $values['difference_value'], 'signed_currency', 'mdi-cash-sync', 'Selisih × HPP', $values['difference_value'] < 0 ? 'red' : 'violet'),
            ],
            'nilai-persediaan' => [
                $this->card('Total nilai stok', $values['purchase_value'], 'currency', 'mdi-cash-multiple', 'Harga beli × stok saat ini', 'navy'),
                $this->card('Potensi nilai jual', $values['selling_value'], 'currency', 'mdi-storefront-outline', 'Harga jual × stok saat ini', 'green'),
                $this->card('Potensi margin', $values['potential_margin'], 'signed_currency', 'mdi-finance', 'Potensi jual dikurangi modal', $values['potential_margin'] < 0 ? 'red' : 'teal'),
                $this->card('Produk bernilai', $values['stocked_products'], 'number', 'mdi-pill-multiple', $values['batch_count'].' batch aktif', 'blue'),
            ],
            default => [
                $this->card('Produk aktif', $values['product_count'], 'number', 'mdi-pill-multiple', $values['stocked_products'].' produk memiliki stok', 'blue'),
                $this->card('Total stok', $values['stock_qty'], 'number', 'mdi-counter', $values['batch_count'].' batch aktif', 'teal'),
                $this->card('Nilai modal', $values['purchase_value'], 'currency', 'mdi-cash-multiple', 'Harga beli × stok', 'navy'),
                $this->card('Potensi nilai jual', $values['selling_value'], 'currency', 'mdi-storefront-outline', 'Harga jual × stok', 'green'),
            ],
        };

        return compact('cards', 'values');
    }

    private function movementMetricQuery(string $type, array $branchIds, array $filters): Builder
    {
        $query = $this->movementBase($branchIds, $filters);
        if ($type !== 'kartu-stok') {
            $query->whereIn('cards.jenis_mutasi', self::ADJUSTMENT_TYPES);
        }
        if ($type === 'stok-rusak') {
            $query->whereRaw('LOWER(COALESCE(cards.keterangan, ?)) LIKE ?', ['', '%rusak%']);
        }
        if ($type === 'stok-karantina') {
            $query->whereRaw('LOWER(COALESCE(cards.keterangan, ?)) LIKE ?', ['', '%karantina%']);
        }

        return $query
            ->selectRaw('COUNT(cards.id) as movement_count')
            ->selectRaw('COUNT(DISTINCT cards.obat_id) as affected_products')
            ->selectRaw('COALESCE(SUM(cards.qty_masuk), 0) as qty_in')
            ->selectRaw('COALESCE(SUM(cards.qty_keluar), 0) as qty_out')
            ->selectRaw('COALESCE(SUM((cards.qty_masuk - cards.qty_keluar) * cards.harga_beli), 0) as movement_value')
            ->selectRaw('COALESCE(SUM((cards.qty_masuk + cards.qty_keluar) * cards.harga_beli), 0) as movement_absolute_value');
    }

    private function chart(string $type, array $branchIds, array $filters): array
    {
        if (in_array($type, ['kartu-stok', 'adjustment', 'stok-rusak', 'stok-karantina'], true)) {
            $query = $this->movementBase($branchIds, $filters);
            if ($type !== 'kartu-stok') {
                $query->whereIn('cards.jenis_mutasi', self::ADJUSTMENT_TYPES);
            }
            if ($type === 'stok-rusak') {
                $query->whereRaw('LOWER(COALESCE(cards.keterangan, ?)) LIKE ?', ['', '%rusak%']);
            }
            if ($type === 'stok-karantina') {
                $query->whereRaw('LOWER(COALESCE(cards.keterangan, ?)) LIKE ?', ['', '%karantina%']);
            }
            $date = $this->dateExpression('cards.tanggal_mutasi');
            $rows = $query->selectRaw($date.' as bucket')
                ->selectRaw('COALESCE(SUM((cards.qty_masuk + cards.qty_keluar) * cards.harga_beli), 0) as amount_value')
                ->selectRaw('COUNT(cards.id) as count_value')
                ->groupByRaw($date)->orderBy('bucket')->get();

            return $this->chartPayload('Nilai mutasi persediaan', 'Nilai berdasarkan harga beli dan jumlah mutasi per hari.', $rows, 'Nilai mutasi', 'Mutasi');
        }

        if (in_array($type, ['hampir-expired', 'expired'], true)) {
            $query = $this->reportQuery($type, $branchIds, $filters)['query'];
            $rows = DB::query()->fromSub($query, 'expiry_rows')
                ->selectRaw('expired_date as bucket, COALESCE(SUM(purchase_value), 0) as amount_value, COUNT(*) as count_value')
                ->groupBy('expired_date')->orderBy('expired_date')->get();

            return $this->chartPayload('Eksposur kedaluwarsa', 'Nilai modal dan jumlah batch berdasarkan tanggal ED.', $rows, 'Nilai modal', 'Batch');
        }

        if (in_array($type, ['stock-opname', 'selisih-opname'], true)) {
            $query = $this->reportQuery($type, $branchIds, $filters)['query'];
            $rows = DB::query()->fromSub($query, 'opname_rows')
                ->selectRaw('opname_date as bucket, COALESCE(SUM(ABS(difference_value)), 0) as amount_value, COUNT(*) as count_value')
                ->groupBy('opname_date')->orderBy('opname_date')->get();

            return $this->chartPayload('Tren selisih stock opname', 'Nilai absolut selisih dan jumlah dokumen/item per tanggal opname.', $rows, 'Nilai selisih', $type === 'stock-opname' ? 'Dokumen' : 'Item');
        }

        $query = $type === 'stok-batch'
            ? $this->stockSnapshot($branchIds)
            : $this->reportQuery($type, $branchIds, $filters)['query'];
        $rows = DB::query()->fromSub($query, 'stock_rows')
            ->selectRaw('product_name as bucket, purchase_value as amount_value, stock_qty as count_value')
            ->orderByDesc('purchase_value')->limit(12)->get()->sortByDesc('amount_value')->values();

        return $this->chartPayload('Komposisi nilai persediaan', 'Maksimal 12 produk dengan nilai modal terbesar pada laporan aktif.', $rows, 'Nilai modal', 'Qty stok');
    }

    private function chartPayload(string $title, string $subtitle, Collection $rows, string $amountLabel, string $countLabel): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'labels' => $rows->pluck('bucket')->map(fn ($value) => (string) $value)->all(),
            'series' => [
                ['name' => $amountLabel, 'format' => 'currency', 'data' => $rows->pluck('amount_value')->map(fn ($value) => (float) $value)->all()],
                ['name' => $countLabel, 'format' => 'number', 'data' => $rows->pluck('count_value')->map(fn ($value) => (float) $value)->all()],
            ],
        ];
    }

    private function insight(string $type, array $values, array $chart): array
    {
        $amounts = collect($chart['series'][0]['data'] ?? []);
        $labels = collect($chart['labels'] ?? []);
        $peakValue = (float) ($amounts->max() ?? 0);
        $peakIndex = $amounts->search($peakValue);
        $peakLabel = $peakIndex === false ? '-' : (string) $labels->get($peakIndex, '-');

        [$title, $copy] = match ($type) {
            'nilai-persediaan' => [
                'Modal stok saat ini Rp'.number_format((float) ($values['purchase_value'] ?? 0), 0, ',', '.'),
                'Potensi nilai jual Rp'.number_format((float) ($values['selling_value'] ?? 0), 0, ',', '.').' dengan potensi margin Rp'.number_format((float) ($values['potential_margin'] ?? 0), 0, ',', '.').'.',
            ],
            'stok-kosong' => [
                ($values['empty_count'] ?? 0) > 0 ? $values['empty_count'].' produk sedang kosong' : 'Tidak ada stok kosong',
                'Prioritaskan produk kosong yang memiliki stok minimum terbesar dan permintaan paling konsisten.',
            ],
            'stok-minimum' => [
                ($values['low_count'] ?? 0) > 0 ? $values['low_count'].' produk perlu diisi ulang' : 'Seluruh stok berada di atas minimum',
                'Gunakan kekurangan stok sebagai dasar awal penyusunan pemesanan ke supplier.',
            ],
            'expired' => [
                ($values['report_batch_count'] ?? 0) > 0 ? 'Stok expired perlu segera dipisahkan' : 'Tidak ada stok expired pada rentang ini',
                'Pastikan batch kedaluwarsa tidak tersedia untuk penjualan dan tindak lanjuti sesuai prosedur pemusnahan atau retur.',
            ],
            'hampir-expired' => [
                ($values['report_batch_count'] ?? 0) > 0 ? 'Prioritaskan batch dengan ED terdekat' : 'Tidak ada batch mendekati ED',
                'Terapkan FEFO, redistribusi cabang, atau program penjualan yang sesuai sebelum nilai stok berisiko.',
            ],
            'stok-berlebih' => [
                ($values['excess_count'] ?? 0) > 0 ? $values['excess_count'].' produk menyimpan stok berlebih' : 'Tidak ada stok berlebih',
                'Ambang laporan adalah tiga kali stok minimum; evaluasi redistribusi dan pembelian berikutnya.',
            ],
            'stok-rusak', 'stok-karantina' => [
                ($values['movement_count'] ?? 0) > 0 ? $values['movement_count'].' mutasi kondisi khusus ditemukan' : 'Belum ada mutasi kondisi khusus',
                'Data berasal dari adjustment yang keterangannya menandai kondisi barang agar jejak audit tetap dapat ditelusuri.',
            ],
            'stock-opname', 'selisih-opname' => [
                ($values['absolute_difference'] ?? 0) > 0 ? 'Selisih fisik memerlukan rekonsiliasi' : 'Tidak ada selisih pada cakupan laporan',
                'Telusuri item dengan nilai selisih terbesar dan pastikan alasan serta persetujuan koreksi sudah lengkap.',
            ],
            default => [
                $peakValue > 0 ? 'Kontributor terbesar: '.$peakLabel : 'Belum ada nilai persediaan',
                'Gunakan tabel detail untuk menelusuri saldo, batch, harga, dan risiko setiap produk.',
            ],
        };

        $purchaseValue = (float) ($values['purchase_value'] ?? 0);
        $expiredValue = (float) ($values['expired_value'] ?? 0);

        return [
            'title' => $title,
            'copy' => $copy,
            'peak_value' => $peakValue,
            'peak_label' => $peakLabel,
            'net_after_returns' => max(0, $purchaseValue - $expiredValue),
            'return_ratio' => $purchaseValue > 0 ? min(100, ($expiredValue / $purchaseValue) * 100) : 0,
            'average_ticket' => 0,
        ];
    }

    private function expiredBatchCount(array $branchIds): int
    {
        return $this->batchBase($branchIds)
            ->where('batches.qty', '>', 0)
            ->whereNotNull('batches.expired_date')
            ->whereDate('batches.expired_date', '<', today()->toDateString())
            ->count('batches.id');
    }

    private function stockSorts(): array
    {
        return [
            'product_code' => 'medicines.kode_obat', 'product_name' => 'medicines.nama_obat', 'category' => 'categories.name',
            'rack' => 'racks.nama', 'batch_count' => 'batch_count', 'stock_qty' => 'stock_qty', 'minimum_stock' => 'medicines.stok_minimum',
            'stock_status' => 'stock_status', 'purchase_value' => 'purchase_value', 'selling_value' => 'selling_value',
            'unit' => 'units.nama', 'nearest_expiry' => 'nearest_expiry',
        ];
    }

    private function report(string $title, Builder $query, array $columns, array $searchable, array $sorts, string $defaultSort, string $defaultDirection): array
    {
        return compact('title', 'query', 'columns', 'searchable', 'sorts') + [
            'default_sort' => $defaultSort,
            'default_direction' => $defaultDirection,
        ];
    }

    private function column(string $key, string $label, string $type = 'text', ?string $emptyLabel = null): array
    {
        return array_filter([
            'key' => $key, 'label' => $label, 'type' => $type, 'sortable' => true, 'empty_label' => $emptyLabel,
        ], fn ($value) => $value !== null);
    }

    private function card(string $label, float|int $value, string $format, string $icon, string $note, string $tone): array
    {
        return compact('label', 'value', 'format', 'icon', 'note', 'tone');
    }

    private function paginate(array $report, array $filters): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = $report['query'];
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '' && $report['searchable'] !== []) {
            $query->where(function (Builder $query) use ($report, $search) {
                foreach ($report['searchable'] as $index => $field) {
                    $query->{$index === 0 ? 'where' : 'orWhere'}($field, 'like', '%'.$search.'%');
                }
            });
        }

        $sortKey = array_key_exists((string) ($filters['sort'] ?? ''), $report['sorts'])
            ? (string) $filters['sort']
            : $report['default_sort'];
        $direction = ($filters['direction'] ?? $report['default_direction']) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($report['sorts'][$sortKey], $direction);

        return $query->paginate(
            (int) ($filters['per_page'] ?? 25),
            ['*'],
            'page',
            (int) ($filters['page'] ?? 1),
        );
    }

    private function normalizeRows(Collection $rows, array $columns): Collection
    {
        $numericKeys = collect($columns)->whereIn('type', ['number', 'currency', 'signed_currency', 'percent'])->pluck('key')->all();

        return $rows->map(function ($row) use ($numericKeys) {
            $normalized = (array) $row;
            foreach ($numericKeys as $key) {
                if (array_key_exists($key, $normalized)) {
                    $normalized[$key] = $normalized[$key] === null ? null : (float) $normalized[$key];
                }
            }

            return $normalized;
        });
    }

    private function safeIds(array $branchIds): array
    {
        return $branchIds === [] ? [-1] : array_map('intval', $branchIds);
    }

    private function expiryStatusExpression(string $column): string
    {
        $today = today()->toDateString();
        $warning = today()->addDays(90)->toDateString();

        return "CASE WHEN {$column} IS NULL THEN 'Tanpa ED' WHEN {$column} < '{$today}' THEN 'Expired' WHEN {$column} <= '{$warning}' THEN 'Hampir Expired' ELSE 'Aman' END";
    }

    private function daysExpression(string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return 'CAST(julianday('.$column.') - julianday(\''.today()->toDateString().'\') AS INTEGER)';
        }

        return 'DATEDIFF('.$column.', \''.today()->toDateString().'\')';
    }

    private function dateExpression(string $column): string
    {
        return 'DATE('.$column.')';
    }
}
