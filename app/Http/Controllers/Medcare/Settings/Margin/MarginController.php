<?php

namespace App\Http\Controllers\Medcare\Settings\Margin;

use App\Http\Controllers\Controller;
use App\Services\Menu\Stok\StockService;
use App\Services\Settings\Margins\MarginsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MarginController extends Controller
{
    protected $MarginsService;
    public function __construct(MarginsService $MarginsService, private readonly StockService $stockService)
    {
        $this->MarginsService = $MarginsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function margin()
    {
        return view('medcare.settings.margin.margin', [
            'marginPriority' => $this->MarginsService->marginPriority(),
            'marginPriorityOptions' => MarginsService::priorityOptions(),
        ]);
    }

    public function table()
    {
        $Margins = $this->MarginsService->getMarginsTable();

        return DataTables::of($Margins)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataMargins) {
            return '
                <div class="margin-action-group">
                    <button type="button" class="btn margin-action-btn margin-action-edit" title="Edit margin" onclick="editMargins(' . $dataMargins['id'] . ')"> 
                        <i class="mdi mdi-pencil-outline"></i>
                    </button> 
                    <button type="button" class="btn margin-action-btn margin-action-delete" title="Hapus margin" onclick="deleteMargins(' . $dataMargins['id'] . ')">  
                        <i class="mdi mdi-delete-outline"></i>
                    </button>
                </div>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);
    }

    public function getReferences($tingkat)
    {
        $dataReferences = $this->MarginsService->getReferences($tingkat);
        return response()->json($dataReferences);
    }

    public function updateStatus(Request $request){
        return $this->MarginsService->updateStatus($request->id, $request->status);
    }

    public function updatePriority(Request $request)
    {
        $validated = $request->validate([
            'priority' => ['required', 'array', 'size:3'],
            'priority.*' => ['required', 'string', 'distinct', Rule::in(array_keys(MarginsService::priorityOptions()))],
        ], [
            'priority.required' => 'Prioritas margin wajib diisi.',
            'priority.size' => 'Prioritas margin harus memuat Sub Golongan, Main Golongan, dan Golongan.',
            'priority.*.distinct' => 'Setiap tingkat margin hanya boleh dipilih satu kali.',
        ]);

        $priority = $this->MarginsService->updateMarginPriority($validated['priority']);

        return response()->json([
            'status' => 'success',
            'message' => 'Prioritas margin berhasil diperbarui.',
            'priority' => $priority,
        ]);
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
            'tingkat' => 'required|string|in:kategori,golongan,main_golongan,sub_golongan,obat',
            'reference_id' => 'required|array',
            'reference_id.*' => 'required',
            'faktor_jual' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->reference_id as $id) {
            $this->MarginsService->createMargins([
                'tingkat' => $request->tingkat,
                'reference_id' => $id,
                'faktor_jual' => $request->faktor_jual,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Margins berhasil ditambahkan'
        ]);
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
        $margin = $this->MarginsService->findByIdMargins($id);
        Log::info($margin);
        return response()->json($margin);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'tingkat' => 'required|string|in:kategori,golongan,main_golongan,sub_golongan,obat',
            'reference_id' => 'required',
            'faktor_jual' => 'required|numeric|min:0|max:100',
            'application_scope' => ['required', Rule::in(['existing_products', 'next_receipts'])],
        ], [
            'application_scope.required' => 'Pilih cara penerapan perubahan margin.',
            'application_scope.in' => 'Pilihan penerapan margin tidak valid.',
        ]);

        return DB::transaction(function () use ($id, $validated) {
            $applicationScope = $validated['application_scope'];
            unset($validated['application_scope']);
            $dataMargins = $this->MarginsService->updateMargins($id, $validated);
            $result = ['updated_product_count' => 0, 'updated_batch_count' => 0];

            if ($applicationScope === 'existing_products') {
                $result = $this->stockService->applyUpdatedMarginToBatches($dataMargins);
            }

            return response()->json([
                'status' => 'success',
                'message' => $applicationScope === 'existing_products'
                    ? 'Margin berhasil diperbarui. Harga '.$result['updated_batch_count'].' batch dari '.$result['updated_product_count'].' produk diperbarui.'
                    : 'Margin berhasil diperbarui untuk penerimaan selanjutnya. Harga produk yang sudah ada tetap.',
                'data' => $dataMargins,
                'application_scope' => $applicationScope,
                ...$result,
            ]);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->MarginsService->deleteMargins($id);
            return response()->json([
                'success' => true,
                'message' => 'Margins berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
