<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\golongan\GolonganObatExport;
use App\Models\GolonganModel;
use App\Repositories\Settings\Master\GolonganRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class GolonganService
{
    protected $GolonganRepository;

    public function __construct(GolonganRepository $GolonganRepository)
    {
        $this->GolonganRepository = $GolonganRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getGolongan()
    {
        $dataGolongan = $this->GolonganRepository->getGolongan();
        return $dataGolongan;
    }

    public function getGolonganTable()
    {
        $Golongan = $this->GolonganRepository->getGolongan();

        $dataGolongan = [];
        foreach ($Golongan as $r) {
            $dataGolongan[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'keterangan'=> $r->keterangan,
                'is_active' => $r->is_active,
            ];
        }

        return $dataGolongan;
    }

    public function createGolongan(array $data)
    {
        $dataGolongan = $this->GolonganRepository->createGolongan($data);
        return $dataGolongan;
    }

    public function findByIdGolongan($id)
    {
        $Golongan = $this->GolonganRepository->findByIdGolongan($id);
        return $Golongan;
    }

    public function updateGolongan($id, array $data)
    {
        $Golongan = $this->GolonganRepository->findByIdGolongan($id);
        $Golongan->update($data);
        return $Golongan;
    }

    public function updateStatus($id, $status)
    {
        return $this->GolonganRepository->updateStatus($id, $status);
    }

    public function deleteGolongan($id)
    {
        return $this->GolonganRepository->findByIdGolongan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Golongan_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new GolonganObatExport, $fileName);
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
                $exists = GolonganModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    GolonganModel::create([
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
