<?php

namespace App\Http\Controllers\Medcare\MasterData\Pabrikan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\PabrikanService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PabrikanController extends Controller
{
    protected $PabrikanService;
    public function __construct(PabrikanService $PabrikanService)
    {
        $this->PabrikanService = $PabrikanService;
    }
    /**
     * Display a listing of the resource.
     */
    public function pabrikan()
    {
        return view('medcare.masterData.pabrikan.pabrikan');
    }

    public function table()
    {
        $Pabrikan = $this->PabrikanService->getPabrikanTable();

        return DataTables::of($Pabrikan)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataPabrikan) {
            return '
                <button class="btn btn-sm btn-success" onclick="editPabrikan(' . $dataPabrikan['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deletePabrikan(' . $dataPabrikan['id'] . ')">  
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
            'kode.*'     => 'required|string|max:10|unique:pabrikan,kode',
            'nama.*'     => 'required|string|max:100',
            'alamat.*'   => 'nullable|string|max:100',
            'telepon.*'  => 'nullable|string|max:100',
            'email.*'    => 'nullable|email|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->PabrikanService->createPabrikan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'alamat' => $request->alamat[$index],
                'telepon' => $request->telepon[$index],
                'email' => $request->email[$index],
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Pabrikan berhasil ditambahkan']);
    } //

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
        $dataPabrikan = $this->PabrikanService->findByIdPabrikan($id);
        return response()->json($dataPabrikan);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode'     => 'required|string|max:10|unique:pabrikan,kode,' . $id,
            'nama'     => 'required|string|max:100',
            'alamat'   => 'nullable|string|max:100',
            'telepon'  => 'nullable|string|max:100',
            'email'    => 'nullable|email|max:100',
        ]);

        $dataPabrikan = $this->PabrikanService->updatePabrikan($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pabrikan updated successfully',
            'data'    => $dataPabrikan
        ], 200);
    }

    public function updateStatus(Request $request){
        return $this->PabrikanService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->PabrikanService->deletePabrikan($id);
            return response()->json([
                'success' => true,
                'message' => 'Pabrikan berhasil dihapus.'
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
        return $this->PabrikanService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->PabrikanService->importExcel($request->file('file'));
    }
}
