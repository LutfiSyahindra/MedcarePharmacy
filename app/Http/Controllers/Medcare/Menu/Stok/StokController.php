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
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StokController extends Controller
{
    private const DEFAULT_EXPIRED_WARNING_DAYS = 90;

    private array $batchMarginPreviews = [];

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
        $today = Carbon::today()->toDateString();
        $warningDate = Carbon::today()->addDays($warningDays)->toDateString();
        $branchIds = BranchAccess::userBranchIds();
        $batchNumberExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "GROUP_CONCAT(CASE WHEN qty > 0 THEN no_batch END, ', ')"
            : "GROUP_CONCAT(CASE WHEN qty > 0 THEN no_batch END SEPARATOR ', ')";
        $batchTotals = DB::table('stok_batches')
            ->whereIn('branch_id', $branchIds)
            ->groupBy('obat_id')
            ->select('obat_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty > 0 THEN qty ELSE 0 END), 0) as total_stok')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty > 0 THEN qty * harga_beli ELSE 0 END), 0) as nilai_stok')
            ->selectRaw('COALESCE(SUM(CASE WHEN qty > 0 THEN qty * COALESCE(harga_jual, 0) ELSE 0 END), 0) as nilai_stok_jual')
            ->selectRaw('SUM(CASE WHEN qty > 0 THEN 1 ELSE 0 END) as batch_count')
            ->selectRaw('SUM(CASE WHEN qty > 0 AND expired_date IS NOT NULL AND expired_date < ? THEN 1 ELSE 0 END) as expired_count', [$today])
            ->selectRaw('SUM(CASE WHEN qty > 0 AND expired_date IS NOT NULL AND expired_date >= ? AND expired_date <= ? THEN 1 ELSE 0 END) as near_expired_count', [$today, $warningDate])
            ->selectRaw('MIN(CASE WHEN qty > 0 AND expired_date IS NOT NULL THEN expired_date ELSE NULL END) as nearest_expired_date')
            ->selectRaw("{$batchNumberExpression} as batch_numbers");
        $latestPrice = DB::table('stok_batches as latest_batches')
            ->select('latest_batches.harga_beli')
            ->whereColumn('latest_batches.obat_id', 'medicines.id')
            ->whereIn('latest_batches.branch_id', $branchIds)
            ->orderByRaw('COALESCE(latest_batches.last_movement_at, latest_batches.created_at) DESC')
            ->orderByDesc('latest_batches.id')
            ->limit(1);
        $baseQuery = DB::table('master_obats as medicines')
            ->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->leftJoinSub($batchTotals, 'batch_totals', fn ($join) => $join->on('batch_totals.obat_id', '=', 'medicines.id'))
            ->when(empty($branchIds), fn ($query) => $query->whereRaw('1 = 0'))
            ->select([
                'medicines.id',
                'medicines.kode_obat',
                'medicines.nama_obat',
                'medicines.stok_minimum',
                DB::raw("COALESCE(units.nama, '-') as satuan"),
                DB::raw("COALESCE(batch_totals.batch_numbers, '') as batch_numbers"),
                DB::raw('COALESCE(batch_totals.total_stok, 0) as total_stok'),
                DB::raw('COALESCE(batch_totals.nilai_stok, 0) as nilai_stok'),
                DB::raw('COALESCE(batch_totals.nilai_stok_jual, 0) as nilai_stok_jual'),
                DB::raw('COALESCE(batch_totals.batch_count, 0) as batch_count'),
                DB::raw('COALESCE(batch_totals.expired_count, 0) as expired_count'),
                DB::raw('COALESCE(batch_totals.near_expired_count, 0) as near_expired_count'),
                'batch_totals.nearest_expired_date',
            ])
            ->selectSub($latestPrice, 'harga_beli_terakhir')
            ->selectRaw('CASE WHEN medicines.stok_minimum > 0 AND COALESCE(batch_totals.total_stok, 0) <= medicines.stok_minimum THEN 1 ELSE 0 END as is_low_stock')
            ->selectRaw("CASE
                WHEN COALESCE(batch_totals.total_stok, 0) <= 0 THEN 'kosong'
                WHEN COALESCE(batch_totals.expired_count, 0) > 0 THEN 'expired'
                WHEN COALESCE(batch_totals.near_expired_count, 0) > 0 THEN 'akan_expired'
                WHEN medicines.stok_minimum > 0 AND COALESCE(batch_totals.total_stok, 0) <= medicines.stok_minimum THEN 'menipis'
                ELSE 'aman'
            END as status");
        $labeledQuery = DB::query()
            ->fromSub($baseQuery, 'stock_base')
            ->select('stock_base.*')
            ->selectRaw("CASE stock_base.status
                WHEN 'kosong' THEN 'Stok Kosong'
                WHEN 'menipis' THEN 'Stok Menipis'
                WHEN 'expired' THEN 'Expired'
                WHEN 'akan_expired' THEN 'Akan Expired'
                ELSE 'Aman'
            END as status_label");
        $rows = DB::query()->fromSub($labeledQuery, 'stock_rows')->select('stock_rows.*');

        $search = $this->stockSearchTerm($request);
        $totalAvailable = (clone $rows)->count();

        if ($search !== '') {
            foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $term) {
                $rows->where(function ($query) use ($term) {
                    $like = '%'.$term.'%';
                    $query->where('kode_obat', 'like', $like)
                        ->orWhere('nama_obat', 'like', $like)
                        ->orWhere('satuan', 'like', $like)
                        ->orWhere('batch_numbers', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('status_label', 'like', $like);
                });
            }
        }

        $searchSummary = $this->stockQuerySummary($rows);
        $totalSearchMatched = (int) ($searchSummary->total_item ?? 0);
        $statusCounts = [
            'all' => $totalSearchMatched,
            'aman' => (int) ($searchSummary->status_aman ?? 0),
            'menipis' => (int) ($searchSummary->status_menipis ?? 0),
            'kosong' => (int) ($searchSummary->status_kosong ?? 0),
            'expired' => (int) ($searchSummary->status_expired ?? 0),
            'akan_expired' => (int) ($searchSummary->status_akan_expired ?? 0),
        ];
        $alertStatus = $this->stockStatusFilter($request);

        if ($alertStatus !== '') {
            $rows->where('status', $alertStatus);
        }

        $filteredSummary = $alertStatus === '' ? $searchSummary : $this->stockQuerySummary($rows);

        $summary = [
            'total_item' => (int) ($filteredSummary->total_item ?? 0),
            'total_available' => $totalAvailable,
            'total_search_matched' => $totalSearchMatched,
            'total_stok' => (float) ($filteredSummary->total_stok ?? 0),
            'nilai_stok' => (float) ($filteredSummary->nilai_stok ?? 0),
            'nilai_stok_jual' => (float) ($filteredSummary->nilai_stok_jual ?? 0),
            'stok_menipis' => (int) ($filteredSummary->stok_menipis ?? 0),
            'expired' => (int) ($filteredSummary->expired ?? 0),
            'akan_expired' => (int) ($filteredSummary->akan_expired ?? 0),
            'stok_kosong' => (int) ($filteredSummary->stok_kosong ?? 0),
            'status_counts' => $statusCounts,
            'active_search' => $search,
            'active_status' => $alertStatus,
            'active_status_label' => $alertStatus !== '' ? $this->statusLabel($alertStatus) : 'Semua stok',
            'expired_warning_days' => $warningDays,
        ];

        return DataTables::of($rows->orderBy('nama_obat'))
            ->addIndexColumn()
            ->editColumn('harga_beli_terakhir', fn ($row) => (float) ($row->harga_beli_terakhir ?? 0))
            ->editColumn('nilai_stok', fn ($row) => (float) ($row->nilai_stok ?? 0))
            ->editColumn('nilai_stok_jual', fn ($row) => (float) ($row->nilai_stok_jual ?? 0))
            ->addColumn('actions', function ($row) {
                $batchButton = '<button type="button" class="btn btn-sm btn-info" onclick="filterBatchObat('.$row->id.')"><i class="mdi mdi-package-variant-closed"></i></button>';
                $cardButton = '<a class="btn btn-sm btn-primary" href="'.route('kartuStok.kartuStok', ['obat_id' => $row->id]).'"><i class="mdi mdi-card-bulleted-outline"></i></a>';

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
        $query = StokBatchModel::with(['obat.satuan', 'obat.golongan', 'obat.mainGolongan', 'obat.subGolongan'])
            ->where('qty', '>', 0)
            ->orderBy('expired_date')
            ->orderBy('no_batch');

        $this->scopeStockBatchBranch($query);

        if ($request->filled('obat_id')) {
            $query->where('obat_id', $request->obat_id);
        }

        $this->applyBatchExpiryFilter($query, (string) $request->input('expiry_status'), $today, $warningDate);

        $batchSummary = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total_batch')
            ->selectRaw('COALESCE(SUM(qty), 0) as total_stok')
            ->selectRaw('COALESCE(SUM(qty * harga_beli), 0) as nilai_stok')
            ->selectRaw('COALESCE(SUM(qty * COALESCE(harga_jual, 0)), 0) as nilai_stok_jual')
            ->selectRaw('SUM(CASE WHEN expired_date IS NOT NULL AND expired_date < ? THEN 1 ELSE 0 END) as expired', [$today->toDateString()])
            ->selectRaw('SUM(CASE WHEN expired_date IS NOT NULL AND expired_date >= ? AND expired_date <= ? THEN 1 ELSE 0 END) as akan_expired', [$today->toDateString(), $warningDate->toDateString()])
            ->first();

        $summary = [
            'total_batch' => (int) ($batchSummary->total_batch ?? 0),
            'total_stok' => (float) ($batchSummary->total_stok ?? 0),
            'nilai_stok' => (float) ($batchSummary->nilai_stok ?? 0),
            'nilai_stok_jual' => (float) ($batchSummary->nilai_stok_jual ?? 0),
            'expired' => (int) ($batchSummary->expired ?? 0),
            'akan_expired' => (int) ($batchSummary->akan_expired ?? 0),
        ];

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value', ''));

                if ($keyword !== '') {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('no_batch', 'like', '%'.$keyword.'%')
                            ->orWhereHas('obat', fn ($obat) => $obat
                                ->where('kode_obat', 'like', '%'.$keyword.'%')
                                ->orWhere('nama_obat', 'like', '%'.$keyword.'%'));
                    });
                }
            })
            ->addColumn('kode_obat', fn (StokBatchModel $batch) => $batch->obat->kode_obat ?? '-')
            ->addColumn('nama_obat', fn (StokBatchModel $batch) => $batch->obat->nama_obat ?? '-')
            ->addColumn('satuan', fn (StokBatchModel $batch) => $batch->obat->satuan->nama ?? '-')
            ->editColumn('expired_date', fn (StokBatchModel $batch) => optional($batch->expired_date)->format('Y-m-d'))
            ->editColumn('qty', fn (StokBatchModel $batch) => (float) $batch->qty)
            ->editColumn('harga_beli', fn (StokBatchModel $batch) => (float) $batch->harga_beli)
            ->editColumn('harga_jual', fn (StokBatchModel $batch) => (float) $batch->harga_jual)
            ->editColumn('diskon', fn (StokBatchModel $batch) => (float) ($batch->diskon ?? 0))
            ->editColumn('ppn', fn (StokBatchModel $batch) => (float) ($batch->ppn ?? 0))
            ->addColumn('nilai_stok', fn (StokBatchModel $batch) => (float) $batch->qty * (float) $batch->harga_beli)
            ->addColumn('nilai_stok_jual', fn (StokBatchModel $batch) => (float) $batch->qty * (float) $batch->harga_jual)
            ->addColumn('status', fn (StokBatchModel $batch) => $this->batchStatus($batch, $today, $warningDate))
            ->addColumn('status_label', fn (StokBatchModel $batch) => $this->statusLabel($this->batchStatus($batch, $today, $warningDate)))
            ->editColumn('last_movement_at', fn (StokBatchModel $batch) => optional($batch->last_movement_at)->format('Y-m-d H:i'))
            ->addColumn('harga_jual_margin', fn (StokBatchModel $batch) => (float) $this->batchMarginPreview($batch)['harga_jual'])
            ->addColumn('margin_harga_beli_dasar', fn (StokBatchModel $batch) => (float) $this->batchMarginPreview($batch)['harga_beli_dasar'])
            ->addColumn('margin_harga_beli_include_ppn', fn (StokBatchModel $batch) => (float) $this->batchMarginPreview($batch)['harga_beli_include_ppn'])
            ->addColumn('margin_faktor_jual', fn (StokBatchModel $batch) => (float) $this->batchMarginPreview($batch)['faktor_jual'])
            ->addColumn('margin_ppn', fn (StokBatchModel $batch) => (float) $this->batchMarginPreview($batch)['ppn'])
            ->addColumn('margin_has_margin', fn (StokBatchModel $batch) => (bool) $this->batchMarginPreview($batch)['has_margin'])
            ->addColumn('margin_reference', fn (StokBatchModel $batch) => $this->batchMarginPreview($batch)['margin_reference'])
            ->removeColumn('obat')
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

        $historySummary = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as total_riwayat')
            ->selectRaw('SUM(CASE WHEN harga_jual_baru > harga_jual_lama THEN 1 ELSE 0 END) as kenaikan')
            ->selectRaw('SUM(CASE WHEN harga_jual_baru < harga_jual_lama THEN 1 ELSE 0 END) as penurunan')
            ->selectRaw('MAX(created_at) as terakhir')
            ->first();

        $summary = [
            'total_riwayat' => (int) ($historySummary->total_riwayat ?? 0),
            'kenaikan' => (int) ($historySummary->kenaikan ?? 0),
            'penurunan' => (int) ($historySummary->penurunan ?? 0),
            'terakhir' => $historySummary?->terakhir ? Carbon::parse($historySummary->terakhir)->format('Y-m-d H:i') : null,
        ];

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value', ''));

                if ($keyword !== '') {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('alasan', 'like', '%'.$keyword.'%')
                            ->orWhereHas('obat', fn ($obat) => $obat
                                ->where('kode_obat', 'like', '%'.$keyword.'%')
                                ->orWhere('nama_obat', 'like', '%'.$keyword.'%'))
                            ->orWhereHas('batch', fn ($batch) => $batch->where('no_batch', 'like', '%'.$keyword.'%'))
                            ->orWhereHas('changedBy', fn ($user) => $user->where('name', 'like', '%'.$keyword.'%'));
                    });
                }
            })
            ->editColumn('created_at', fn (RiwayatHargaModel $history) => optional($history->created_at)->format('Y-m-d H:i'))
            ->addColumn('kode_obat', fn (RiwayatHargaModel $history) => $history->obat->kode_obat ?? '-')
            ->addColumn('nama_obat', fn (RiwayatHargaModel $history) => $history->obat->nama_obat ?? '-')
            ->addColumn('satuan', fn (RiwayatHargaModel $history) => $history->obat->satuan->nama ?? '-')
            ->addColumn('no_batch', fn (RiwayatHargaModel $history) => $history->batch->no_batch ?? '-')
            ->addColumn('expired_date', fn (RiwayatHargaModel $history) => optional($history->batch?->expired_date)->format('Y-m-d'))
            ->editColumn('harga_jual_lama', fn (RiwayatHargaModel $history) => (float) $history->harga_jual_lama)
            ->editColumn('harga_jual_baru', fn (RiwayatHargaModel $history) => (float) $history->harga_jual_baru)
            ->addColumn('selisih', fn (RiwayatHargaModel $history) => (float) $history->harga_jual_baru - (float) $history->harga_jual_lama)
            ->editColumn('alasan', fn (RiwayatHargaModel $history) => $history->alasan ?? '-')
            ->addColumn('user', fn (RiwayatHargaModel $history) => $history->changedBy->name ?? '-')
            ->removeColumn('obat')
            ->removeColumn('batch')
            ->removeColumn('changed_by')
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function kartuTable(Request $request)
    {
        $request->validate([
            'obat_id' => ['nullable', 'integer'],
            'stok_batch_id' => ['nullable', 'integer'],
            'jenis_mutasi' => ['nullable', 'string', 'max:40'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $query = KartuStokModel::with(['obat.satuan', 'createdBy'])
            ->when($request->filled('obat_id'), fn ($query) => $query->where('obat_id', $request->obat_id))
            ->when($request->filled('stok_batch_id'), fn ($query) => $query->where('stok_batch_id', $request->stok_batch_id))
            ->when($request->filled('jenis_mutasi'), fn ($query) => $query->where('jenis_mutasi', $request->jenis_mutasi))
            ->when($request->filled('date_start'), fn ($query) => $query->where('tanggal_mutasi', '>=', $request->date_start.' 00:00:00'))
            ->when($request->filled('date_end'), fn ($query) => $query->where('tanggal_mutasi', '<', Carbon::createFromFormat('Y-m-d', $request->date_end)->addDay()->startOfDay()))
            ->latest('tanggal_mutasi')
            ->latest('id');

        $this->scopeKartuStokBranch($query);

        $movementSummary = (clone $query)
            ->reorder()
            ->selectRaw('COUNT(*) as jumlah_mutasi')
            ->selectRaw('COALESCE(SUM(qty_masuk), 0) as total_masuk')
            ->selectRaw('COALESCE(SUM(qty_keluar), 0) as total_keluar')
            ->selectRaw("COALESCE(SUM(CASE WHEN jenis_mutasi = 'expired' THEN qty_keluar ELSE 0 END), 0) as total_expired")
            ->first();

        $summary = [
            'jumlah_mutasi' => (int) ($movementSummary->jumlah_mutasi ?? 0),
            'total_masuk' => (float) ($movementSummary->total_masuk ?? 0),
            'total_keluar' => (float) ($movementSummary->total_keluar ?? 0),
            'total_expired' => (float) ($movementSummary->total_expired ?? 0),
            'saldo_tercatat' => $this->saldoTercatat($request),
        ];

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value', ''));

                if ($keyword !== '') {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('no_batch', 'like', '%'.$keyword.'%')
                            ->orWhere('nomor_referensi', 'like', '%'.$keyword.'%')
                            ->orWhere('keterangan', 'like', '%'.$keyword.'%')
                            ->orWhere('jenis_mutasi', 'like', '%'.$keyword.'%')
                            ->orWhereHas('obat', fn ($obat) => $obat
                                ->where('kode_obat', 'like', '%'.$keyword.'%')
                                ->orWhere('nama_obat', 'like', '%'.$keyword.'%'))
                            ->orWhereHas('createdBy', fn ($user) => $user->where('name', 'like', '%'.$keyword.'%'));
                    });
                }
            })
            ->editColumn('tanggal_mutasi', fn (KartuStokModel $movement) => optional($movement->tanggal_mutasi)->format('Y-m-d H:i'))
            ->addColumn('kode_obat', fn (KartuStokModel $movement) => $movement->obat->kode_obat ?? '-')
            ->addColumn('nama_obat', fn (KartuStokModel $movement) => $movement->obat->nama_obat ?? '-')
            ->addColumn('satuan', fn (KartuStokModel $movement) => $movement->obat->satuan->nama ?? '-')
            ->editColumn('expired_date', fn (KartuStokModel $movement) => optional($movement->expired_date)->format('Y-m-d'))
            ->addColumn('jenis_label', fn (KartuStokModel $movement) => $this->mutationLabel($movement->jenis_mutasi))
            ->editColumn('qty_masuk', fn (KartuStokModel $movement) => (float) $movement->qty_masuk)
            ->editColumn('qty_keluar', fn (KartuStokModel $movement) => (float) $movement->qty_keluar)
            ->editColumn('saldo_batch', fn (KartuStokModel $movement) => (float) $movement->saldo_batch)
            ->editColumn('saldo_total', fn (KartuStokModel $movement) => (float) $movement->saldo_total)
            ->editColumn('harga_beli', fn (KartuStokModel $movement) => (float) $movement->harga_beli)
            ->editColumn('nomor_referensi', fn (KartuStokModel $movement) => $movement->nomor_referensi ?? '-')
            ->editColumn('keterangan', fn (KartuStokModel $movement) => $movement->keterangan ?? '-')
            ->addColumn('user', fn (KartuStokModel $movement) => $movement->createdBy->name ?? '-')
            ->removeColumn('obat')
            ->removeColumn('created_by')
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

    private function stockQuerySummary($query): object
    {
        return DB::query()
            ->fromSub((clone $query)->reorder(), 'stock_summary_rows')
            ->selectRaw('COUNT(*) as total_item')
            ->selectRaw('COALESCE(SUM(total_stok), 0) as total_stok')
            ->selectRaw('COALESCE(SUM(nilai_stok), 0) as nilai_stok')
            ->selectRaw('COALESCE(SUM(nilai_stok_jual), 0) as nilai_stok_jual')
            ->selectRaw('SUM(CASE WHEN is_low_stock = 1 THEN 1 ELSE 0 END) as stok_menipis')
            ->selectRaw('SUM(CASE WHEN expired_count > 0 THEN 1 ELSE 0 END) as expired')
            ->selectRaw('SUM(CASE WHEN near_expired_count > 0 THEN 1 ELSE 0 END) as akan_expired')
            ->selectRaw('SUM(CASE WHEN total_stok <= 0 THEN 1 ELSE 0 END) as stok_kosong')
            ->selectRaw("SUM(CASE WHEN status = 'aman' THEN 1 ELSE 0 END) as status_aman")
            ->selectRaw("SUM(CASE WHEN status = 'menipis' THEN 1 ELSE 0 END) as status_menipis")
            ->selectRaw("SUM(CASE WHEN status = 'kosong' THEN 1 ELSE 0 END) as status_kosong")
            ->selectRaw("SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as status_expired")
            ->selectRaw("SUM(CASE WHEN status = 'akan_expired' THEN 1 ELSE 0 END) as status_akan_expired")
            ->first();
    }

    private function applyBatchExpiryFilter($query, string $status, Carbon $today, Carbon $warningDate): void
    {
        match ($status) {
            'expired' => $query->whereNotNull('expired_date')->where('expired_date', '<', $today->toDateString()),
            'akan_expired' => $query->whereNotNull('expired_date')->whereBetween('expired_date', [$today->toDateString(), $warningDate->toDateString()]),
            'aman' => $query->where(function ($query) use ($warningDate) {
                $query->whereNull('expired_date')->orWhere('expired_date', '>', $warningDate->toDateString());
            }),
            default => null,
        };
    }

    private function batchMarginPreview(StokBatchModel $batch): array
    {
        return $this->batchMarginPreviews[$batch->id]
            ??= $this->stockService->batchSellingPriceMarginPreview($batch);
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
