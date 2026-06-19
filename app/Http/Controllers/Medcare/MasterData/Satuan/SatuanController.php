<?php

namespace App\Http\Controllers\Medcare\MasterData\Satuan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\SatuanService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SatuanController extends Controller
{
    protected $SatuanService;
    public function __construct(SatuanService $SatuanService)
    {
        $this->SatuanService = $SatuanService;
    }
    /**
     * Display a listing of the resource.
     */
    public function satuan()
    {
        return view('medcare.masterData.satuan.satuan');
    }

    public function table()
    {
        $Satuan = $this->SatuanService->getSatuanTable();

        return DataTables::of($Satuan)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataSatuan) {
            return '
                <div class="satuan-action-group">
                    <button type="button" class="btn satuan-action-btn satuan-action-edit" title="Edit satuan" onclick="editSatuan(' . $dataSatuan['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn satuan-action-btn satuan-action-delete" title="Hapus satuan" onclick="deleteSatuan(' . $dataSatuan['id'] . ')">
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
            'kode.*' => 'required|string|max:10|unique:satuans,kode',
            'nama.*' => 'required|string|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->SatuanService->createSatuan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Satuan berhasil ditambahkan']);
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
        $dataSatuan = $this->SatuanService->findByIdSatuan($id);
        return response()->json($dataSatuan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:satuans,kode,' . $id,
            'nama' => 'required|string|max:100',
        ]);

        $dataSatuan = $this->SatuanService->updateSatuan($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Satuan berhasil diperbarui',
            'data'    => $dataSatuan
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        return $this->SatuanService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->SatuanService->deleteSatuan($id);
            return response()->json([
                'success' => true,
                'message' => 'Satuan berhasil dihapus.'
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
        return $this->SatuanService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->SatuanService->importExcel($request->file('file'));
    }



}
