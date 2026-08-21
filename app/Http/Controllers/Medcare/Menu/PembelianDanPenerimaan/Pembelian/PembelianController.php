<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Services\Menu\PembelianPenerimaan\PembelianService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananNarkotikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananOotService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPrekursorService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPsikotropikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananRegulerService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Services\Settings\Master\DistributorService;
use App\Services\Settings\Master\MasterObatService;
use App\Support\BranchAccess;
use App\Support\TieredDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PembelianController extends Controller
{
    protected $PembelianService;

    protected $DistributorService;

    protected $MasterObatService;

    public function __construct(
        PembelianService $PembelianService,
        DistributorService $DistributorService,
        MasterObatService $MasterObatService,
        private readonly TransactionNotificationService $transactionNotifications,
        private readonly SuratPesananNarkotikaService $suratPesananNarkotika,
        private readonly SuratPesananPsikotropikaService $suratPesananPsikotropika,
        private readonly SuratPesananPrekursorService $suratPesananPrekursor,
        private readonly SuratPesananOotService $suratPesananOot,
        private readonly SuratPesananRegulerService $suratPesananReguler,
    ) {
        $this->PembelianService = $PembelianService;
        $this->DistributorService = $DistributorService;
        $this->MasterObatService = $MasterObatService;
    }

    /**
     * Display a listing of the resource.
     */
    public function pembelian()
    {
        return view('medcare.menu.pembelianPenerimaan.pembelian.pembelian');
    }

    public function table(Request $request)
    {
        $branchIds = BranchAccess::userBranchIds();
        $Pembelian = $this->PembelianService->getPembelianTable($branchIds);
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        if ($dateStart || $dateEnd) {
            $Pembelian = collect($Pembelian)->filter(function ($row) use ($dateStart, $dateEnd) {
                if (empty($row['tanggal_po']) || $row['tanggal_po'] === '-') {
                    return false;
                }

                $purchaseDate = Carbon::parse($row['tanggal_po'])->startOfDay();

                if ($dateStart && $purchaseDate->lt(Carbon::parse($dateStart)->startOfDay())) {
                    return false;
                }

                if ($dateEnd && $purchaseDate->gt(Carbon::parse($dateEnd)->endOfDay())) {
                    return false;
                }

                return true;
            })->values()->all();
        }

        $summary = collect($Pembelian);
        $draftCount = $summary->where('status', 'draft')->count();
        $waitingApprovalCount = $summary->where('status', 'waiting_approval')->count();
        $summaryData = [
            'total' => $summary->count(),
            'draft' => $draftCount,
            'waiting_approval' => $waitingApprovalCount,
            'pending' => $draftCount + $waitingApprovalCount,
            'approved' => $summary->where('status', 'approved')->count(),
            'rejected' => $summary->where('status', 'rejected')->count(),
            'total_estimasi' => $summary->sum(function ($row) {
                return (float) ($row['total_estimasi'] ?? 0);
            }),
        ];
        $approvalUser = Auth::user();

        return DataTables::of($Pembelian)
            ->addIndexColumn()
            ->addColumn('actions', function ($dataPembelian) use ($approvalUser) {
                $status = $dataPembelian['status'];
                $canApprove = $this->transactionNotifications->canApproveBranch(
                    $approvalUser,
                    isset($dataPembelian['branch_key']) ? (int) $dataPembelian['branch_key'] : null
                );
                $approvalButton = $canApprove && in_array($status, ['draft', 'waiting_approval'], true)
                    ? '<button class="btn btn-sm btn-success btn-approve-pembelian" onclick="approvePembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-check-circle"></i>
                </button>'
                    : '';
                $rejectButton = $canApprove && in_array($status, ['draft', 'waiting_approval'], true)
                    ? '<button class="btn btn-sm btn-warning btn-reject-pembelian" onclick="rejectPembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-close-circle"></i>
                </button>'
                    : '';
                $reopenButton = $canApprove && in_array($status, ['approved', 'rejected'], true)
                    ? '<button class="btn btn-sm btn-secondary btn-reopen-pembelian" onclick="reopenPembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-lock-open-variant"></i>
                </button>'
                    : '';
                $editButton = ! in_array($status, ['approved', 'diterima_sebagian', 'selesai'], true)
                    ? '<button class="btn btn-sm btn-success btn-edit-pembelian" onclick="editPembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-pencil"></i>
                </button>'
                    : '';

                return '
                '.$approvalButton.'
                '.$rejectButton.'
                '.$reopenButton.'
                '.$editButton.'
                <button class="btn btn-sm btn-info" onclick="lihatPembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-eye"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deletePembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-delete"></i>
                </button>
            ';
            })
            ->rawColumns(['actions'])
            ->with(['summary' => $summaryData])
            ->make(true);
    }

    public function generateNoPO()
    {
        $Pembelian = $this->PembelianService->generatePo();

        return response()->json($Pembelian);
    }

    public function getDistributor()
    {
        $distributor = $this->DistributorService->getDistributor();

        return response()->json($distributor);
    }

    public function getObat()
    {
        $obat = $this->MasterObatService->getMasterObat();

        return response()->json($obat);
    }

    public function getKonversiSatuan(Request $request)
    {
        $KonversiSatuan = $this->PembelianService->getKonversiSatuan($request->obat_id);

        // Log::info($KonversiSatuan);
        return response()->json($KonversiSatuan);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_po' => 'required|string|max:50|unique:purchase_orders,no_po',
            'distributor_id' => 'required|integer',
            'tanggal' => 'required|string',
            'catatan' => 'nullable|string',
            'total_estimasi' => 'required|numeric',

            'obat_id.*' => 'required|integer',
            'qty.*' => 'required|numeric|min:1',
            'harga_estimasi.*' => 'required|numeric|min:0',
            'diskon_1' => 'required|array',
            'diskon_1.*' => 'nullable|numeric|min:0|max:100',
            'diskon_2' => 'required|array',
            'diskon_2.*' => 'nullable|numeric|min:0|max:100',
            'diskon_3' => 'required|array',
            'diskon_3.*' => 'nullable|numeric|min:0|max:100',
            'subtotal.*' => 'required|numeric|min:0',
            'satuan_id.*' => 'required|integer|min:1',
            'is_oot' => 'nullable|array',
            'is_oot.*' => 'boolean',
        ]);

        $detailRows = $this->purchaseDetailRows($request);
        $totalEstimasi = round(collect($detailRows)->sum('subtotal'), 2);

        DB::beginTransaction();

        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $branchId = BranchAccess::requireUserBranchId($user);
            $isApprover = $this->transactionNotifications->isApprovalRole($user);
            Log::info('User Role: '.($isApprover ? 'Approver' : 'Non-Approver'));

            // Insert header
            $po = $this->PembelianService->createPembelian([
                'no_po' => $request->no_po,
                'distributor_id' => $request->distributor_id,
                'branch_id' => $branchId,
                'tanggal_po' => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $totalEstimasi,
                'catatan' => $request->catatan,
                'created_by' => $user->id,

                'approved_by' => $isApprover ? $user->id : null,
                'status' => $isApprover ? 'approved' : 'waiting_approval',
            ]);

            // Insert detail
            foreach ($detailRows as $detailRow) {
                $this->PembelianService->createPembelianDetail([
                    'purchase_order_id' => $po->id,
                    ...$detailRow,
                ]);
            }

            DB::commit();

            // ======================
            // 🔔 KIRIM NOTIFIKASI
            // ======================
            $this->transactionNotifications->notifyApprovalRequest('pembelian', $po, $user);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembelian berhasil ditambahkan',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Pembelian = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $ootDetails = $this->suratPesananOot->ootDetails($Pembelian);

        if ($ootDetails->isNotEmpty()) {
            $Pembelian->setAttribute('has_regular_items', false);
            $Pembelian->setAttribute('regular_item_count', 0);
            $Pembelian->setAttribute('has_narcotic_items', false);
            $Pembelian->setAttribute('narcotic_item_count', 0);
            $Pembelian->setAttribute('has_psychotropic_items', false);
            $Pembelian->setAttribute('psychotropic_item_count', 0);
            $Pembelian->setAttribute('has_precursor_items', false);
            $Pembelian->setAttribute('precursor_item_count', 0);
        } else {
            $regularDetails = $this->suratPesananReguler->regularDetails($Pembelian);
            $narcoticDetails = $this->suratPesananNarkotika->narcoticDetails($Pembelian);
            $psychotropicDetails = $this->suratPesananPsikotropika->psychotropicDetails($Pembelian);
            $precursorDetails = $this->suratPesananPrekursor->precursorDetails($Pembelian);

            $Pembelian->setAttribute('has_regular_items', $regularDetails->isNotEmpty());
            $Pembelian->setAttribute('regular_item_count', $regularDetails->count());
            $Pembelian->setAttribute('has_narcotic_items', $narcoticDetails->isNotEmpty());
            $Pembelian->setAttribute('narcotic_item_count', $narcoticDetails->count());
            $Pembelian->setAttribute('has_psychotropic_items', $psychotropicDetails->isNotEmpty());
            $Pembelian->setAttribute('psychotropic_item_count', $psychotropicDetails->count());
            $Pembelian->setAttribute('has_precursor_items', $precursorDetails->isNotEmpty());
            $Pembelian->setAttribute('precursor_item_count', $precursorDetails->count());
        }

        $Pembelian->setAttribute('has_oot_items', $ootDetails->isNotEmpty());
        $Pembelian->setAttribute('oot_item_count', $ootDetails->count());

        return response()->json($Pembelian);
    }

    public function suratPesananNarkotika(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $this->abortIfOotPurchaseOrder($purchaseOrder);
        $narcoticDetails = $this->suratPesananNarkotika->narcoticDetails($purchaseOrder);

        abort_if(
            $narcoticDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat dengan klasifikasi Narkotika.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananNarkotika', [
            'purchaseOrder' => $purchaseOrder,
            'narcoticDetails' => $narcoticDetails,
            'copyCount' => SuratPesananNarkotikaService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function suratPesananPsikotropika(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $this->abortIfOotPurchaseOrder($purchaseOrder);
        $psychotropicDetails = $this->suratPesananPsikotropika->psychotropicDetails($purchaseOrder);

        abort_if(
            $psychotropicDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat dengan klasifikasi Psikotropika.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananPsikotropika', [
            'purchaseOrder' => $purchaseOrder,
            'psychotropicDetails' => $psychotropicDetails,
            'copyCount' => SuratPesananPsikotropikaService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function suratPesananPrekursor(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $this->abortIfOotPurchaseOrder($purchaseOrder);
        $precursorDetails = $this->suratPesananPrekursor->precursorDetails($purchaseOrder);

        abort_if(
            $precursorDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat dengan klasifikasi Prekursor.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananPrekursor', [
            'purchaseOrder' => $purchaseOrder,
            'precursorDetails' => $precursorDetails,
            'copyCount' => SuratPesananPrekursorService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function suratPesananReguler(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $this->abortIfOotPurchaseOrder($purchaseOrder);
        $regularDetails = $this->suratPesananReguler->regularDetails($purchaseOrder);

        abort_if(
            $regularDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat reguler di luar klasifikasi Narkotika, Psikotropika, dan Prekursor.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananReguler', [
            'purchaseOrder' => $purchaseOrder,
            'regularDetails' => $regularDetails,
            'copyCount' => SuratPesananRegulerService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function suratPesananOot(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $ootDetails = $this->suratPesananOot->ootDetails($purchaseOrder);

        abort_if(
            $ootDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat yang ditandai OOT oleh apoteker.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananOot', [
            'purchaseOrder' => $purchaseOrder,
            'ootDetails' => $ootDetails,
            'copyCount' => SuratPesananOotService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    private function abortIfOotPurchaseOrder(PembelianModel $purchaseOrder): void
    {
        abort_if(
            $this->suratPesananOot->ootDetails($purchaseOrder)->isNotEmpty(),
            422,
            'Purchase order yang ditandai OOT hanya dapat menghasilkan Surat Pesanan OOT.'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Pembelian = $this->PembelianService->findByIdPembelian($id, BranchAccess::userBranchIds());

        if (! $Pembelian) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembelian tidak ditemukan untuk branch user.',
            ], 404);
        }

        if ($Pembelian && $Pembelian->status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pembelian yang sudah disetujui tidak bisa diedit. Buka approval terlebih dahulu.',
            ], 422);
        }

        return response()->json($Pembelian);
    }

    public function approve(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->transactionNotifications->isApprovalRole($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk pembelian.',
            ], 403);
        }

        $branchIds = BranchAccess::approvalBranchIds($user);
        $po = $this->PembelianService->findByIdPembelian($id, $branchIds);

        if (! $po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase order tidak ditemukan.',
            ], 404);
        }

        if ($po->status === 'approved') {
            return response()->json([
                'status' => 'info',
                'message' => 'Purchase order sudah disetujui.',
            ]);
        }

        if ($po->status === 'rejected') {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase order yang ditolak tidak dapat disetujui.',
            ], 422);
        }

        $updatedPo = $this->PembelianService->updateStatus($id, 'approved', $user->id, $branchIds);
        $this->transactionNotifications->notifyActionResult('pembelian', $updatedPo, 'approved', $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembelian berhasil disetujui.',
        ]);
    }

    public function reject(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->transactionNotifications->isApprovalRole($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk pembelian.',
            ], 403);
        }

        $branchIds = BranchAccess::approvalBranchIds($user);
        $po = $this->PembelianService->findByIdPembelian($id, $branchIds);

        if (! $po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase order tidak ditemukan.',
            ], 404);
        }

        if ($po->status === 'approved') {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase order yang sudah disetujui harus dibuka approval-nya dulu.',
            ], 422);
        }

        if ($po->status === 'rejected') {
            return response()->json([
                'status' => 'info',
                'message' => 'Purchase order sudah ditolak.',
            ]);
        }

        $updatedPo = $this->PembelianService->updateStatus($id, 'rejected', null, $branchIds);
        $this->transactionNotifications->notifyActionResult('pembelian', $updatedPo, 'rejected', $user);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembelian berhasil ditolak.',
        ]);
    }

    public function reopenApproval(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $this->transactionNotifications->isApprovalRole($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role Anda tidak memiliki akses approval untuk pembelian.',
            ], 403);
        }

        $branchIds = BranchAccess::approvalBranchIds($user);
        $po = $this->PembelianService->findByIdPembelian($id, $branchIds);

        if (! $po) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase order tidak ditemukan.',
            ], 404);
        }

        if (in_array($po->status, ['draft', 'waiting_approval'], true)) {
            return response()->json([
                'status' => 'info',
                'message' => 'Approval purchase order sudah terbuka.',
            ]);
        }

        $this->PembelianService->updateStatus($id, 'waiting_approval', null, $branchIds);

        return response()->json([
            'status' => 'success',
            'message' => 'Approval pembelian berhasil dibuka.',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'no_po' => 'required|string|max:50|unique:purchase_orders,no_po,'.$id,
            'distributor_id' => 'required|integer',
            'tanggal' => 'required|string',
            'catatan' => 'nullable|string',
            'total_estimasi' => 'required|numeric',

            'obat_id.*' => 'required|integer',
            'qty.*' => 'required|numeric|min:1',
            'harga_estimasi.*' => 'required|numeric|min:0',
            'diskon_1' => 'required|array',
            'diskon_1.*' => 'nullable|numeric|min:0|max:100',
            'diskon_2' => 'required|array',
            'diskon_2.*' => 'nullable|numeric|min:0|max:100',
            'diskon_3' => 'required|array',
            'diskon_3.*' => 'nullable|numeric|min:0|max:100',
            'subtotal.*' => 'required|numeric|min:0',
            'satuan_id.*' => 'required|integer|min:1',
            'is_oot' => 'nullable|array',
            'is_oot.*' => 'boolean',
        ]);

        $detailRows = $this->purchaseDetailRows($request);
        $totalEstimasi = round(collect($detailRows)->sum('subtotal'), 2);

        DB::beginTransaction();

        try {

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $branchIds = BranchAccess::userBranchIds($user);
            $existingPo = $this->PembelianService->findByIdPembelian($id, $branchIds);

            if (! $existingPo) {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembelian tidak ditemukan untuk branch user.',
                ], 404);
            }

            if ($existingPo && $existingPo->status === 'approved') {
                DB::rollBack();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembelian yang sudah disetujui tidak bisa diedit. Buka approval terlebih dahulu.',
                ], 422);
            }

            // =========================== UPDATE HEADER ============================
            $po = $this->PembelianService->updatePembelian($id, [
                'no_po' => $request->no_po,
                'distributor_id' => $request->distributor_id,
                'branch_id' => $existingPo->branch_id,
                'tanggal_po' => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $totalEstimasi,
                'catatan' => $request->catatan,
                'approved_by' => null,
                'status' => 'waiting_approval',
            ], $branchIds);

            // =========================== RESET DETAIL ============================
            // Hapus semua detail PO lama
            $this->PembelianService->deletePembelianDetail($id);

            // =========================== INSERT DETAIL BARU =======================
            foreach ($detailRows as $detailRow) {
                $this->PembelianService->createPembelianDetail([
                    'purchase_order_id' => $id,
                    ...$detailRow,
                ]);
            }

            DB::commit();

            $this->transactionNotifications->notifyApprovalRequest('pembelian', $po, $user);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembelian berhasil diperbarui',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deleted = $this->PembelianService->deletePembelian($id, BranchAccess::userBranchIds());

            if (! $deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'PO tidak ditemukan untuk branch user.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'PO berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return array<int, array<string, bool|int|float>>
     */
    private function purchaseDetailRows(Request $request): array
    {
        $rows = [];

        foreach ($request->input('obat_id', []) as $index => $obatId) {
            $qty = (float) $request->input('qty.'.$index, 0);
            $price = (float) $request->input('harga_estimasi.'.$index, 0);
            [$discount1, $discount2, $discount3] = TieredDiscount::percentages(
                $request->input('diskon_1.'.$index, 0),
                $request->input('diskon_2.'.$index, 0),
                $request->input('diskon_3.'.$index, 0)
            );

            $rows[] = [
                'obat_id' => (int) $obatId,
                'qty' => $qty,
                'harga_estimasi' => $price,
                'diskon_1' => $discount1,
                'diskon_2' => $discount2,
                'diskon_3' => $discount3,
                'subtotal' => TieredDiscount::netAmount(
                    $qty * $price,
                    $discount1,
                    $discount2,
                    $discount3
                ),
                'satuan_konversi' => (int) $request->input('satuan_id.'.$index),
                'is_oot' => $request->boolean('is_oot.'.$index),
            ];
        }

        return $rows;
    }
}
