<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Sediaan\SediaanObatExport;
use App\Models\SediaanModel;
use App\Repositories\Settings\Master\SediaanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SediaanService
{
    protected $SediaanRepository;

    public function __construct(SediaanRepository $SediaanRepository)
    {
        $this->SediaanRepository = $SediaanRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getSediaan()
    {
        $dataSediaan = $this->SediaanRepository->getSediaan();
        return $dataSediaan;
    }

    public function getSediaanTable()
    {
        $Sediaan = $this->SediaanRepository->getSediaan();

        $dataSediaan = [];
        foreach ($Sediaan as $r) {
            $dataSediaan[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'keterangan'=> $r->keterangan,
                'is_active' => $r->is_active,
            ];
        }

        return $dataSediaan;
    }

    public function createSediaan(array $data)
    {
        $dataSediaan = $this->SediaanRepository->createSediaan($data);
        return $dataSediaan;
    }

    public function findByIdSediaan($id)
    {
        $Sediaan = $this->SediaanRepository->findByIdSediaan($id);
        return $Sediaan;
    }

    public function updateSediaan($id, array $data)
    {
        $Sediaan = $this->SediaanRepository->findByIdSediaan($id);
        $Sediaan->update($data);
        return $Sediaan;
    }

    public function updateStatus($id, $status)
    {
        return $this->SediaanRepository->updateStatus($id, $status);
    }

    public function deleteSediaan($id)
    {
        return $this->SediaanRepository->findByIdSediaan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Sediaan_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new SediaanObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Golongan Obat: ' . $e->getMessage());
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
                $kode = trim($row['A']);
                $nama = trim($row['B']);

                if (!$kode || !$nama) continue; // lewati baris kosong

                // cek duplikat
                $exists = SediaanModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    SediaanModel::create([
                        'kode' => $kode,
                        'nama' => $nama,
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
