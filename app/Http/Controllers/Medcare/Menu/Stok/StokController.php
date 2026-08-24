<?php

namespace App\Http\Controllers\Medcare\Menu\Stok;

use App\Http\Controllers\Controller;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Stok\StockService;
use App\Support\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    private const DEFAULT_EXPIRED_WARNING_DAYS = 90;

    public function __construct(private readonly StockService $stockService) {}

    public function stok(Request $request)
    {
        return view('medcare.menu.stok.stok', [
            'stockOpnameReadOnly' => (bool) $request->attributes->get('stock_opname_read_only', false),
            'activeStockOpname' => $request->attributes->get('active_stock_opname'),
        ]);
    }

    public function kartuStok()
    {
        return view('medcare.menu.stok.kartuStok');
    }

    public function stockTable(Request $request)
    {
        $warningDays = $this->warningDays($request);
        $today = Carbon::today();
        $warningDate = Carbon::today()->addDays($warningDays);
        $branchIds = BranchAccess::userBranchIds();
        $rows = collect();

        if (! empty($branchIds)) {
            $rows = MasterObatModel::with([
                'satuan',
                'stokBatches' => function ($query) use ($branchIds) {
                    $this->scopeStockBatchBranch($query, $branchIds);
                    $query->orderBy('expired_date')->orderBy('no_batch');
                },
            ])
                ->orderBy('nama_obat')
                ->get()
                ->map(fn ($obat) => $this->stockRow($obat, $today, $warningDate));
        }

        $search = $this->stockSearchTerm($request);
        $totalAvailable = $rows->count();

        if ($search !== '') {
            $rows = $this->filterStockRows($rows, $search);
        }

        $totalSearchMatched = $rows->count();
        $statusCounts = $this->stockStatusCounts($rows);
        $alertStatus = $this->stockStatusFilter($request);

        if ($alertStatus !== '') {
            $rows = $rows->where('status', $alertStatus)->values();
        }

        $summary = [
            'total_item' => $rows->count(),
            'total_available' => $totalAvailable,
            'total_search_matched' => $totalSearchMatched,
            'total_stok' => $rows->sum('total_stok'),
            'nilai_stok' => $rows->sum('nilai_stok'),
            'stok_menipis' => $rows->where('is_low_stock', true)->count(),
            'expired' => $rows->where('expired_count', '>', 0)->count(),
            'akan_expired' => $rows->where('near_expired_count', '>', 0)->count(),
            'stok_kosong' => $rows->where('total_stok', '<=', 0)->count(),
            'status_counts' => $statusCounts,
            'active_search' => $search,
            'active_status' => $alertStatus,
            'active_status_label' => $alertStatus !== '' ? $this->statusLabel($alertStatus) : 'Semua stok',
            'expired_warning_days' => $warningDays,
        ];

        return DataTables::of($rows)
            ->addIndexColumn()
            ->addColumn('actions', function ($row) {
                $batchButton = '<button type="button" class="btn btn-sm btn-info" onclick="filterBatchObat('.$row['id'].')"><i class="mdi mdi-package-variant-closed"></i></button>';
                $cardButton = '<a class="btn btn-sm btn-primary" href="'.route('kartuStok.kartuStok', ['obat_id' => $row['id']]).'"><i class="mdi mdi-card-bulleted-outline"></i></a>';

                return $batchButton.' '.$cardButton;
            })
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function batchTable(Request $request)
    {
        $warningDays = $this->warningDays($request);
        $today = Carbon::today();
        $warningDate = Carbon::today()->addDays($warningDays);
        $query = StokBatchModel::with(['obat.satuan', 'obat.golongan'])
            ->where('qty', '>', 0)
            ->orderBy('expired_date')
            ->orderBy('no_batch');

        $this->scopeStockBatchBranch($query);

        if ($request->filled('obat_id')) {
            $query->where('obat_id', $request->obat_id);
        }

        $rows = $query->get()
            ->map(function ($batch) use ($today, $warningDate) {
                $status = $this->batchStatus($batch, $today, $warningDate);
                $marginPrice = $this->stockService->batchSellingPriceMarginPreview($batch);

                return [
                    'id' => $batch->id,
                    'obat_id' => $batch->obat_id,
                    'kode_obat' => $batch->obat->kode_obat ?? '-',
                    'nama_obat' => $batch->obat->nama_obat ?? '-',
                    'satuan' => $batch->obat->satuan->nama ?? '-',
                    'no_batch' => $batch->no_batch,
                    'expired_date' => optional($batch->expired_date)->format('Y-m-d'),
                    'qty' => (float) $batch->qty,
                    'harga_beli' => (float) $batch->harga_beli,
                    'harga_jual' => (float) $batch->harga_jual,
                    'harga_jual_margin' => (float) $marginPrice['harga_jual'],
                    'margin_harga_beli_dasar' => (float) $marginPrice['harga_beli_dasar'],
                    'margin_harga_beli_include_ppn' => (float) $marginPrice['harga_beli_include_ppn'],
                    'margin_faktor_jual' => (float) $marginPrice['faktor_jual'],
                    'margin_ppn' => (float) $marginPrice['ppn'],
                    'margin_has_margin' => (bool) $marginPrice['has_margin'],
                    'margin_reference' => $marginPrice['margin_reference'],
                    'diskon' => (float) ($batch->diskon ?? 0),
                    'ppn' => (float) ($batch->ppn ?? 0),
                    'nilai_stok' => (float) $batch->qty * (float) $batch->harga_beli,
                    'status' => $status,
                    'status_label' => $this->statusLabel($status),
                    'last_movement_at' => optional($batch->last_movement_at)->format('Y-m-d H:i'),
                ];
            });

        if ($request->filled('expiry_status')) {
            $rows = $rows->where('status', $request->expiry_status)->values();
        }

        $summary = [
            'total_batch' => $rows->count(),
            'total_stok' => $rows->sum('qty'),
            'nilai_stok' => $rows->sum('nilai_stok'),
            'expired' => $rows->where('status', 'expired')->count(),
            'akan_expired' => $rows->where('status', 'akan_expired')->count(),
        ];

        return DataTables::of($rows)
            ->addIndexColumn()
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function riwayatHargaTable(Request $request)
    {
        $branchIds = BranchAccess::userBranchIds();
        $query = RiwayatHargaModel::with(['obat.satuan', 'batch', 'changedBy'])
            ->whereHas('batch', function ($query) use ($branchIds) {
                $this->scopeStockBatchBranch($query, $branchIds);
            })
            ->when($request->filled('obat_id'), fn ($query) => $query->where('obat_id', $request->obat_id))
            ->when($request->filled('stok_batch_id'), fn ($query) => $query->where('stok_batch_id', $request->stok_batch_id))
            ->latest('created_at')
            ->latest('id');

        $rows = $query->get()
            ->map(function ($riwayat) {
                $hargaLama = (float) $riwayat->harga_jual_lama;
                $hargaBaru = (float) $riwayat->harga_jual_baru;

                return [
                    'id' => $riwayat->id,
                    'created_at' => optional($riwayat->created_at)->format('Y-m-d H:i'),
                    'obat_id' => $riwayat->obat_id,
                    'stok_batch_id' => $riwayat->stok_batch_id,
                    'kode_obat' => $riwayat->obat->kode_obat ?? '-',
                    'nama_obat' => $riwayat->obat->nama_obat ?? '-',
                    'satuan' => $riwayat->obat->satuan->nama ?? '-',
                    'no_batch' => $riwayat->batch->no_batch ?? '-',
                    'expired_date' => optional($riwayat->batch?->expired_date)->format('Y-m-d'),
                    'harga_jual_lama' => $hargaLama,
                    'harga_jual_baru' => $hargaBaru,
                    'selisih' => $hargaBaru - $hargaLama,
                    'alasan' => $riwayat->alasan ?? '-',
                    'user' => $riwayat->changedBy->name ?? '-',
                ];
            });

        $latestRow = $rows->first();

        $summary = [
            'total_riwayat' => $rows->count(),
            'kenaikan' => $rows->where('selisih', '>', 0)->count(),
            'penurunan' => $rows->where('selisih', '<', 0)->count(),
            'terakhir' => $latestRow['created_at'] ?? null,
        ];

        return DataTables::of($rows)
            ->addIndexColumn()
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function kartuTable(Request $request)
    {
        $query = KartuStokModel::with(['obat.satuan', 'createdBy'])
            ->when($request->filled('obat_id'), fn ($query) => $query->where('obat_id', $request->obat_id))
            ->when($request->filled('stok_batch_id'), fn ($query) => $query->where('stok_batch_id', $request->stok_batch_id))
            ->when($request->filled('jenis_mutasi'), fn ($query) => $query->where('jenis_mutasi', $request->jenis_mutasi))
            ->when($request->filled('date_start'), fn ($query) => $query->whereDate('tanggal_mutasi', '>=', $request->date_start))
            ->when($request->filled('date_end'), fn ($query) => $query->whereDate('tanggal_mutasi', '<=', $request->date_end))
            ->latest('tanggal_mutasi')
            ->latest('id');

        $this->scopeKartuStokBranch($query);

        $rows = $query->get()->map(function ($mutasi) {
            return [
                'id' => $mutasi->id,
                'tanggal_mutasi' => optional($mutasi->tanggal_mutasi)->format('Y-m-d H:i'),
                'obat_id' => $mutasi->obat_id,
                'kode_obat' => $mutasi->obat->kode_obat ?? '-',
                'nama_obat' => $mutasi->obat->nama_obat ?? '-',
                'satuan' => $mutasi->obat->satuan->nama ?? '-',
                'no_batch' => $mutasi->no_batch ?? '-',
                'expired_date' => optional($mutasi->expired_date)->format('Y-m-d'),
                'jenis_mutasi' => $mutasi->jenis_mutasi,
                'jenis_label' => $this->mutationLabel($mutasi->jenis_mutasi),
                'qty_masuk' => (float) $mutasi->qty_masuk,
                'qty_keluar' => (float) $mutasi->qty_keluar,
                'saldo_batch' => (float) $mutasi->saldo_batch,
                'saldo_total' => (float) $mutasi->saldo_total,
                'harga_beli' => (float) $mutasi->harga_beli,
                'nomor_referensi' => $mutasi->nomor_referensi ?? '-',
                'keterangan' => $mutasi->keterangan ?? '-',
                'user' => $mutasi->createdBy->name ?? '-',
            ];
        });

        $summary = [
            'jumlah_mutasi' => $rows->count(),
            'total_masuk' => $rows->sum('qty_masuk'),
            'total_keluar' => $rows->sum('qty_keluar'),
            'total_expired' => $rows->where('jenis_mutasi', 'expired')->sum('qty_keluar'),
            'saldo_tercatat' => $this->saldoTercatat($request),
        ];

        return DataTables::of($rows)
            ->addIndexColumn()
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function obatOptions(Request $request)
    {
        $branchIds = BranchAccess::userBranchIds();

        if ($request->filled('id')) {
            $obat = MasterObatModel::withSum([
                'stokBatches as total_stok' => function ($query) use ($branchIds) {
                    $this->scopeStockBatchBranch($query, $branchIds);
                },
            ], 'qty')->find($request->id);

            return response()->json($obat ? [[
                'id' => $obat->id,
                'text' => $obat->kode_obat.' - '.$obat->nama_obat,
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => $obat->nama_obat,
                'total_stok' => (float) ($obat->total_stok ?? 0),
            ]] : []);
        }

        $search = trim((string) $request->input('q', ''));

        $obats = MasterObatModel::query()
            ->withSum([
                'stokBatches as total_stok' => function ($query) use ($branchIds) {
                    $this->scopeStockBatchBranch($query, $branchIds);
                },
            ], 'qty')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_obat', 'like', '%'.$search.'%')
                        ->orWhere('kode_obat', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('nama_obat')
            ->limit(50)
            ->get()
            ->map(function ($obat) {
                return [
                    'id' => $obat->id,
                    'text' => $obat->kode_obat.' - '.$obat->nama_obat,
                    'kode_obat' => $obat->kode_obat,
                    'nama_obat' => $obat->nama_obat,
                    'total_stok' => (float) ($obat->total_stok ?? 0),
                ];
            });

        return response()->json($obats);
    }

    public function batchOptions(Request $request, $obatId)
    {
        $batches = StokBatchModel::where('obat_id', $obatId)
            ->when(! $request->boolean('include_empty'), fn ($query) => $query->where('qty', '>', 0))
            ->orderBy('expired_date')
            ->orderBy('no_batch');

        $this->scopeStockBatchBranch($batches);

        $batches = $batches->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'text' => $batch->no_batch
                        .' | ED '.(optional($batch->expired_date)->format('Y-m-d') ?: '-')
                        .' | Diskon '.number_format((float) ($batch->diskon ?? 0), 2, ',', '.').'%'
                        .' | PPN '.number_format((float) ($batch->ppn ?? 0), 2, ',', '.').'%'
                        .' | Stok '.number_format((float) $batch->qty, 2, ',', '.'),
                    'no_batch' => $batch->no_batch,
                    'expired_date' => optional($batch->expired_date)->format('Y-m-d'),
                    'qty' => (float) $batch->qty,
                    'harga_beli' => (float) $batch->harga_beli,
                    'harga_jual' => (float) $batch->harga_jual,
                    'diskon' => (float) ($batch->diskon ?? 0),
                    'ppn' => (float) ($batch->ppn ?? 0),
                ];
            });

        return response()->json($batches);
    }

    public function storeMutation(Request $request)
    {
        $validated = $request->validate([
            'jenis_mutasi' => ['required', Rule::in(['masuk', 'keluar', 'expired', 'penyesuaian_masuk', 'penyesuaian_keluar'])],
            'obat_id' => ['required', 'integer', 'exists:master_obats,id'],
            'stok_batch_id' => ['nullable', 'integer', 'exists:stok_batches,id'],
            'no_batch' => ['nullable', 'string', 'max:80'],
            'expired_date' => ['nullable', 'string'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'harga_beli' => ['nullable', 'numeric', 'min:0'],
            'harga_jual' => ['nullable', 'numeric', 'min:0'],
            'ppn' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'alasan_harga' => ['nullable', 'string', 'max:1000'],
            'tanggal_mutasi' => ['required', 'string'],
            'nomor_referensi' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $this->stockService->recordManualMutation($validated);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Mutasi stok berhasil dicatat.',
        ]);
    }

    public function updateBatchHargaJual(Request $request, $id)
    {
        $request->merge([
            'metode_harga' => $request->input('metode_harga', 'manual'),
        ]);

        $validated = $request->validate([
            'metode_harga' => ['required', Rule::in(['manual', 'margin'])],
            'harga_jual_baru' => [
                Rule::requiredIf(fn () => $request->input('metode_harga') === 'manual'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'alasan' => ['required', 'string', 'max:1000'],
        ]);

        $result = DB::transaction(function () use ($validated, $id) {
            if ($validated['metode_harga'] === 'margin') {
                return $this->stockService->updateBatchSellingPriceFromMargin(
                    (int) $id,
                    $validated['alasan'],
                    Auth::id()
                );
            }

            return $this->stockService->updateBatchSellingPrice(
                (int) $id,
                (float) $validated['harga_jual_baru'],
                $validated['alasan'],
                Auth::id()
            );
        });

        return response()->json([
            'status' => 'success',
            'changed' => $result['changed'],
            'harga_jual_baru' => $result['harga_jual_baru'],
            'message' => $result['changed']
                ? 'Harga jual batch berhasil diperbarui.'
                : 'Harga jual batch tidak berubah.',
        ]);
    }

    private function stockRow(MasterObatModel $obat, Carbon $today, Carbon $warningDate): array
    {
        $activeBatches = $obat->stokBatches->filter(fn ($batch) => (float) $batch->qty > 0)->values();
        $totalBatchStock = (float) $activeBatches->sum(fn ($batch) => (float) $batch->qty);
        $totalStock = $totalBatchStock;
        $nearestBatch = $activeBatches->filter(fn ($batch) => $batch->expired_date)->sortBy('expired_date')->first();
        $latestBatch = $obat->stokBatches
            ->sortByDesc(fn ($batch) => $batch->last_movement_at ? $batch->last_movement_at->timestamp : $batch->id)
            ->first();
        $expiredCount = $activeBatches
            ->filter(fn ($batch) => $batch->expired_date && $batch->expired_date->lt($today))
            ->count();
        $nearExpiredCount = $activeBatches
            ->filter(fn ($batch) => $batch->expired_date && $batch->expired_date->gte($today) && $batch->expired_date->lte($warningDate))
            ->count();
        $minimumStock = (float) $obat->stok_minimum;
        $isLowStock = $minimumStock > 0 && $totalStock <= $minimumStock;
        $status = $this->stockStatus($totalStock, $isLowStock, $expiredCount, $nearExpiredCount);
        $lastPrice = (float) ($latestBatch->harga_beli ?? $obat->harga_beli ?? 0);
        $stockValue = (float) $activeBatches->sum(fn ($batch) => (float) $batch->qty * (float) $batch->harga_beli);

        return [
            'id' => $obat->id,
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'satuan' => $obat->satuan->nama ?? '-',
            'batch_numbers' => $activeBatches->pluck('no_batch')->filter()->implode(', '),
            'total_stok' => $totalStock,
            'stok_minimum' => $minimumStock,
            'batch_count' => $activeBatches->count(),
            'expired_count' => $expiredCount,
            'near_expired_count' => $nearExpiredCount,
            'nearest_expired_date' => $this->formatDateValue($nearestBatch?->expired_date),
            'harga_beli_terakhir' => $lastPrice,
            'nilai_stok' => $stockValue,
            'is_low_stock' => $isLowStock,
            'status' => $status,
            'status_label' => $this->statusLabel($status),
        ];
    }

    private function stockStatus(float $totalStock, bool $isLowStock, int $expiredCount, int $nearExpiredCount): string
    {
        if ($totalStock <= 0) {
            return 'kosong';
        }

        if ($expiredCount > 0) {
            return 'expired';
        }

        if ($nearExpiredCount > 0) {
            return 'akan_expired';
        }

        if ($isLowStock) {
            return 'menipis';
        }

        return 'aman';
    }

    private function batchStatus(StokBatchModel $batch, Carbon $today, Carbon $warningDate): string
    {
        if (! $batch->expired_date) {
            return 'aman';
        }

        if ($batch->expired_date->lt($today)) {
            return 'expired';
        }

        if ($batch->expired_date->lte($warningDate)) {
            return 'akan_expired';
        }

        return 'aman';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'kosong' => 'Stok Kosong',
            'menipis' => 'Stok Menipis',
            'expired' => 'Expired',
            'akan_expired' => 'Akan Expired',
            default => 'Aman',
        };
    }

    private function stockSearchTerm(Request $request): string
    {
        $search = trim((string) $request->input('stock_search', ''));

        if ($search !== '') {
            return $search;
        }

        return trim((string) $request->input('search.value', ''));
    }

    private function stockStatusFilter(Request $request): string
    {
        $status = (string) $request->input('alert_status', '');

        return in_array($status, ['aman', 'menipis', 'kosong', 'expired', 'akan_expired'], true)
            ? $status
            : '';
    }

    private function filterStockRows($rows, string $search)
    {
        $terms = collect(preg_split('/\s+/', Str::lower($search), -1, PREG_SPLIT_NO_EMPTY));

        if ($terms->isEmpty()) {
            return $rows->values();
        }

        return $rows->filter(function (array $row) use ($terms) {
            $haystack = Str::lower(implode(' ', array_filter([
                $row['kode_obat'] ?? '',
                $row['nama_obat'] ?? '',
                $row['satuan'] ?? '',
                $row['status'] ?? '',
                $row['status_label'] ?? '',
                $row['nearest_expired_date'] ?? '',
                $row['batch_numbers'] ?? '',
            ], fn ($value) => $value !== null && $value !== '')));

            return $terms->every(fn ($term) => Str::contains($haystack, $term));
        })->values();
    }

    private function stockStatusCounts($rows): array
    {
        return [
            'all' => $rows->count(),
            'aman' => $rows->where('status', 'aman')->count(),
            'menipis' => $rows->where('status', 'menipis')->count(),
            'kosong' => $rows->where('status', 'kosong')->count(),
            'expired' => $rows->where('status', 'expired')->count(),
            'akan_expired' => $rows->where('status', 'akan_expired')->count(),
        ];
    }

    private function formatDateValue($date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function mutationLabel(string $jenisMutasi): string
    {
        return match ($jenisMutasi) {
            'masuk' => 'Obat Masuk',
            'keluar' => 'Obat Keluar',
            'expired' => 'Obat Expired',
            'penyesuaian_masuk' => 'Penyesuaian Masuk',
            'penyesuaian_keluar' => 'Penyesuaian Keluar',
            'penyesuaian_opname_masuk' => 'Stock Opname Masuk',
            'penyesuaian_opname_keluar' => 'Stock Opname Keluar',
            'pembatalan_penerimaan' => 'Pembatalan Penerimaan',
            'retur_pembelian' => 'Retur Pembelian',
            'pembatalan_retur_pembelian' => 'Pembatalan Retur Pembelian',
            'penjualan' => 'Penjualan POS',
            'pembatalan_penjualan' => 'Pembatalan Penjualan',
            'retur_penjualan' => 'Retur Penjualan',
            'pembatalan_retur_penjualan' => 'Pembatalan Retur Penjualan',
            'saldo_awal' => 'Saldo Awal',
            default => ucwords(str_replace('_', ' ', $jenisMutasi)),
        };
    }

    private function saldoTercatat(Request $request): float
    {
        $query = StokBatchModel::query();

        $this->scopeStockBatchBranch($query);

        if ($request->filled('obat_id')) {
            $query->where('obat_id', $request->obat_id);
        }

        return (float) $query->sum('qty');
    }

    private function scopeStockBatchBranch($query, ?array $branchIds = null): void
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        if (empty($branchIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('branch_id', $branchIds);
    }

    private function scopeKartuStokBranch($query, ?array $branchIds = null): void
    {
        $branchIds = $branchIds ?? BranchAccess::userBranchIds();

        if (empty($branchIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('branch_id', $branchIds);
    }

    private function warningDays(Request $request): int
    {
        return min(365, max(1, (int) $request->input('expired_days', self::DEFAULT_EXPIRED_WARNING_DAYS)));
    }
}
