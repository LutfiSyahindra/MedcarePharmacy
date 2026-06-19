<?php

namespace App\Http\Controllers\Medcare\MasterData\Kategori;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\KategoriUtamaService;
use App\Services\Settings\Master\MainCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class MainKategoriController extends Controller
{
    protected $MainCategoryService, $KategoriUtamaService;
    public function __construct(MainCategoryService $MainCategoryService, KategoriUtamaService $KategoriUtamaService)
    {
        $this->MainCategoryService = $MainCategoryService;
        $this->KategoriUtamaService = $KategoriUtamaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function mainKategori()
    {
        return view('medcare.masterData.kategori.mainKategori.mainKategori');
    }
    
    public function kategoriUtama()
    {
        $KategoriUtama = $this->KategoriUtamaService->getKategoriUtama();
        return response()->json($KategoriUtama);
    }

    public function table()
    {
        $MainCategory = $this->MainCategoryService->getMainCategoryTable();

        return DataTables::of($MainCategory)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataMainCategory) {
            return '
                <div class="category-action-group">
                    <button type="button" class="btn category-action-btn category-action-edit" title="Edit main kategori" onclick="editMainCategory(' . $dataMainCategory['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn category-action-btn category-action-delete" title="Hapus main kategori" onclick="deleteMainCategory(' . $dataMainCategory['id'] . ')">
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
        Log::info($request->all());
        $validated = $request->validate([
            'code.*' => 'required|string|max:10|unique:main_category,code',
            'name.*' => 'required|string|max:100',
            'category_id.*' => 'required|string|max:10',
        ]);

        foreach ($request->code as $index => $code) {
            $this->MainCategoryService->createMainCategory([
                'code' => $code,
                'name' => $request->name[$index],
                'category_id' => $request->category_id[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Kategori berhasil ditambahkan']);
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
        $dataMainCategory = $this->MainCategoryService->findByIdMainCategory($id);
        return response()->json([
            'id' => $dataMainCategory->id,
            'category_id' => $dataMainCategory->category_id,
            'name' => $dataMainCategory->name,
            'code' => $dataMainCategory->code,
            'category_name' => $dataMainCategory->category->name ?? '-', // relasi
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:main_category,code,' . $id,
            'name' => 'required|string|max:100',
            'category_id' => 'required|string|max:10',
        ]);

        $dataMainCategory = $this->MainCategoryService->updateMainCategory($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Main Kategori berhasil diperbarui',
            'data'    => $dataMainCategory
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->MainCategoryService->deleteMainCategory($id);
            return response()->json([
                'success' => true,
                'message' => 'Main Category berhasil dihapus.'
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
        return $this->MainCategoryService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->MainCategoryService->importExcel($request->file('file'));
    }
}
