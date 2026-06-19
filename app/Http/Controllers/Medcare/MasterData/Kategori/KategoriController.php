<?php

namespace App\Http\Controllers\Medcare\MasterData\Kategori;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\KategoriUtamaService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class KategoriController extends Controller
{
    protected $KategoriUtamaService;
    public function __construct(KategoriUtamaService $KategoriUtamaService)
    {
        $this->KategoriUtamaService = $KategoriUtamaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function kategoriUtama()
    {
        return view('medcare.masterData.kategori.kategoriUtama.Kategori');
    }

    public function table()
    {
        $KategoriUtama = $this->KategoriUtamaService->getKategoriUtamaTable();

        return DataTables::of($KategoriUtama)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataKategoriUtama) {
            return '
                <div class="category-action-group">
                    <button type="button" class="btn category-action-btn category-action-edit" title="Edit kategori utama" onclick="editKategoriUtama(' . $dataKategoriUtama['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn category-action-btn category-action-delete" title="Hapus kategori utama" onclick="deleteKategoriUtama(' . $dataKategoriUtama['id'] . ')">
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
            'code.*' => 'required|string|max:10|unique:categories,code',
            'name.*' => 'required|string|max:100',
        ]);

        foreach ($request->code as $index => $code) {
            $this->KategoriUtamaService->createKategoriUtama([
                'code' => $code,
                'name' => $request->name[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Kategori Utama berhasil ditambahkan']);
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
        $dataKategoriUtama = $this->KategoriUtamaService->findByIdKategoriUtama($id);
        return response()->json($dataKategoriUtama);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:categories,code,' . $id,
            'name' => 'required|string|max:100',
        ]);

        $dataKategoriUtama = $this->KategoriUtamaService->updateKategoriUtama($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kategori Utama berhasil diperbarui',
            'data'    => $dataKategoriUtama
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->KategoriUtamaService->deleteKategoriUtama($id);
            return response()->json([
                'success' => true,
                'message' => 'Kategori Utama berhasil dihapus.'
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
