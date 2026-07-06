<?php

namespace App\Http\Controllers\Medcare\MasterData\Golongan;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\GolonganService;
use App\Services\Settings\Master\MainGolonganService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MainGolonganController extends Controller
{
    protected $MainGolonganService, $GolonganService;

    public function __construct(MainGolonganService $MainGolonganService, GolonganService $GolonganService)
    {
        $this->MainGolonganService = $MainGolonganService;
        $this->GolonganService = $GolonganService;
    }

    public function mainGolongan()
    {
        return view('medcare.masterData.golongan.mainGolongan.mainGolongan');
    }

    public function golongan()
    {
        return response()->json($this->GolonganService->getGolongan());
    }

    public function table()
    {
        $mainGolongan = $this->MainGolonganService->getMainGolonganTable();

        return DataTables::of($mainGolongan)
            ->addIndexColumn()
            ->addColumn('actions', function ($dataMainGolongan) {
                return '
                    <div class="category-action-group">
                        <button type="button" class="btn category-action-btn category-action-edit" title="Edit main golongan" onclick="editMainGolongan(' . $dataMainGolongan['id'] . ')">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                        <button type="button" class="btn category-action-btn category-action-delete" title="Hapus main golongan" onclick="deleteMainGolongan(' . $dataMainGolongan['id'] . ')">
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
            'kode.*' => 'required|string|max:20|unique:main_golongan_obats,kode',
            'nama.*' => 'required|string|max:100',
            'golongan_id.*' => 'required|exists:golongan_obats,id',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        foreach ($request->kode as $index => $kode) {
            $this->MainGolonganService->createMainGolongan([
                'kode' => $kode,
                'nama' => $request->nama[$index],
                'golongan_id' => $request->golongan_id[$index],
                'keterangan' => $request->keterangan[$index] ?? null,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Main Golongan berhasil ditambahkan']);
    }

    public function edit(string $id)
    {
        $dataMainGolongan = $this->MainGolonganService->findByIdMainGolongan($id);

        return response()->json([
            'id' => $dataMainGolongan->id,
            'golongan_id' => $dataMainGolongan->golongan_id,
            'kode' => $dataMainGolongan->kode,
            'nama' => $dataMainGolongan->nama,
            'keterangan' => $dataMainGolongan->keterangan,
            'golongan_name' => $dataMainGolongan->golongan->nama ?? '-',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:main_golongan_obats,kode,' . $id,
            'nama' => 'required|string|max:100',
            'golongan_id' => 'required|exists:golongan_obats,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $dataMainGolongan = $this->MainGolonganService->updateMainGolongan($id, $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Main Golongan berhasil diperbarui',
            'data' => $dataMainGolongan,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $this->MainGolonganService->deleteMainGolongan($id);

            return response()->json([
                'success' => true,
                'message' => 'Main Golongan berhasil dihapus.',
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
        return $this->MainGolonganService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->MainGolonganService->importExcel($request->file('file'));
    }
}
