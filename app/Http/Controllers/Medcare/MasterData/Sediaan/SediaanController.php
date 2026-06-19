<?php

namespace App\Http\Controllers\Medcare\MasterData\Sediaan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\SediaanService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SediaanController extends Controller
{
    protected $SediaanService;
    public function __construct(SediaanService $SediaanService)
    {
        $this->SediaanService = $SediaanService;
    }
    /**
     * Display a listing of the resource.
     */
    public function sediaan()
    {
        return view('medcare.masterData.sediaan.sediaan');
    }

    public function table()
    {
        $Sediaan = $this->SediaanService->getSediaanTable();

        return DataTables::of($Sediaan)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataSediaan) {
            return '
                <div class="sediaan-action-group">
                    <button type="button" class="btn sediaan-action-btn sediaan-action-edit" title="Edit sediaan" onclick="editSediaan(' . $dataSediaan['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn sediaan-action-btn sediaan-action-delete" title="Hapus sediaan" onclick="deleteSediaan(' . $dataSediaan['id'] . ')">
                        <i class="mdi mdi-delete-outline"></i>
                    </button>
                </div>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);
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
            'kode.*' => 'required|string|max:10|unique:sediaan_obats,kode',
            'nama.*' => 'required|string|max:100',
            'keterangan.*' => 'nullable|string|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->SediaanService->createSediaan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'keterangan' => $request->keterangan[$index] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Sediaan berhasil ditambahkan']);
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
        $dataSediaan = $this->SediaanService->findByIdSediaan($id);
        return response()->json($dataSediaan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:sediaan_obats,kode,' . $id,
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $dataSediaan = $this->SediaanService->updateSediaan($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sediaan berhasil diperbarui',
            'data'    => $dataSediaan
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        return $this->SediaanService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->SediaanService->deleteSediaan($id);
            return response()->json([
                'success' => true,
                'message' => 'Sediaan berhasil dihapus.'
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
        return $this->SediaanService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->SediaanService->importExcel($request->file('file'));
    }
}
