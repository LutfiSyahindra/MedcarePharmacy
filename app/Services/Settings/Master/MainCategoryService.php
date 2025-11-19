<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Kategori\MainKategoriObatExport;
use App\Models\CategoryModel;
use App\Models\MainCategoryModel;
use App\Repositories\Settings\Master\MainCategoryRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MainCategoryService
{
    protected $MainCategoryRepository;

    public function __construct(MainCategoryRepository $MainCategoryRepository)
    {
        $this->MainCategoryRepository = $MainCategoryRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getMainCategory()
    {
        $dataMainCategory = $this->MainCategoryRepository->getMainCategory();
        return $dataMainCategory;
    }

    public function getMainCategoryTable()
    {
        $MainCategory = $this->MainCategoryRepository->getMainCategory()->load('category');

        $dataMainCategory = [];
        foreach ($MainCategory as $r) {
            $dataMainCategory[] = [
                'id'            => $r->id,
                'category_id'   => $r->category->name,
                'name'          => $r->name,
                'code'          => $r->code,
            ];
        }

        return $dataMainCategory;
    }

    public function createMainCategory(array $data)
    {
        $dataMainCategory = $this->MainCategoryRepository->createMainCategory($data);
        return $dataMainCategory;
    }

    public function findByIdMainCategory($id)
    {
        $MainCategory = $this->MainCategoryRepository->findByIdMainCategory($id);
        return $MainCategory;
    }

    public function updateMainCategory($id, array $data)
    {
        $MainCategory = $this->MainCategoryRepository->findByIdMainCategory($id);
        $MainCategory->update($data);
        return $MainCategory;
    }

    public function deleteMainCategory($id)
    {
        return $this->MainCategoryRepository->findByIdMainCategory($id)->delete();
    }
    
    public function exportTemplate()
    {
        try {
            $fileName = 'Template_MainKategori_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new MainKategoriObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Main Kategori Obat: ' . $e->getMessage());

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
                $kategoriUtama = trim($row['A']);
                $code = trim($row['B']);
                $nama = trim($row['C']);

                if (!$code || !$nama) continue; // lewati baris kosong

                // cek duplikat
                $exists = MainCategoryModel::where('code', $code)->exists();

                // cari ID kategori utama berdasarkan kode
                $kategoriUtamaId = CategoryModel::where('code', $kategoriUtama)->first();

                if (!$kategoriUtamaId) {
                    // kalau tidak ditemukan, bisa skip atau buat error
                    $skipped++;
                    continue;
                }

                if ($exists) {
                    $skipped++;
                } else {
                    MainCategoryModel::create([
                        'category_id' => $kategoriUtamaId->id,
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
