<?php

namespace App\Http\Controllers\Medcare\MasterData\MasterObat;

use App\Http\Controllers\Controller;
use App\Services\Settings\Master\DistributorService;
use App\Services\Settings\Master\GolonganService;
use App\Services\Settings\Master\KategoriUtamaService;
use App\Services\Settings\Master\MainCategoryService;
use App\Services\Settings\Master\MasterObatService;
use App\Services\Settings\Master\PabrikanService;
use App\Services\Settings\Master\RakService;
use App\Services\Settings\Master\SatuanService;
use App\Services\Settings\Master\SediaanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class MasterObatController extends Controller
{
    protected $MasterObatService, $MainCategoryService, $SediaanService, $GolonganService, $SatuanService, $PabrikanService, $DistributorService, $RakService, $KategoriUtamaService;
    public function __construct(MasterObatService $MasterObatService, MainCategoryService $MainCategoryService, SediaanService $SediaanService, GolonganService $GolonganService, SatuanService $SatuanService, PabrikanService $PabrikanService, DistributorService $DistributorService, RakService $RakService, KategoriUtamaService $KategoriUtamaService)
    {
        $this->MasterObatService = $MasterObatService;
        $this->MainCategoryService = $MainCategoryService;
        $this->SediaanService = $SediaanService;
        $this->GolonganService = $GolonganService;
        $this->SatuanService = $SatuanService;
        $this->PabrikanService = $PabrikanService;
        $this->DistributorService = $DistributorService;
        $this->RakService = $RakService;
        $this->KategoriUtamaService = $KategoriUtamaService;
    }
    /**
     * Display a listing of the resource.
     */
    public function MasterObat()
    {
        return view('medcare.masterData.Obat.obat');
    }

    public function table()
    {
        $MasterObat = $this->MasterObatService->getMasterObatTable();

        return DataTables::of($MasterObat)
            ->addIndexColumn()
            ->addColumn('actions', function ($dataMasterObat) {
                return '
                    <div class="obat-action-group">
                        <button type="button" class="btn obat-action-btn obat-action-edit" title="Edit obat" onclick="editMasterObat(' . $dataMasterObat['id'] . ')">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                        <button type="button" class="btn obat-action-btn obat-action-delete" title="Hapus obat" onclick="deleteMasterObat(' . $dataMasterObat['id'] . ')">
                            <i class="mdi mdi-delete-outline"></i>
                        </button>
                    </div>
                ';
            })

            ->rawColumns(['actions'])
            ->make(true);
    }

    public function getMainKategori($kategoriUtama)
    {
        $mainKategori = $this->MasterObatService->getMainKategori($kategoriUtama);
        return response()->json($mainKategori);
    }

    public function getSubKategori($kategori)
    {
        $subKategoris = $this->MasterObatService->getSubKategori($kategori);
        return response()->json($subKategoris);
    }

    public function getKategoriUtama()
    {
        $kategoriUtama = $this->KategoriUtamaService->getKategoriUtama();
        return response()->json($kategoriUtama);
    }

    public function getKategori()
    {
        $kategori = $this->MainCategoryService->getMainCategory();
        return response()->json($kategori);
    }

    public function getSediaan()
    {
        $sediaan = $this->SediaanService->getSediaan();
        return response()->json($sediaan);
    }

    public function getGolongan()
    {
        $golongan = $this->GolonganService->getGolongan();
        return response()->json($golongan);
    }

    public function getSatuan()
    {
        $satuan = $this->SatuanService->getSatuan();
        return response()->json($satuan);
    }

    public function getPabrikan()
    {
        $pabrikan = $this->PabrikanService->getPabrikan();
        return response()->json($pabrikan);
    }

    public function getDistributor()
    {
        $distributor = $this->DistributorService->getDistributor();
        return response()->json($distributor);
    }

    public function getRak()
    {
        $rak = $this->RakService->getRak();
        return response()->json($rak);
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
            'kode_obat'       => 'required|string|max:50|unique:master_obats,kode_obat',
            'nama_obat'       => 'required|string|max:150',
            'sediaan_id'      => 'required|exists:sediaan_obats,id',
            'category_id'     => 'required|exists:categories,id',
            'main_category_id'=> 'required|exists:main_category,id',
            'sub_kategori_id' => 'nullable|exists:sub_categories,id',
            'golongan_id'     => 'required|exists:golongan_obats,id',
            'satuan_id'       => 'required|exists:satuans,id',
            'pabrikan_id'     => 'required|exists:pabrikan,id',
            'distributor_id'  => 'nullable|exists:distributors,id',
            'rak_id'          => 'nullable|exists:rak_penyimpanans,id',

            'komposisi'       => 'nullable|string|max:255',
            'indikasi'        => 'nullable|string|max:255',
            'dosis'           => 'nullable|string|max:255',
            'kemasan'         => 'nullable|string|max:255',

            'stok_minimum'    => 'required|numeric|min:0',
            'harga_beli'      => 'required|numeric|min:0',

            'is_generik'      => 'required|boolean',
            'is_active'       => 'required|boolean',
        ]);

        $this->MasterObatService->createMasterObat($validated);

        return response()->json(['status' => 'success', 'message' => 'Master Obat berhasil ditambahkan']);
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
        $dataMasterObat = $this->MasterObatService->findByIdMasterObat($id);
        return response()->json($dataMasterObat);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode_obat'       => 'required|string|max:50|unique:master_obats,kode_obat,' . $id,
            'nama_obat'       => 'required|string|max:150',
            'sediaan_id'      => 'required|exists:sediaan_obats,id',
            'category_id'     => 'required|exists:categories,id',
            'main_category_id'=> 'required|exists:main_category,id',
            'sub_kategori_id' => 'nullable|exists:sub_categories,id',
            'golongan_id'     => 'required|exists:golongan_obats,id',
            'satuan_id'       => 'required|exists:satuans,id',
            'pabrikan_id'     => 'required|exists:pabrikan,id',
            'distributor_id'  => 'nullable|exists:distributors,id',
            'rak_id'          => 'nullable|exists:rak_penyimpanans,id',

            'komposisi'       => 'nullable|string|max:255',
            'indikasi'        => 'nullable|string|max:255',
            'dosis'           => 'nullable|string|max:255',
            'kemasan'         => 'nullable|string|max:255',
            
            'stok_minimum'    => 'required|numeric|min:0',
            'harga_beli'      => 'required|numeric|min:0',

            'is_generik'      => 'required|boolean',
            'is_active'       => 'required|boolean',
        ]);

        $dataMasterObat = $this->MasterObatService->updateMasterObat($id, $validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Master Obat berhasil diperbarui',
            'data'    => $dataMasterObat
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        return $this->MasterObatService->updateStatus($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->MasterObatService->deleteMasterObat($id);
            return response()->json([
                'success' => true,
                'message' => 'Master Obat berhasil dihapus.'
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
        return $this->MasterObatService->exportTemplate();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        return $this->MasterObatService->importExcel($request->file('file'));
    }
}
