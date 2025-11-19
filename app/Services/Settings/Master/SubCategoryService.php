<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Kategori\SubKategoriObatExport;
use App\Models\MainCategoryModel;
use App\Models\SubCategoryModel;
use App\Repositories\Settings\Master\SubCategoryRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SubCategoryService
{
    /**
     * Create a new class instance.
     */
    protected $SubCategoryRepository;

    public function __construct(SubCategoryRepository $SubCategoryRepository)
    {
        $this->SubCategoryRepository = $SubCategoryRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getSubCategory()
    {
        $dataSubCategory = $this->SubCategoryRepository->getSubCategory();
        return $dataSubCategory;
    }

    public function getSubCategoryTable()
    {
        $SubCategory = $this->SubCategoryRepository->getSubCategory()->load('mainCategory');

        $dataSubCategory = [];
        foreach ($SubCategory as $r) {
            $dataSubCategory[] = [
                'id'                => $r->id,
                'name'              => $r->name,
                'main_category_id'  => $r->mainCategory->name,
                'code'              => $r->code
            ];
        }

        return $dataSubCategory;
    }

    public function createSubCategory(array $data)
    {
        $dataSubCategory = $this->SubCategoryRepository->createSubCategory($data);
        return $dataSubCategory;
    }

    public function findByIdSubCategory($id)
    {
        $SubCategory = $this->SubCategoryRepository->findByIdSubCategory($id);
        return $SubCategory;
    }

    public function updateSubCategory($id, array $data)
    {
        $SubCategory = $this->SubCategoryRepository->findByIdSubCategory($id);
        $SubCategory->update($data);
        return $SubCategory;
    }

    public function deleteSubCategory($id)
    {
        return $this->SubCategoryRepository->findByIdSubCategory($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_SubKategori_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new SubKategoriObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Sub Kategori Obat: ' . $e->getMessage());

            return [
                'status' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage(),
            ];
        }
    }

    public function importExcel($file)
    {
        DB::beginTransaction();
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $added = 0;
            $skipped = 0;

            // Lewati header (baris pertama)
            foreach (array_slice($rows, 1) as $row) {
                $mainKategori = trim($row['A']);
                $code = trim($row['B']);
                $nama = trim($row['C']);

                if (!$code || !$nama) continue; // lewati baris kosong

                // cek duplikat
                $exists = SubCategoryModel::where('code', $code)->exists();

                // cari ID kategori utama berdasarkan kode
                $mainkategoriId = MainCategoryModel::where('code', $mainKategori)->first();

                if (!$mainkategoriId) {
                    // kalau tidak ditemukan, bisa skip atau buat error
                    $skipped++;
                    continue;
                }

                if ($exists) {
                    $skipped++;
                } else {
                    SubCategoryModel::create([
                        'main_category_id' => $mainkategoriId->id,
                        'code' => $code,
                        'name' => $nama,
                    ]);
                    $added++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import selesai!',
                'added' => $added,
                'skipped' => $skipped,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
