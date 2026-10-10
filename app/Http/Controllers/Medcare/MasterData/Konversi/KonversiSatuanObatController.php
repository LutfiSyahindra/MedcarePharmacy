<?php

namespace App\Http\Controllers\Medcare\MasterData\Konversi;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\KonversiSatuanObatService;
use App\Services\Settings\Master\MasterObatService;
use App\Services\Settings\Master\SatuanService;
use App\Support\BranchAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\DataTables;

class KonversiSatuanObatController extends Controller
{
    protected $KonversiSatuanObatService, $MasterObatService, $SatuanService;
    public function __construct(KonversiSatuanObatService $KonversiSatuanObatService, MasterObatService $MasterObatService, SatuanService $SatuanService)
    {
        $this->KonversiSatuanObatService = $KonversiSatuanObatService;
        $this->MasterObatService = $MasterObatService;
        $this->SatuanService = $SatuanService;
        
    }
    /**
     * Display a listing of the resource.
     */
    public function konversi()
    {
        return view('medcare.masterData.konversi.konversi');
    }

    public function table(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable', Rule::in(['all', 'with', 'without', 'po_without'])]]);
        $branchIds = BranchAccess::userBranchIds();
        $KonversiSatuanObat = $this->KonversiSatuanObatService->getObatKonversiTable($validated['status'] ?? 'all', $branchIds);

        return DataTables::of($KonversiSatuanObat)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataKonversiSatuanObat) {
            return '
                <div class="obat-action-group">
                    <button type="button" class="btn obat-action-btn obat-action-edit" title="Kelola konversi"
                        onclick="manageKonversiObat(' . $dataKonversiSatuanObat['id'] . ')">
                        <i class="mdi mdi-tune-variant"></i>
                    </button>
                </div>
            ';
        })

        ->rawColumns(['actions'])
        ->with('summary', $this->KonversiSatuanObatService->getKonversiSummary($branchIds))
        ->make(true);
    }

    public function getObat()
    {
        $MasterObat = $this->MasterObatService->getMasterObat();
        return $MasterObat;
    }

    public function getSatuan()
    {
        $Satuan = $this->SatuanService->getSatuan();
        return $Satuan;
    }

    private function batchTargetRules(): array
    {
        return [
            'scope' => ['required', Rule::in(['all', 'without', 'po_without'])],
            'satuan_stok_ids' => ['nullable', 'array'],
            'satuan_stok_ids.*' => ['required', 'integer', 'distinct', 'exists:satuans,id'],
            'obat_ids' => ['nullable', 'array'],
            'obat_ids.*' => ['required', 'integer', 'distinct', 'exists:master_obats,id'],
        ];
    }

    public function previewBatch(Request $request)
    {
        $validated = $request->validate($this->batchTargetRules());
        $targets = $this->KonversiSatuanObatService->previewBatch($validated, BranchAccess::userBranchIds());

        return response()->json(['data' => $targets, 'target_count' => count($targets)]);
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            ...$this->batchTargetRules(),
            'target_ids' => ['required', 'array', 'min:1'],
            'target_ids.*' => ['required', 'integer', 'distinct', 'exists:master_obats,id'],
            'conversions' => ['required', 'array', 'min:1', 'max:20'],
            'conversions.*.satuan_id' => ['required', 'integer', 'distinct', 'exists:satuans,id'],
            'conversions.*.konversi' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'conversions.*.is_default' => ['nullable', 'boolean'],
        ]);
        $result = $this->KonversiSatuanObatService->storeBatch($validated, BranchAccess::userBranchIds());

        return response()->json([
            'status' => 'success',
            'data' => $result,
            'message' => $result['added'].' konversi ditambahkan. '.$result['updated_medicines'].' obat diperbarui. '.$result['skipped'].' nilai konversi yang sudah ada dilewati. '.$this->poSyncMessage($result['po_sync']),
        ]);
    }

    private function poSyncMessage(array $summary): string
    {
        $message = $summary['updated_items'].' item pada '.$summary['updated_orders'].' PO ikut diperbarui.';
        if ($summary['protected_items']) {
            $message .= ' '.$summary['protected_items'].' item PO dilindungi karena sudah memiliki penerimaan atau status PO sudah ditutup.';
        }
        if ($summary['ambiguous_items']) {
            $message .= ' '.$summary['ambiguous_items'].' item PO belum diperbarui. Pilih satu konversi Utama agar satuan PO dapat ditentukan.';
        }

        return $message;
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
        Log::info($request);
        $validated = $request->validate([
            'obat_id.*' => 'required|integer|exists:master_obats,id',
            'satuan_id.*' => 'required|integer|exists:satuans,id',
            'konversi.*' => 'required|integer|min:1',
            'is_default.*' => 'nullable|boolean'
        ]);

        $poSync = DB::transaction(function () use ($request) {
            foreach ($request->obat_id as $index => $obat_id) {
                $this->KonversiSatuanObatService->createKonversi([
                    'obat_id' => $obat_id,
                    'satuan_id' => $request->satuan_id[$index],
                    'konversi' => $request->konversi[$index],
                    'is_default'=> $request->is_default[$index] ?? 0,
                ]);
            }

            return $this->KonversiSatuanObatService->syncMissingPurchaseOrderUnits($request->obat_id, BranchAccess::userBranchIds());
        }, 3);

        return response()->json(['status' => 'success', 'po_sync' => $poSync, 'message' => 'Konversi berhasil ditambahkan. '.$this->poSyncMessage($poSync)]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $KonversiSatuanObat = $this->KonversiSatuanObatService->findKonversi($id);
        return $KonversiSatuanObat;
    }

    public function sync(Request $request, string $obatId)
    {
        $validated = $request->validate([
            'conversion_id' => 'nullable|array',
            'conversion_id.*' => 'nullable|integer|exists:obat_satuan_conversions,id',
            'satuan_id' => 'required|array|min:1',
            'satuan_id.*' => 'required|integer|exists:satuans,id',
            'konversi' => 'required|array|min:1',
            'konversi.*' => 'required|integer|min:1',
            'is_default' => 'nullable|array',
            'is_default.*' => 'nullable|boolean',
        ]);

        $data = $this->KonversiSatuanObatService->syncKonversiForObat($obatId, $validated, BranchAccess::userBranchIds());

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Konversi satuan obat berhasil disimpan. '.$this->poSyncMessage($data['po_sync']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::info($request->all());
        $validated = $request->validate([
            'obat_id' => [
                'required',
                'integer',
                'exists:master_obats,id',
            ],
            'satuan_id' => [
                'required',
                'integer',
                'exists:satuans,id',
                Rule::unique('obat_satuan_conversions', 'satuan_id')
                    ->ignore($id) 
                    ->where('obat_id', $request->obat_id),
            ],
            'konversi'  => 'required|integer|min:1',
            'is_default' => 'nullable|in:0,1',
        ]);

        $validated['is_default'] = (int) $request->input('is_default', 0);

        $result = DB::transaction(function () use ($id, $validated) {
            $conversion = $this->KonversiSatuanObatService->updateKonversi($id, $validated);

            return [
                'conversion' => $conversion,
                'po_sync' => $this->KonversiSatuanObatService->syncMissingPurchaseOrderUnits([$conversion->obat_id], BranchAccess::userBranchIds()),
            ];
        }, 3);

        return response()->json(['status' => 'success', 'data' => $result['conversion'], 'po_sync' => $result['po_sync'], 'message' => 'Konversi berhasil diperbarui. '.$this->poSyncMessage($result['po_sync'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->KonversiSatuanObatService->deleteKonversi($id);
            return response()->json([
                'success' => true,
                'message' => 'Konversi berhasil dihapus.'
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportTemplate()
    {
        return $this->KonversiSatuanObatService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->KonversiSatuanObatService->importExcel($request->file('file'), BranchAccess::userBranchIds());
    }
}
