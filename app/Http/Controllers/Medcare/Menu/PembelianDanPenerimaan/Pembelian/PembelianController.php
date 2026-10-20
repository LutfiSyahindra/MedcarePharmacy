<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Services\Menu\PembelianPenerimaan\PembelianService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananNarkotikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananOotService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPrekursorService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPsikotropikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananRegulerService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Services\Settings\Master\DistributorService;
use App\Services\Settings\Master\KonversiSatuanObatService;
use App\Support\BranchAccess;
use App\Support\TieredDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PembelianController extends Controller
{
    protected $PembelianService;

    protected $DistributorService;

    public function __construct(
        PembelianService $PembelianService,
        DistributorService $DistributorService,
        private readonly TransactionNotificationService $transactionNotifications,
        private readonly SuratPesananNarkotikaService $suratPesananNarkotika,
        private readonly SuratPesananPsikotropikaService $suratPesananPsikotropika,
        private readonly SuratPesananPrekursorService $suratPesananPrekursor,
        private readonly SuratPesananOotService $suratPesananOot,
        private readonly SuratPesananRegulerService $suratPesananReguler,
    ) {
        $this->PembelianService = $PembelianService;
        $this->DistributorService = $DistributorService;
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
        $validated = $request->validate([
            'medicine_search' => ['nullable', 'string', 'max:150'],
            'medicine_id' => ['nullable', 'integer', 'exists:master_obats,id'],
        ]);
        $branchIds = BranchAccess::userBranchIds();
        $Pembelian = $this->PembelianService->getPembelianTable(
            $branchIds,
            $validated['medicine_search'] ?? null,
            isset($validated['medicine_id']) ? (int) $validated['medicine_id'] : null
        );
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
        $statusColumn = collect($request->input('columns', []))->firstWhere('data', 'status');
        $statusFilter = data_get($statusColumn, 'search.value', '');
        $estimateSummary = filled($statusFilter) ? $summary->where('status', $statusFilter) : $summary;
        $draftCount = $summary->where('status', 'draft')->count();
        $waitingApprovalCount = $summary->where('status', 'waiting_approval')->count();
        $summaryData = [
            'total' => $summary->count(),
            'draft' => $draftCount,
            'waiting_approval' => $waitingApprovalCount,
            'pending' => $draftCount + $waitingApprovalCount,
            'approved' => $summary->where('status', 'approved')->count(),
            'dalam_penerimaan' => $summary->where('status', 'dalam_penerimaan')->count(),
            'selesai' => $summary->where('status', 'selesai')->count(),
            'rejected' => $summary->where('status', 'rejected')->count(),
            'total_estimasi' => $estimateSummary->sum(function ($row) {
                return (float) ($row['total_estimasi'] ?? 0);
            }),
            'total_item' => $summary->sum(function ($row) {
                return (int) ($row['item_count'] ?? 0);
            }),
            'total_qty' => $summary->sum(function ($row) {
                return (float) ($row['total_qty'] ?? 0);
            }),
        ];
        $approvalUser = Auth::user();

        return DataTables::of($Pembelian)
            ->addIndexColumn()
            ->addColumn('actions', function ($dataPembelian) use ($approvalUser) {
                $status = $dataPembelian['purchase_order_status'];
                $canApprove = $this->transactionNotifications->canApproveBranch(
                    $approvalUser,
                    isset($dataPembelian['branch_key']) ? (int) $dataPembelian['branch_key'] : null
                );
                $approvalButton = $canApprove && $status === 'waiting_approval'
                    ? '<button class="btn btn-sm btn-success btn-approve-pembelian" onclick="approvePembelian('.$dataPembelian['id'].')">
                    <i class="mdi mdi-check-circle"></i>
                </button>'
                    : '';
                $rejectButton = $canApprove && $status === 'waiting_approval'
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
        $branchId = BranchAccess::userBranchId();
        $obat = MasterObatModel::query()
            ->select(['id', 'kode_obat', 'nama_obat', 'harga_beli', 'satuan_id'])
            ->with('satuan:id,nama')
            ->withSum([
                'stokBatches as stok_saat_ini' => function ($query) use ($branchId) {
                    $query->where('qty', '>', 0)
                        ->when(
                            $branchId,
                            fn ($stockQuery) => $stockQuery->where('branch_id', $branchId),
                            fn ($stockQuery) => $stockQuery->whereRaw('1 = 0')
                        );
                },
            ], 'qty')
            ->orderBy('nama_obat')
            ->get()
            ->each(function (MasterObatModel $medicine) {
                $medicine->setAttribute('stok_saat_ini', (float) ($medicine->stok_saat_ini ?? 0));
            });

        return response()->json($obat);
    }

    public function getKonversiSatuan(Request $request)
    {
        $conversionQuery = KonversiSatuanModel::query()
            ->select(['id', 'obat_id', 'satuan_id', 'konversi', 'is_default'])
            ->selectRaw('EXISTS (SELECT 1 FROM purchase_order_details WHERE purchase_order_details.satuan_konversi = obat_satuan_conversions.id) AS is_used')
            ->with('satuan:id,nama')
            ->orderByDesc('is_default')->orderBy('konversi')->orderBy('id');
        if ($request->has('obat_ids')) {
            $validated = $request->validate([
                'obat_ids' => ['required', 'array', 'max:250'],
                'obat_ids.*' => ['required', 'integer', 'distinct', 'exists:master_obats,id'],
            ]);
            $medicineIds = collect($validated['obat_ids'])
                ->map(fn ($medicineId) => (int) $medicineId)
                ->unique()
                ->values();
            $conversions = $conversionQuery
                ->whereIn('obat_id', $medicineIds)
                ->get()
                ->groupBy(fn (KonversiSatuanModel $conversion) => (string) $conversion->obat_id);

            return response()->json(
                $medicineIds->mapWithKeys(fn (int $medicineId) => [
                    (string) $medicineId => $conversions->get((string) $medicineId, collect())->values(),
                ])
            );
        }

        $validated = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:master_obats,id'],
        ]);
        $KonversiSatuan = $conversionQuery->where('obat_id', $validated['obat_id'])->get();

        return response()->json($KonversiSatuan);
    }

    public function getSatuan()
    {
        return response()->json(SatuansModel::query()
            ->select(['id', 'nama'])
            ->where('is_active', true)
            ->orderBy('nama')
            ->get());
    }

    public function storeKonversiSatuan(Request $request, KonversiSatuanObatService $conversionService)
    {
        $batch = $request->has('conversions');
        $validated = $request->validate([
            'obat_id' => ['required', 'integer', 'exists:master_obats,id'],
            ...($batch ? [
                'conversions' => ['required', 'array', 'list', 'min:1', 'max:20'],
                'conversions.*.id' => ['nullable', 'integer', 'distinct'],
                'conversions.*.satuan_id' => ['required', 'integer', 'distinct', 'exists:satuans,id'],
                'conversions.*.konversi' => ['required', 'integer', 'min:1', 'max:2147483647'],
                'selected_satuan_id' => ['nullable', 'integer'],
            ] : [
                'satuan_id' => ['required', 'integer', Rule::exists('satuans', 'id')->where('is_active', true)],
                'konversi' => ['required', 'integer', 'min:1', 'max:2147483647'],
            ]),
        ]);
        $rows = $batch ? collect($validated['conversions'])->sortKeys()->values()->all() : [$validated];
        $selectedUnitId = (int) ($validated['selected_satuan_id'] ?? $rows[0]['satuan_id']);
        if (! collect($rows)->contains(fn (array $row) => (int) $row['satuan_id'] === $selectedUnitId)) {
            throw ValidationException::withMessages(['selected_satuan_id' => 'Pilih salah satu satuan konversi untuk dipakai di PO.']);
        }

        $result = DB::transaction(function () use ($validated, $rows, $selectedUnitId, $batch, $conversionService) {
            $medicine = MasterObatModel::with('satuan')->lockForUpdate()->findOrFail($validated['obat_id']);

            if (! $medicine->satuan) {
                throw ValidationException::withMessages([
                    'obat_id' => 'Obat belum memiliki satuan stok. Lengkapi satuan stok obat terlebih dahulu.',
                ]);
            }

            $existing = KonversiSatuanModel::where('obat_id', $medicine->id)->get();
            $requestedById = collect($rows)->filter(fn (array $row) => isset($row['id']))->keyBy('id');
            $activeUnitIds = SatuansModel::where('is_active', true)->pluck('id');
            $errors = [];
            foreach ($rows as $index => $row) {
                $prefix = $batch ? 'conversions.'.$index.'.' : '';
                $conversion = isset($row['id']) ? $existing->firstWhere('id', (int) $row['id']) : null;
                if (isset($row['id']) && ! $conversion) {
                    $errors[$prefix.'id'] = 'Satuan konversi ini tidak tersedia untuk obat yang dipilih. Muat ulang pengaturan konversi.';
                }
                if (! $activeUnitIds->contains((int) $row['satuan_id']) && (int) ($conversion?->satuan_id ?? 0) !== (int) $row['satuan_id']) {
                    $errors[$prefix.'satuan_id'] = 'Pilih satuan beli yang masih aktif.';
                }
                if ((int) $medicine->satuan_id === (int) $row['satuan_id'] && (int) $row['konversi'] !== 1) {
                    $errors[$prefix.'konversi'] = 'Konversi untuk satuan yang sama dengan satuan stok harus bernilai 1.';
                }
                $sameUnit = $existing->firstWhere('satuan_id', (int) $row['satuan_id']);
                $replacement = $sameUnit ? $requestedById->get($sameUnit->id) : null;
                if ($replacement && (int) $replacement['satuan_id'] !== (int) $sameUnit->satuan_id) {
                    $sameUnit = null;
                }
                if ($sameUnit && $conversion && $sameUnit->id !== $conversion->id) {
                    $errors[$prefix.'satuan_id'] = 'Satuan beli ini sudah dipakai pada konversi lain. Pilih satuan yang berbeda.';
                } elseif ($sameUnit && ! $conversion && (int) $sameUnit->konversi !== (int) $row['konversi']) {
                    $errors[$prefix.'satuan_id'] = 'Satuan konversi ini sudah tersedia untuk obat tersebut. Pilih satuan yang sudah tersedia.';
                }
            }
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $created = 0;
            $updated = 0;
            foreach ($rows as $index => $row) {
                if (isset($row['id'])) {
                    $conversion = $existing->firstWhere('id', (int) $row['id']);
                    $changed = (int) $conversion->satuan_id !== (int) $row['satuan_id']
                        || (int) $conversion->konversi !== (int) $row['konversi'];
                    if ($changed) {
                        try {
                            $conversionService->updateKonversi($conversion->id, [
                                'satuan_id' => $row['satuan_id'],
                                'konversi' => $row['konversi'],
                            ]);
                        } catch (ValidationException $exception) {
                            throw ValidationException::withMessages([
                                'conversions.'.$index.'.konversi' => collect($exception->errors())->flatten()->all(),
                            ]);
                        }
                        $updated++;
                    }
                }
            }
            $currentUnitIds = KonversiSatuanModel::where('obat_id', $medicine->id)->pluck('satuan_id');
            foreach ($rows as $row) {
                if (isset($row['id']) || $currentUnitIds->contains((int) $row['satuan_id'])) {
                    continue;
                }
                $conversionService->createKonversi([
                    'obat_id' => $medicine->id,
                    'satuan_id' => $row['satuan_id'],
                    'konversi' => $row['konversi'],
                    'is_default' => $existing->isEmpty() && (int) $row['satuan_id'] === $selectedUnitId,
                ]);
                $currentUnitIds->push((int) $row['satuan_id']);
                $created++;
            }
            $conversions = KonversiSatuanModel::with('satuan:id,nama')->where('obat_id', $medicine->id)
                ->orderByDesc('is_default')->orderBy('konversi')->orderBy('id')->get();

            return ['conversions' => $conversions, 'selected' => $conversions->firstWhere('satuan_id', $selectedUnitId), 'created' => $created, 'updated' => $updated];
        }, 3);

        return response()->json([
            'status' => 'success',
            'data' => $batch ? $result['conversions'] : $result['selected'],
            'selected_id' => $result['selected']->id,
            'created_count' => $result['created'],
            'updated_count' => $result['updated'],
            'message' => 'Satuan konversi berhasil disimpan dan dapat langsung digunakan pada PO.',
        ], $result['created'] > 0 ? 201 : 200);
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
        $usesManualNumber = $this->validatePurchaseRequest($request);

        $saveAsDraft = $request->boolean('save_as_draft');
        $detailRows = $this->purchaseDetailRows($request);
        $biayaAsuransi = round((float) $request->input('biaya_asuransi', 0), 2);
        $biayaPengiriman = round((float) $request->input('biaya_pengiriman', 0), 2);
        $totalEstimasi = round(
            collect($detailRows)->sum('subtotal') + $biayaAsuransi + $biayaPengiriman,
            2
        );

        return DB::transaction(function () use ($request, $usesManualNumber, $saveAsDraft, $detailRows, $biayaAsuransi, $biayaPengiriman, $totalEstimasi) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $branchId = BranchAccess::requireUserBranchId($user);
            $isApprover = $this->transactionNotifications->isApprovalRole($user);
            Log::info('User Role: '.($isApprover ? 'Approver' : 'Non-Approver'));

            if ($usesManualNumber) {
                $this->PembelianService->validateManualPoNumber($request->no_po);
            }
            $number = $usesManualNumber ? $request->no_po : $this->PembelianService->reservePoNumber();

            // Insert header
            $po = $this->PembelianService->createPembelian([
                'no_po' => $number,
                'distributor_id' => $request->distributor_id,
                'branch_id' => $branchId,
                'tanggal_po' => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $totalEstimasi,
                'biaya_asuransi' => $biayaAsuransi,
                'biaya_pengiriman' => $biayaPengiriman,
                'catatan' => $request->catatan,
                'created_by' => $user->id,

                'approved_by' => ! $saveAsDraft && $isApprover ? $user->id : null,
                'status' => $saveAsDraft ? 'draft' : ($isApprover ? 'approved' : 'waiting_approval'),
            ]);

            // Insert detail
            foreach ($detailRows as $detailRow) {
                $this->PembelianService->createPembelianDetail([
                    'purchase_order_id' => $po->id,
                    ...$detailRow,
                ]);
            }

            // ======================
            // 🔔 KIRIM NOTIFIKASI
            // ======================
            if (! $saveAsDraft) {
                DB::afterCommit(fn () => $this->transactionNotifications->notifyApprovalRequest('pembelian', $po, $user));
            }

            return response()->json([
                'status' => 'success',
                'message' => $saveAsDraft
                    ? 'Draft purchase order berhasil disimpan.'
                    : 'Pembelian berhasil ditambahkan',
                'warning' => $this->missingConversionWarning($detailRows),
                'data' => [
                    'id' => $po->id,
                    'no_po' => $po->no_po,
                    'status' => $po->status,
                ],
            ]);

        }, 5);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Pembelian = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $ootDetails = $this->suratPesananOot->ootDetails($Pembelian);

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
        $Pembelian->setAttribute('has_oot_items', $ootDetails->isNotEmpty());
        $Pembelian->setAttribute('oot_item_count', $ootDetails->count());

        return response()->json($Pembelian);
    }

    public function suratPesananNarkotika(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
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
            'withSignature' => $request->boolean('with_signature', true),
        ]);
    }

    public function suratPesananPsikotropika(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
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
            'withSignature' => $request->boolean('with_signature', true),
        ]);
    }

    public function suratPesananPrekursor(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
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
            'withSignature' => $request->boolean('with_signature', true),
        ]);
    }

    public function suratPesananReguler(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
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
            'withSignature' => $request->boolean('with_signature', true),
        ]);
    }

    public function suratPesananOot(Request $request, string $id)
    {
        $purchaseOrder = $this->PembelianService->DetailPembelian($id, BranchAccess::userBranchIds());
        $ootDetails = $this->suratPesananOot->ootDetails($purchaseOrder);

        abort_if(
            $ootDetails->isEmpty(),
            422,
            'Purchase order ini tidak memiliki obat dengan klasifikasi OOT.'
        );

        return view('medcare.menu.pembelianPenerimaan.pembelian.suratPesananOot', [
            'purchaseOrder' => $purchaseOrder,
            'ootDetails' => $ootDetails,
            'copyCount' => SuratPesananOotService::COPY_COUNT,
            'autoPrint' => $request->boolean('print'),
            'withSignature' => $request->boolean('with_signature', true),
        ]);
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

        if ($po->status === 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Draft purchase order harus diajukan terlebih dahulu sebelum disetujui.',
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

        if ($po->status === 'draft') {
            return response()->json([
                'status' => 'error',
                'message' => 'Draft purchase order belum diajukan ke alur approval.',
            ], 422);
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
        $usesManualNumber = $this->validatePurchaseRequest($request, (int) $id);

        $saveAsDraft = $request->boolean('save_as_draft');
        $detailRows = $this->purchaseDetailRows($request);
        $biayaAsuransi = round((float) $request->input('biaya_asuransi', 0), 2);
        $biayaPengiriman = round((float) $request->input('biaya_pengiriman', 0), 2);
        $totalEstimasi = round(
            collect($detailRows)->sum('subtotal') + $biayaAsuransi + $biayaPengiriman,
            2
        );

        return DB::transaction(function () use ($request, $id, $usesManualNumber, $saveAsDraft, $detailRows, $biayaAsuransi, $biayaPengiriman, $totalEstimasi) {

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $branchIds = BranchAccess::userBranchIds($user);
            $existingPo = $this->PembelianService->findByIdPembelian($id, $branchIds);

            if (! $existingPo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembelian tidak ditemukan untuk branch user.',
                ], 404);
            }

            if ($existingPo && $existingPo->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pembelian yang sudah disetujui tidak bisa diedit. Buka approval terlebih dahulu.',
                ], 422);
            }

            if ($usesManualNumber) {
                $this->PembelianService->validateManualPoNumber($request->no_po, (int) $id);
            }

            // =========================== UPDATE HEADER ============================
            $po = $this->PembelianService->updatePembelian($id, [
                'no_po' => $usesManualNumber ? $request->no_po : $existingPo->no_po,
                'distributor_id' => $request->distributor_id,
                'branch_id' => $existingPo->branch_id,
                'tanggal_po' => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $totalEstimasi,
                'biaya_asuransi' => $biayaAsuransi,
                'biaya_pengiriman' => $biayaPengiriman,
                'catatan' => $request->catatan,
                'approved_by' => null,
                'status' => $saveAsDraft ? 'draft' : 'waiting_approval',
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

            if (! $saveAsDraft) {
                DB::afterCommit(fn () => $this->transactionNotifications->notifyApprovalRequest('pembelian', $po, $user));
            }

            return response()->json([
                'status' => 'success',
                'message' => $saveAsDraft
                    ? 'Draft purchase order berhasil diperbarui.'
                    : 'Pembelian berhasil diperbarui',
                'warning' => $this->missingConversionWarning($detailRows),
                'data' => [
                    'id' => $po->id,
                    'no_po' => $po->no_po,
                    'status' => $po->status,
                ],
            ]);

        }, 5);
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
     * @return array<int, array<string, bool|int|float|null>>
     */
    private function purchaseDetailRows(Request $request): array
    {
        $rows = [];
        $medicineIds = collect($request->input('obat_id', []))
            ->map(fn ($medicineId) => (int) $medicineId)
            ->filter()
            ->unique()
            ->values();
        $medicines = MasterObatModel::query()
            ->with([
                'golongan',
                'mainGolongan.golongan',
                'subGolongan.mainGolongan.golongan',
            ])
            ->whereKey($medicineIds)
            ->get()
            ->keyBy('id');

        foreach ($request->input('obat_id', []) as $index => $obatId) {
            $qty = (float) $request->input('qty.'.$index, 0);
            $price = (float) $request->input('harga_estimasi.'.$index, 0);
            $conversionId = (int) $request->input('satuan_id.'.$index, 0);
            [$discount1, $discount2, $discount3] = TieredDiscount::percentages(
                $request->input('diskon_1.'.$index, 0),
                $request->input('diskon_2.'.$index, 0),
                $request->input('diskon_3.'.$index, 0)
            );
            $ppn = round(min(100, max(0, (float) $request->input('ppn.'.$index, 11))), 2);
            $netAmount = TieredDiscount::netAmount(
                $qty * $price,
                $discount1,
                $discount2,
                $discount3
            );

            $rows[] = [
                'obat_id' => (int) $obatId,
                'qty' => $qty,
                'harga_estimasi' => $price,
                'diskon_1' => $discount1,
                'diskon_2' => $discount2,
                'diskon_3' => $discount3,
                'ppn' => $ppn,
                'subtotal' => round($netAmount * (1 + ($ppn / 100)), 2),
                'satuan_konversi' => $conversionId > 0 ? $conversionId : null,
                'is_oot' => $this->suratPesananOot->isOotDrug(
                    $medicines->get((int) $obatId)
                ),
            ];
        }

        return $rows;
    }

    private function missingConversionWarning(array $detailRows): ?string
    {
        if (collect($detailRows)->contains(fn (array $row) => $row['satuan_konversi'] === null)) {
            return 'PO hanya dapat disimpan sebagai draft. Lengkapi satuan konversi langsung di Form Purchase Order sebelum memproses PO.';
        }

        return null;
    }

    private function validatePurchaseRequest(Request $request, ?int $ignorePurchaseOrderId = null): bool
    {
        $request->merge([
            'no_po' => trim((string) $request->input('no_po')),
        ]);

        $purchaseOrderUniqueRule = 'unique:purchase_orders,no_po';

        if ($ignorePurchaseOrderId !== null) {
            $purchaseOrderUniqueRule .= ','.$ignorePurchaseOrderId;
        }

        $distributorId = $request->input('distributor_id');
        $usesManualNumber = is_scalar($distributorId) && (bool) DistributorModel::query()
            ->whereKey($distributorId)->value('uses_manual_po_number');
        $numberRules = $usesManualNumber
            ? ['required', 'string', 'max:50', $purchaseOrderUniqueRule]
            : ['nullable', 'string', 'max:50'];

        $validator = Validator::make($request->all(), [
            'no_po' => $numberRules,
            'save_as_draft' => ['sometimes', 'boolean'],
            'distributor_id' => ['required', 'integer', 'exists:distributors,id'],
            'tanggal' => ['required', 'date_format:d-m-Y'],
            'catatan' => ['nullable', 'string'],
            'total_estimasi' => ['required', 'numeric', 'min:0'],
            'biaya_asuransi' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'biaya_pengiriman' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],

            'obat_id' => ['required', 'array', 'min:1'],
            'obat_id.*' => ['required', 'integer', 'exists:master_obats,id'],
            'qty' => ['required', 'array'],
            'qty.*' => ['required', 'numeric', 'min:1'],
            'harga_estimasi' => ['required', 'array'],
            'harga_estimasi.*' => ['required', 'numeric', 'min:0'],
            'diskon_1' => ['required', 'array'],
            'diskon_1.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_2' => ['required', 'array'],
            'diskon_2.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'diskon_3' => ['required', 'array'],
            'diskon_3.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ppn' => ['sometimes', 'array'],
            'ppn.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'subtotal' => ['required', 'array'],
            'subtotal.*' => ['required', 'numeric', 'min:0'],
            'satuan_id' => ['required', 'array'],
            'satuan_id.*' => ['nullable', 'integer', 'min:0'],
        ], [
            'satuan_id.required' => 'Satuan setiap item obat belum selesai dimuat. Silakan tunggu lalu simpan kembali.',
        ]);

        $validator->after(function ($validator) use ($request) {
            $medicineIds = $request->input('obat_id', []);

            if (! is_array($medicineIds)) {
                return;
            }

            $detailFields = [
                'qty',
                'harga_estimasi',
                'diskon_1',
                'diskon_2',
                'diskon_3',
                'subtotal',
                'satuan_id',
            ];

            if ($request->has('ppn')) {
                $detailFields[] = 'ppn';
            }
            $itemCount = count($medicineIds);

            foreach ($detailFields as $field) {
                $values = $request->input($field, []);

                if (! is_array($values) || count($values) !== $itemCount) {
                    $validator->errors()->add(
                        $field,
                        'Data '.str_replace('_', ' ', $field).' harus lengkap untuk seluruh '.$itemCount.' item obat.'
                    );
                }
            }

            $conversionValues = $request->input('satuan_id', []);

            if (! is_array($conversionValues)) {
                return;
            }

            $conversionIds = collect($conversionValues)
                ->map(fn ($conversionId) => (int) $conversionId)
                ->filter(fn (int $conversionId) => $conversionId > 0)
                ->unique()
                ->values();
            $conversions = KonversiSatuanModel::query()
                ->whereKey($conversionIds)
                ->where('konversi', '>', 0)
                ->whereHas('satuan')
                ->get(['id', 'obat_id'])
                ->keyBy('id');

            foreach ($medicineIds as $index => $medicineId) {
                if (! array_key_exists($index, $conversionValues)) {
                    $validator->errors()->add(
                        'satuan_id.'.$index,
                        'Satuan item obat ke-'.((int) $index + 1).' belum selesai dimuat.'
                    );

                    continue;
                }

                $medicineId = (int) $medicineId;
                $conversionId = (int) $conversionValues[$index];

                if ($conversionId <= 0) {
                    if (! $request->boolean('save_as_draft')) {
                        $validator->errors()->add(
                            'satuan_id.'.$index,
                            'Lengkapi satuan konversi untuk item obat ke-'.((int) $index + 1).'. PO hanya dapat disimpan sebagai draft sebelum satuan konversi lengkap.'
                        );
                    }

                    continue;
                }

                $conversion = $conversions->get($conversionId);

                if (! $conversion || (int) $conversion->obat_id !== $medicineId) {
                    $validator->errors()->add(
                        'satuan_id.'.$index,
                        'Satuan item obat ke-'.((int) $index + 1).' tidak valid atau tidak sesuai dengan obat.'
                    );
                }
            }
        });

        $validator->validate();

        return $usesManualNumber;
    }
}
