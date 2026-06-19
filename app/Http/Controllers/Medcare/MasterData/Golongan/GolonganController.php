<?php

namespace App\Http\Controllers\Medcare\MasterData\Golongan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\GolonganService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GolonganController extends Controller
{
    protected $GolonganService;
    public function __construct(GolonganService $GolonganService)
    {
        $this->GolonganService = $GolonganService;
    }
    /**
     * Display a listing of the resource.
     */
    public function golongan()
    {
        return view('medcare.masterData.golongan.golongan');
    }

    public function table()
    {
        $Golongan = $this->GolonganService->getGolonganTable();

        return DataTables::of($Golongan)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataGolongan) {
            return '
                <div class="golongan-action-group">
                    <button type="button" class="btn golongan-action-btn golongan-action-edit" title="Edit golongan" onclick="editGolongan(' . $dataGolongan['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn golongan-action-btn golongan-action-delete" title="Hapus golongan" onclick="deleteGolongan(' . $dataGolongan['id'] . ')">
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
            'kode.*' => 'required|string|max:10|unique:golongan_obats,kode',
            'nama.*' => 'required|string|max:100',
            'keterangan.*' => 'nullable|string|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->GolonganService->createGolongan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'keterangan' => $request->keterangan[$index] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Golongan berhasil ditambahkan']);
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
        $dataGolongan = $this->GolonganService->findByIdGolongan($id);
        return response()->json($dataGolongan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:golongan_obats,kode,' . $id,
            'nama' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:100',
        ]);

        $dataGolongan = $this->GolonganService->updateGolongan($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Golongan berhasil diperbarui',
            'data'    => $dataGolongan
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        return $this->GolonganService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->GolonganService->deleteGolongan($id);
            return response()->json([
                'success' => true,
                'message' => 'Golongan berhasil dihapus.'
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
        return $this->GolonganService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->GolonganService->importExcel($request->file('file'));
    }
}
