<?php

namespace App\Http\Controllers\Medcare\MasterData\Kategori;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\MainCategoryService;
use App\Services\Settings\Master\SubCategoryService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubKategoriController extends Controller
{

    protected $MainCategoryService, $SubCategoryService;
    public function __construct(MainCategoryService $MainCategoryService, SubCategoryService $SubCategoryService)
    {
        $this->MainCategoryService = $MainCategoryService;
        $this->SubCategoryService = $SubCategoryService;
    }
    /**
     * Display a listing of the resource.
     */
    public function subKategori()
    {
        return view('medcare.masterData.kategori.subKategori.subKategori');
    }

    public function table()
    {
        $SubCategory = $this->SubCategoryService->getSubCategoryTable();

        return DataTables::of($SubCategory)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataSubCategory) {
            return '
                <button class="btn btn-sm btn-success" onclick="editSubCategory(' . $dataSubCategory['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deleteSubCategory(' . $dataSubCategory['id'] . ')">  
                    <i class="mdi mdi-delete"></i>
                </button>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);
    }

    public function mainKategori()
    {
        $MainCategory = $this->MainCategoryService->getMainCategory();
        return response()->json($MainCategory);
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
            'code.*' => 'required|string|max:10|unique:sub_categories,code',
            'main_category_id.*' => 'required|string|max:10',
            'name.*' => 'required|string|max:100',
        ]);

        foreach ($request->code as $index => $code) {
            $this->SubCategoryService->createSubCategory([
                'code' => $code,
                'main_category_id' => $request->main_category_id[$index],
                'name' => $request->name[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Sub Kategori berhasil ditambahkan']);
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
        $dataSubCategory = $this->SubCategoryService->findByIdSubCategory($id);
        return response()->json([
            'id' => $dataSubCategory->id,
            'main_category_id' => $dataSubCategory->main_category_id,
            'name' => $dataSubCategory->name,
            'main_category_name' => $dataSubCategory->mainCategory->name ?? '-', // relasi
            'code' => $dataSubCategory->code
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:sub_categories,code,' . $id,
            'main_category_id' => 'required|string|max:10',
            'name' => 'required|string|max:100',
        ]);

        $dataSubCategory = $this->SubCategoryService->updateSubCategory($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Sub Category updated successfully',
            'data'    => $dataSubCategory
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->SubCategoryService->deleteSubCategory($id);
            return response()->json([
                'success' => true,
                'message' => 'Sub Category berhasil dihapus.'
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
        return $this->SubCategoryService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->SubCategoryService->importExcel($request->file('file'));
    }
}
