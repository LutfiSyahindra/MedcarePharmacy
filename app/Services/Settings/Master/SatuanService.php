<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Satuan\SatuanObatExport;
use App\Imports\MasterData\Satuan\SatuanObatImport;
use App\Models\SatuansModel;
use App\Repositories\Settings\Master\SatuanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;


class SatuanService
{
    protected $SatuanRepository;

    public function __construct(SatuanRepository $SatuanRepository)
    {
        $this->SatuanRepository = $SatuanRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getSatuan()
    {
        $dataSatuan = $this->SatuanRepository->getSatuan();
        return $dataSatuan;
    }

    public function getSatuanTable()
    {
        $Satuan = $this->SatuanRepository->getSatuan();

        $dataSatuan = [];
        foreach ($Satuan as $r) {
            $dataSatuan[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'is_active' => $r->is_active,
            ];
        }

        return $dataSatuan;
    }

    public function createSatuan(array $data)
    {
        $dataSatuan = $this->SatuanRepository->createSatuan($data);
        return $dataSatuan;
    }

    public function findByIdSatuan($id)
    {
        $Satuan = $this->SatuanRepository->findByIdSatuan($id);
        return $Satuan;
    }

    public function updateSatuan($id, array $data)
    {
        $Satuan = $this->SatuanRepository->findByIdSatuan($id);
        $Satuan->update($data);
        return $Satuan;
    }

    public function updateStatus($id, $status)
    {
        return $this->SatuanRepository->updateStatus($id, $status);
    }

    public function deleteSatuan($id)
    {
        return $this->SatuanRepository->findByIdSatuan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Satuan_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new SatuanObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Satuan Obat: ' . $e->getMessage());

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
                $exists = SatuansModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    SatuansModel::create([
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
