<?php

namespace App\Http\Controllers\Medcare\MasterData\Rak;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\RakService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RakController extends Controller
{
    protected $RakService;
    public function __construct(RakService $RakService)
    {
        $this->RakService = $RakService;
    }
    /**
     * Display a listing of the resource.
     */
    public function rak()
    {
        return view('medcare/masterData/rak/rak');
    }

    public function table()
    {
        $Rak = $this->RakService->getRakTable();

        return DataTables::of($Rak)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataRak) {
            return '
                <button class="btn btn-sm btn-success" onclick="editRak(' . $dataRak['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deleteRak(' . $dataRak['id'] . ')">  
                    <i class="mdi mdi-delete"></i>
                </button>
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
            'kode.*' => 'required|string|max:10|unique:rak_penyimpanans,kode',
            'nama.*' => 'required|string|max:100',
            'lokasi.*' => 'nullable|string|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->RakService->createRak([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'lokasi' => $request->lokasi[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Rak Penyimpanan berhasil ditambahkan']);
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
        $dataRak = $this->RakService->findByIdRak($id);
        return response()->json($dataRak);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:rak_penyimpanans,kode,' . $id,
            'nama' => 'required|string|max:100',
            'lokasi' => 'nullable|string|max:100',
        ]);

        $dataRak = $this->RakService->updateRak($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Rak updated successfully',
            'data'    => $dataRak
        ], 200);
    }

    public function updateStatus(Request $request){
        return $this->RakService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->RakService->deleteRak($id);
            return response()->json([
                'success' => true,
                'message' => 'Rak Penyimpanan berhasil dihapus.'
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
        return $this->RakService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->RakService->importExcel($request->file('file'));
    }
}
