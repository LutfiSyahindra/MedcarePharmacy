<?php

namespace App\Http\Controllers\Medcare\Settings\Margin;

use App\Http\Controllers\Controller;
use App\Services\Settings\Margins\MarginsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class MarginController extends Controller
{
    protected $MarginsService;
    public function __construct(MarginsService $MarginsService)
    {
        $this->MarginsService = $MarginsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function margin()
    {
        return view('medcare.settings.margin.margin');
    }

    public function table()
    {
        $Margins = $this->MarginsService->getMarginsTable();

        return DataTables::of($Margins)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataMargins) {
            return '
                <button class="btn btn-sm btn-success" onclick="editMargins(' . $dataMargins['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deleteMargins(' . $dataMargins['id'] . ')">  
                    <i class="mdi mdi-delete"></i>
                </button>
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
            'tingkat' => 'required|string|in:kategoriUtama,kategori,sub_kategori,obat',
            'reference_id' => 'required',
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
        Log::info($request->all());
        $validated = $request->validate([
            'tingkat' => 'required|string|in:kategoriUtama,kategori,sub_kategori,obat',
            'reference_id' => 'required',
            'faktor_jual' => 'required|numeric|min:0|max:100',
        ]);

        Log::info($validated);

        $dataMargins = $this->MarginsService->updateMargins($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Margins updated successfully',
            'data'    => $dataMargins
        ], 200);
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
