<?php

namespace App\Http\Controllers\Medcare\MasterData\Distributor;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\DistributorService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DistributorController extends Controller
{
    protected $DistributorService;
    public function __construct(DistributorService $DistributorService)
    {
        $this->DistributorService = $DistributorService;
    }
    /**
     * Display a listing of the resource.
     */
    public function distributor()
    {
        return view('medcare.masterData.distributor.distributor');
    }

    public function table()
    {
        $Distributor = $this->DistributorService->getDistributorTable();

        return DataTables::of($Distributor)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataDistributor) {
            return '
                <div class="company-action-group">
                    <button type="button" class="btn company-action-btn company-action-edit" title="Edit distributor" onclick="editDistributor(' . $dataDistributor['id'] . ')">
                        <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button type="button" class="btn company-action-btn company-action-delete" title="Hapus distributor" onclick="deleteDistributor(' . $dataDistributor['id'] . ')">
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
            'kode.*'     => 'required|string|max:10|unique:distributors,kode',
            'nama.*'     => 'required|string|max:100',
            'alamat.*'   => 'nullable|string|max:100',
            'telepon.*'  => 'nullable|string|max:100',
            'email.*'    => 'nullable|email|max:100',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->DistributorService->createDistributor([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'alamat' => $request->alamat[$index] ?? null,
                'telepon' => $request->telepon[$index] ?? null,
                'email' => $request->email[$index] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Distributor berhasil ditambahkan']);
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
        $dataDistributor = $this->DistributorService->findByIdDistributor($id);
        return response()->json($dataDistributor);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode'     => 'required|string|max:10|unique:distributors,kode,' . $id,
            'nama'     => 'required|string|max:100',
            'alamat'   => 'nullable|string|max:100',
            'telepon'  => 'nullable|string|max:100',
            'email'    => 'nullable|email|max:100',
        ]);

        $dataDistributor = $this->DistributorService->updateDistributor($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Distributor berhasil diperbarui',
            'data'    => $dataDistributor
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        return $this->DistributorService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->DistributorService->deleteDistributor($id);
            return response()->json([
                'success' => true,
                'message' => 'Distributor berhasil dihapus.'
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
        return $this->DistributorService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->DistributorService->importExcel($request->file('file'));
    }
}
