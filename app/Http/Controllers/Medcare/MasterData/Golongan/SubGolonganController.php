<?php

namespace App\Http\Controllers\Medcare\MasterData\Golongan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\MainGolonganService;
use App\Services\Settings\Master\SubGolonganService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubGolonganController extends Controller
{
    protected $MainGolonganService, $SubGolonganService;

    public function __construct(MainGolonganService $MainGolonganService, SubGolonganService $SubGolonganService)
    {
        $this->MainGolonganService = $MainGolonganService;
        $this->SubGolonganService = $SubGolonganService;
    }

    public function subGolongan()
    {
        return view('medcare.masterData.golongan.subGolongan.subGolongan');
    }

    public function mainGolongan()
    {
        return response()->json($this->MainGolonganService->getMainGolongan());
    }

    public function table()
    {
        $subGolongan = $this->SubGolonganService->getSubGolonganTable();

        return DataTables::of($subGolongan)
            ->addIndexColumn()
            ->addColumn('actions', function ($dataSubGolongan) {
                return '
                    <div class="category-action-group">
                        <button type="button" class="btn category-action-btn category-action-edit" title="Edit sub golongan" onclick="editSubGolongan(' . $dataSubGolongan['id'] . ')">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                        <button type="button" class="btn category-action-btn category-action-delete" title="Hapus sub golongan" onclick="deleteSubGolongan(' . $dataSubGolongan['id'] . ')">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode.*' => 'required|string|max:20|unique:sub_golongan_obats,kode',
            'nama.*' => 'required|string|max:100',
            'main_golongan_id.*' => 'required|exists:main_golongan_obats,id',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->SubGolonganService->createSubGolongan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'main_golongan_id' => $request->main_golongan_id[$index],
                'keterangan' => $request->keterangan[$index] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Sub Golongan berhasil ditambahkan']);
    }

    public function edit(string $id)
    {
        $dataSubGolongan = $this->SubGolonganService->findByIdSubGolongan($id);

        return response()->json([
            'id' => $dataSubGolongan->id,
            'main_golongan_id' => $dataSubGolongan->main_golongan_id,
            'kode' => $dataSubGolongan->kode,
            'nama' => $dataSubGolongan->nama,
            'keterangan' => $dataSubGolongan->keterangan,
            'main_golongan_name' => $dataSubGolongan->mainGolongan->nama ?? '-',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:sub_golongan_obats,kode,' . $id,
            'nama' => 'required|string|max:100',
            'main_golongan_id' => 'required|exists:main_golongan_obats,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $dataSubGolongan = $this->SubGolonganService->updateSubGolongan($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Sub Golongan berhasil diperbarui',
            'data' => $dataSubGolongan,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $this->SubGolonganService->deleteSubGolongan($id);

            return response()->json([
                'success' => true,
                'message' => 'Sub Golongan berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportTemplate()
    {
        return $this->SubGolonganService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->SubGolonganService->importExcel($request->file('file'));
    }
}
