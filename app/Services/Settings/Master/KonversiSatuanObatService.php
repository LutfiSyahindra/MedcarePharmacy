<?php

namespace App\Services\Settings\Master;

use App\Exports\Menu\Konversi\KonversiExport;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Repositories\Settings\Master\KonversiSatuanObatRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class KonversiSatuanObatService
{
    /**
     * Create a new class instance.
     */
    protected $KonversiSatuanObatRepository;

    public function __construct(KonversiSatuanObatRepository $KonversiSatuanObatRepository)
    {
        $this->KonversiSatuanObatRepository = $KonversiSatuanObatRepository;
    }
    
    public function getKonversi()
    {
        $konversi = $this->KonversiSatuanObatRepository->getKonversi()->load(['obat', 'satuan']);

        $dataKonversiSatuan = [];
        foreach ($konversi as $r) {
            $dataKonversiSatuan[] = [
                'id'        => $r->id,
                'obat_id'   => $r->obat->nama_obat,
                'satuan_id'    => $r->satuan->nama,
                'konversi'  => $r->konversi,
            ];
        }

        return $dataKonversiSatuan;
    }

    public function editKonversi($id, array $data)
    {
        $Konversi = $this->KonversiSatuanObatRepository->findByIdKonversi($id);

        if (!$Konversi) {
            throw new \Exception('Konversi not found');
        }

        $Konversi->update($data);

        return $Konversi;
    }

    public function findKonversi($id)
    {
        return $this->KonversiSatuanObatRepository->findByIdKonversi($id);
    }

    public function updateKonversi($id, array $data)
    {
        return $this->KonversiSatuanObatRepository->findByIdKonversi($id)->update($data);
    }

    public function createKonversi($data)
    {
        return $this->KonversiSatuanObatRepository->createKonversi($data);
    }

    public function deleteKonversi($id)
    {
        return $this->KonversiSatuanObatRepository->findByIdKonversi($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Konversi_Satuan_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new KonversiExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Konversi Satuan Obat: ' . $e->getMessage());
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
                $obat = trim($row['A']);
                $satuan = trim($row['B']);
                $konversi = trim($row['C']);

                if (!$obat || !$satuan) continue; // lewati baris kosong

                // cek duplikat
                $exists = KonversiSatuanModel::where('obat_id', $obat && 'satuan_id', $satuan)->exists();

                // cari
                $obat_id = MasterObatModel::where('nama_obat', $obat)->first();
                $satuan_id = SatuansModel::where('nama', $satuan)->first();

                if ($exists) {
                    $skipped++;
                } else {
                    KonversiSatuanModel::create([
                        'obat_id' => $obat_id?->id,
                        'satuan_id' => $satuan_id?->id,
                        'konversi' => $konversi,
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
