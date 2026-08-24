<?php

namespace App\Http\Controllers\Medcare\Menu\Stok;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\Menu\Stok\StockOpnameLogModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\RakPenyimpananModel;
use App\Services\Menu\Stok\StockOpnameReportPdf;
use App\Services\Menu\Stok\StockOpnameService;
use App\Services\Settings\Auth\RoleSettingService;
use App\Support\BranchAccess;
use App\Support\StockOpnameAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class StockOpnameController extends Controller
{
    public function __construct(
        private readonly StockOpnameService $service,
        private readonly RoleSettingService $roleSettings,
        private readonly StockOpnameAccess $stockOpnameAccess
    ) {}

    public function index()
    {
        $branchIds = BranchAccess::userBranchIds();
        $authorizationBranchIds = BranchAccess::approvalBranchIds();
        $countingBranchIds = BranchAccess::assignedUserBranchIds();
        $activeStockOpnameLock = $this->stockOpnameAccess->activeLock();
        $stockMenuLock = $this->stockOpnameAccess->stockMenusAreLocked() ? $activeStockOpnameLock : null;

        return view('medcare.menu.stok.stockOpname.index', [
            'branches' => BranchModel::query()->whereIn('id', $branchIds)->where('is_active', true)->orderBy('name')->get(),
            'authorizationBranches' => BranchModel::query()->whereIn('id', $authorizationBranchIds)->where('is_active', true)->orderBy('name')->get(),
            'canAuthorize' => ! empty($authorizationBranchIds),
            'canValidate' => ! empty($countingBranchIds),
            'stockMenuLock' => $stockMenuLock,
            'racks' => RakPenyimpananModel::query()->where('is_active', true)->orderBy('kode')->get(),
        ]);
    }

    public function table(Request $request)
    {
        $query = StockOpnameModel::query()
            ->with(['branch', 'rack', 'creator', 'verifier', 'approver'])
            ->withCount([
                'details',
                'details as counted_count' => fn ($query) => $query->whereNotNull('counted_at'),
                'details as difference_count' => fn ($query) => $query->whereRaw('ABS(COALESCE(selisih_validasi, selisih, 0)) >= 0.005'),
                'movements',
            ])
            ->whereIn('branch_id', BranchAccess::userBranchIds())
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->latest('tanggal_opname')
            ->latest('id');

        $rows = $query->get()->map(fn (StockOpnameModel $opname) => $this->headerPayload($opname));
        $statusCounts = $rows->countBy('status');

        return DataTables::of($rows)
            ->addIndexColumn()
            ->with([
                'summary' => [
                    'total' => $rows->count(),
                    'draft' => (int) ($statusCounts[StockOpnameModel::STATUS_DRAFT] ?? 0),
                    'counting' => (int) ($statusCounts[StockOpnameModel::STATUS_COUNTING] ?? 0),
                    'waiting' => (int) (($statusCounts[StockOpnameModel::STATUS_AWAITING_VERIFICATION] ?? 0)
                        + ($statusCounts[StockOpnameModel::STATUS_AWAITING_APPROVAL] ?? 0)),
                    'approved' => (int) ($statusCounts[StockOpnameModel::STATUS_APPROVED] ?? 0),
                    'adjusted' => (int) ($statusCounts[StockOpnameModel::STATUS_ADJUSTED] ?? 0),
                ],
            ])
            ->make(true);
    }

    public function store(Request $request)
    {
        $this->requireOpnameAuthorization();
        $validated = $this->validateHeader($request);

        $opname = DB::transaction(function () use ($validated) {
            $opname = StockOpnameModel::create([
                ...$validated,
                'nomor' => $this->generateNumber((int) $validated['branch_id'], $validated['tanggal_opname']),
                'status' => StockOpnameModel::STATUS_DRAFT,
                'created_by' => Auth::id(),
            ]);

            StockOpnameLogModel::create([
                'stock_opname_id' => $opname->id,
                'action' => 'create',
                'to_status' => StockOpnameModel::STATUS_DRAFT,
                'note' => 'Draft stock opname dibuat.',
                'performed_by' => Auth::id(),
                'performed_at' => now(),
            ]);

            return $opname;
        });

        return response()->json([
            'message' => 'Draft stock opname berhasil dibuat.',
            'opname' => $this->headerPayload($opname->load(['branch', 'rack', 'creator'])),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $opname = $this->findAccessible($id);
        $this->requireOpnameAuthorization((int) $opname->branch_id);
        $validated = $this->validateHeader($request);

        if ($opname->status !== StockOpnameModel::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Hanya draft yang dapat diubah.']);
        }

        $opname->fill($validated)->save();

        StockOpnameLogModel::create([
            'stock_opname_id' => $opname->id,
            'action' => 'update',
            'from_status' => $opname->status,
            'to_status' => $opname->status,
            'note' => 'Informasi draft diperbarui.',
            'performed_by' => Auth::id(),
            'performed_at' => now(),
        ]);

        return response()->json(['message' => 'Draft stock opname berhasil diperbarui.']);
    }

    public function show(int $id)
    {
        $opname = $this->findAccessible($id);
        $relations = [
            'branch', 'rack', 'creator', 'starter', 'submitter', 'verifier', 'approver', 'adjuster',
            'details.counter', 'details.rack',
        ];

        if ($this->canViewMovements($opname)) {
            $relations[] = 'movements.detail';
            $relations[] = 'movements.kartuStok.createdBy';
            $relations[] = 'movements.kartuStok.obat';
        }

        if ($this->canReview($opname)) {
            $relations[] = 'logs.performer';
        }

        $opname->load($relations);

        return response()->json(['opname' => $this->detailPayload($opname)]);
    }

    public function printCountSheet(int $id)
    {
        $opname = $this->findAccessible($id);

        abort_unless(
            in_array((int) $opname->branch_id, BranchAccess::assignedUserBranchIds(), true)
                || $this->canReview($opname),
            403
        );

        if ($opname->status === StockOpnameModel::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Mulai penghitungan terlebih dahulu agar lembar opname dapat dicetak.']);
        }

        $opname->load(['branch', 'rack', 'details.rack']);

        return view('medcare.menu.stok.stockOpname.print', compact('opname'));
    }

    public function downloadReport(int $id, StockOpnameReportPdf $reportPdf)
    {
        $opname = $this->findAccessible($id);

        abort_unless(
            $this->canReview($opname),
            403,
            'Laporan lengkap hanya dapat diakses oleh validator atau penyetuju stock opname.'
        );

        if ($opname->submitted_at === null) {
            throw ValidationException::withMessages([
                'status' => 'Laporan lengkap tersedia setelah hasil blind count disubmit.',
            ]);
        }

        $opname->load([
            'branch.apotekProfile', 'rack', 'creator', 'starter', 'submitter', 'verifier', 'approver', 'adjuster',
            'details.counter', 'details.rack',
            'movements.detail', 'movements.kartuStok.createdBy', 'movements.kartuStok.obat',
            'logs.performer',
        ]);
        $opname->setRelation('details', $opname->details
            ->sortBy(fn ($detail) => implode('|', [
                $detail->rack?->kode,
                $detail->nama_obat,
                optional($detail->expired_date)->format('Y-m-d'),
                $detail->no_batch,
            ]), SORT_NATURAL | SORT_FLAG_CASE)
            ->values());
        $opname->setRelation('movements', $opname->movements->sortBy('occurred_at')->values());
        $opname->setRelation('logs', $opname->logs->sortBy('performed_at')->values());
        $summary = $opname->posting_summary ?: $this->service->summary($opname);
        $pdf = $reportPdf->render($opname, $summary);
        $filename = 'laporan-stock-opname-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $opname->nomor).'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pdf),
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function destroy(int $id)
    {
        $opname = $this->findAccessible($id);
        $this->requireOpnameAuthorization((int) $opname->branch_id);

        if ($opname->status !== StockOpnameModel::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Hanya draft yang dapat dihapus.']);
        }

        $opname->delete();

        return response()->json(['message' => 'Draft stock opname berhasil dihapus.']);
    }

    public function start(int $id)
    {
        $opname = $this->service->start($id);

        return response()->json(['message' => 'Penghitungan dimulai dan snapshot stok berhasil dibuat.', 'opname' => $this->headerPayload($opname)]);
    }

    public function saveCounts(Request $request, int $id)
    {
        $validated = $request->validate([
            'details' => ['required', 'array', 'min:1'],
            'details.*.id' => ['required', 'integer'],
            'details.*.stok_fisik' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ]);

        $opname = $this->service->saveCounts($id, $validated['details']);

        return response()->json(['message' => 'Hasil hitung fisik berhasil disimpan.', 'opname' => $this->headerPayload($opname)]);
    }

    public function submit(int $id)
    {
        $opname = $this->service->submit($id);

        return response()->json(['message' => 'Stok fisik berhasil disubmit. Perbandingan dibuka dan operasional stok/POS kembali aktif.', 'opname' => $this->headerPayload($opname)]);
    }

    public function saveReasons(Request $request, int $id)
    {
        $validated = $request->validate([
            'details' => ['required', 'array', 'min:1'],
            'details.*.id' => ['required', 'integer'],
            'details.*.alasan_selisih' => ['nullable', 'string', 'max:1000'],
        ]);
        $opname = $this->service->saveReasons($id, $validated['details']);

        return response()->json(['message' => 'Alasan selisih berhasil disimpan.', 'opname' => $this->headerPayload($opname)]);
    }

    public function verify(Request $request, int $id)
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $opname = $this->service->verify($id, $validated['note'] ?? null);

        return response()->json(['message' => 'Stock opname berhasil divalidasi dan direkonsiliasi dengan transaksi berjalan.', 'opname' => $this->headerPayload($opname)]);
    }

    public function approve(Request $request, int $id)
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);
        $opname = $this->service->approve($id, $validated['note'] ?? null);

        return response()->json(['message' => 'Stock opname berhasil disetujui.', 'opname' => $this->headerPayload($opname)]);
    }

    public function reject(Request $request, int $id)
    {
        $validated = $request->validate(['note' => ['required', 'string', 'max:1000']]);
        $opname = $this->service->reject($id, $validated['note']);

        return response()->json(['message' => 'Stock opname dikembalikan ke proses penghitungan.', 'opname' => $this->headerPayload($opname)]);
    }

    public function adjust(int $id)
    {
        $opname = $this->service->adjust($id);

        return response()->json([
            'message' => 'Selisih berhasil diposting ke kartu stok.',
            'opname' => $this->headerPayload($opname),
            'summary' => $opname->posting_summary,
        ]);
    }

    private function validateHeader(Request $request): array
    {
        $branchIds = BranchAccess::approvalBranchIds();

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', Rule::in($branchIds), 'exists:branches,id'],
            'rak_id' => ['nullable', 'integer', 'exists:rak_penyimpanans,id'],
            'tanggal_opname' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['transaction_mode'] = StockOpnameModel::MODE_FREEZE;

        return $validated;
    }

    private function findAccessible(int $id): StockOpnameModel
    {
        return StockOpnameModel::query()
            ->whereIn('branch_id', BranchAccess::userBranchIds())
            ->findOrFail($id);
    }

    private function generateNumber(int $branchId, string $date): string
    {
        $branch = BranchModel::query()->lockForUpdate()->findOrFail($branchId);
        $prefix = 'SO-'.strtoupper(preg_replace('/[^A-Z0-9]/i', '', $branch->code ?: (string) $branch->id)).'-'.date('Ymd', strtotime($date));
        $sequence = StockOpnameModel::query()->where('nomor', 'like', $prefix.'-%')->count() + 1;

        do {
            $number = $prefix.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        } while (StockOpnameModel::query()->where('nomor', $number)->exists());

        return $number;
    }

    private function headerPayload(StockOpnameModel $opname): array
    {
        $opname->loadMissing(['branch', 'rack', 'creator', 'verifier', 'approver']);
        $labels = StockOpnameModel::statusLabels();
        $canManage = $this->roleSettings->canApproveBranch(Auth::user(), (int) $opname->branch_id);
        $canValidate = $this->roleSettings->canValidateStockOpnameBranch(Auth::user(), (int) $opname->branch_id);
        $canReview = $canManage || $canValidate;
        $canCount = in_array((int) $opname->branch_id, BranchAccess::assignedUserBranchIds(), true);
        $differenceCount = (int) ($opname->difference_count ?? $opname->details()->whereRaw('ABS(COALESCE(selisih_validasi, selisih, 0)) >= 0.005')->count());
        $comparisonIsVisible = $opname->submitted_at !== null;

        return [
            'id' => $opname->id,
            'nomor' => $opname->nomor,
            'tanggal_opname' => optional($opname->tanggal_opname)->format('Y-m-d'),
            'branch_id' => $opname->branch_id,
            'branch' => $opname->branch?->name ?: '-',
            'rak_id' => $opname->rak_id,
            'lokasi' => $opname->rack ? $opname->rack->kode.' - '.$opname->rack->nama : 'Semua Rak',
            'status' => $opname->status,
            'status_label' => $labels[$opname->status] ?? $opname->status,
            'transaction_mode' => $opname->transaction_mode,
            'transaction_mode_label' => $opname->status === StockOpnameModel::STATUS_COUNTING ? 'Stok & POS dikunci' : 'Operasional aktif',
            'catatan' => $opname->catatan,
            'created_by' => $opname->creator?->name ?: '-',
            'verified_by' => $opname->verifier?->name,
            'approved_by' => $opname->approver?->name,
            'details_count' => (int) ($opname->details_count ?? $opname->details()->count()),
            'counted_count' => (int) ($opname->counted_count ?? $opname->details()->whereNotNull('counted_at')->count()),
            'difference_count' => $comparisonIsVisible ? $differenceCount : null,
            'movements_count' => (int) ($opname->movements_count ?? $opname->movements()->count()),
            'can_manage' => $canManage,
            'can_review' => $canReview,
            'can_view_movements' => $this->canViewMovements($opname),
            'can_count' => $canCount && $opname->status === StockOpnameModel::STATUS_COUNTING,
            'can_submit' => $canCount && $opname->status === StockOpnameModel::STATUS_COUNTING,
            'can_explain' => $canCount && $opname->status === StockOpnameModel::STATUS_AWAITING_VERIFICATION,
            'comparison_visible' => $comparisonIsVisible,
            'can_verify' => $canCount && $opname->status === StockOpnameModel::STATUS_AWAITING_VERIFICATION,
            'can_reject' => ($canValidate && $opname->status === StockOpnameModel::STATUS_AWAITING_VERIFICATION)
                || ($canManage && $opname->status === StockOpnameModel::STATUS_AWAITING_APPROVAL),
            'can_approve' => $canManage && $opname->status === StockOpnameModel::STATUS_AWAITING_APPROVAL,
            'can_adjust' => $canManage && $opname->status === StockOpnameModel::STATUS_APPROVED,
            'report_url' => $comparisonIsVisible && $canReview ? route('stockOpname.report', $opname->id) : null,
        ];
    }

    private function requireOpnameAuthorization(?int $branchId = null): void
    {
        $authorized = $branchId === null
            ? $this->roleSettings->userIsApprover(Auth::user())
            : $this->roleSettings->canApproveBranch(Auth::user(), $branchId);

        abort_unless($authorized, 403, 'Hanya admin atau apoteker yang berwenang mengatur stock opname.');
    }

    private function detailPayload(StockOpnameModel $opname): array
    {
        $header = $this->headerPayload($opname);
        $header['verification_note'] = $opname->verification_note;
        $header['approval_note'] = $opname->approval_note;
        $header['started_at'] = optional($opname->started_at)->format('Y-m-d H:i:s');
        $header['submitted_at'] = optional($opname->submitted_at)->format('Y-m-d H:i:s');
        $header['verified_at'] = optional($opname->verified_at)->format('Y-m-d H:i:s');
        $header['approved_at'] = optional($opname->approved_at)->format('Y-m-d H:i:s');
        $header['adjusted_at'] = optional($opname->adjusted_at)->format('Y-m-d H:i:s');
        $header['started_by'] = $opname->starter?->name;
        $header['submitted_by'] = $opname->submitter?->name;
        $header['adjusted_by'] = $opname->adjuster?->name;
        $header['print_url'] = route('stockOpname.print', $opname->id);
        $header['details'] = $opname->details->map(function ($detail) use ($header) {
            $payload = [
                'id' => $detail->id,
                'stok_batch_id' => $detail->stok_batch_id,
                'has_batch' => $detail->stok_batch_id !== null,
                'kode_obat' => $detail->kode_obat,
                'nama_obat' => $detail->nama_obat,
                'satuan' => $detail->satuan,
                'rak' => $detail->rack ? $detail->rack->kode.' - '.$detail->rack->nama : '-',
                'no_batch' => $detail->no_batch,
                'expired_date' => optional($detail->expired_date)->format('Y-m-d'),
                'stok_fisik' => $detail->stok_fisik !== null ? (float) $detail->stok_fisik : null,
                'counted_by' => $detail->counter?->name,
                'counted_at' => optional($detail->counted_at)->format('Y-m-d H:i:s'),
            ];

            if ($header['comparison_visible']) {
                $payload += [
                    'stok_sistem' => (float) $detail->stok_sistem_hitung,
                    'selisih' => (float) $detail->selisih,
                    'alasan_selisih' => $detail->alasan_selisih,
                    'mutasi_masuk' => (float) $detail->mutasi_masuk,
                    'mutasi_keluar' => (float) $detail->mutasi_keluar,
                    'stok_sistem_validasi' => $detail->stok_sistem_validasi !== null ? (float) $detail->stok_sistem_validasi : null,
                    'stok_target_validasi' => $detail->stok_target_validasi !== null ? (float) $detail->stok_target_validasi : null,
                    'selisih_validasi' => $detail->selisih_validasi !== null ? (float) $detail->selisih_validasi : null,
                ];

                if ($header['can_review']) {
                    $payload['hpp'] = (float) $detail->hpp;
                    $payload['nilai_selisih'] = round((float) ($detail->selisih_validasi ?? $detail->selisih) * (float) $detail->hpp, 2);
                }
            }

            return $payload;
        })->values();
        $header['summary'] = $header['comparison_visible'] && $header['can_review']
            ? ($opname->posting_summary ?: $this->service->summary($opname))
            : null;
        $header['movements'] = $header['can_view_movements'] ? $opname->movements->map(fn ($movement) => [
            'id' => $movement->id,
            'occurred_at' => optional($movement->occurred_at)->format('Y-m-d H:i:s'),
            'kode_obat' => $movement->detail?->kode_obat ?: $movement->kartuStok?->obat?->kode_obat,
            'nama_obat' => $movement->detail?->nama_obat ?: ($movement->kartuStok?->obat?->nama_obat ?: 'Obat tidak ditemukan'),
            'satuan' => $movement->detail?->satuan,
            'no_batch' => $movement->detail?->no_batch ?: ($movement->kartuStok?->no_batch ?: '-'),
            'expired_date' => optional($movement->detail?->expired_date ?? $movement->kartuStok?->expired_date)->format('Y-m-d'),
            'jenis_mutasi' => $movement->jenis_mutasi,
            'qty_masuk' => (float) $movement->qty_masuk,
            'qty_keluar' => (float) $movement->qty_keluar,
            'reference' => $movement->kartuStok?->nomor_referensi,
            'user' => $movement->kartuStok?->createdBy?->name,
        ])->values() : collect();
        $header['logs'] = $header['can_review'] ? $opname->logs->sortByDesc('performed_at')->values()->map(fn ($log) => [
            'action' => $log->action,
            'from_status' => $log->from_status,
            'to_status' => $log->to_status,
            'note' => $log->note,
            'performed_by' => $log->performer?->name,
            'performed_at' => optional($log->performed_at)->format('Y-m-d H:i:s'),
        ]) : collect();

        return $header;
    }

    private function canReview(StockOpnameModel $opname): bool
    {
        return $this->roleSettings->canApproveBranch(Auth::user(), (int) $opname->branch_id)
            || $this->roleSettings->canValidateStockOpnameBranch(Auth::user(), (int) $opname->branch_id);
    }

    private function canViewMovements(StockOpnameModel $opname): bool
    {
        return in_array((int) $opname->branch_id, BranchAccess::assignedUserBranchIds(), true)
            || $this->canReview($opname);
    }
}
