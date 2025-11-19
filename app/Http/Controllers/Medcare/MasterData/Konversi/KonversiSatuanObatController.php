<?php

namespace App\Http\Controllers\Medcare\MasterData\Konversi;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\KonversiSatuanObatService;
use App\Services\Settings\Master\MasterObatService;
use App\Services\Settings\Master\SatuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
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

    public function table()
    {
        $KonversiSatuanObat = $this->KonversiSatuanObatService->getKonversi();

        return DataTables::of($KonversiSatuanObat)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataKonversiSatuanObat) {
            return '
                <button class="btn btn-sm btn-success" onclick="editKonversiSatuanObat(' . $dataKonversiSatuanObat['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deleteKonversiSatuanObat(' . $dataKonversiSatuanObat['id'] . ')">  
                    <i class="mdi mdi-delete"></i>
                </button>
            ';
        })

        ->rawColumns(['actions'])
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
            'obat_id.*' => 'required|string|max:10|unique:obat_satuan_conversions,obat_id',
            'satuan_id.*' => 'required|string|max:100',
            'konversi.*' => 'required|string|max:100',
            'is_default.*' => 'nullable|boolean'
        ]);

        foreach ($request->obat_id as $index => $obat_id) {
            $this->KonversiSatuanObatService->createKonversi([
                'obat_id' => $obat_id,
                'satuan_id' => $request->satuan_id[$index],
                'konversi' => $request->konversi[$index],
                'is_default'=> $request->is_default[$index] ?? 0,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Konversi berhasil ditambahkan']);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::info($request->all());
        $validated = $request->validate([
            'obat_id' => [
                'required',
                Rule::unique('obat_satuan_conversions', 'obat_id')
                    ->ignore($id) 
                    ->where('satuan_id', $request->satuan_id),
            ],
            'satuan_id' => 'required|string|max:100',
            'konversi'  => 'required|integer|min:1',
            'is_default' => 'nullable|in:0,1',
        ]);

        
        $validated['is_default'] = $request->is_default[0] ?? 0;

        $KonversiSatuanObat = $this->KonversiSatuanObatService->updateKonversi($id, $validated);

        return response()->json(['status' => 'success', 'data' => $KonversiSatuanObat, 'message' => 'Konversi berhasil Update']);
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

        return $this->KonversiSatuanObatService->importExcel($request->file('file'));
    }
}
