<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Rak\RakObatExport;
use App\Models\RakPenyimpanan;
use App\Models\RakPenyimpananModel;
use App\Repositories\Settings\Master\RakRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RakService
{
    protected $RakRepository;

    public function __construct(RakRepository $RakRepository)
    {
        $this->RakRepository = $RakRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getRak()
    {
        $dataRak = $this->RakRepository->getRak();
        return $dataRak;
    }

    public function getRakTable()
    {
        $Rak = $this->RakRepository->getRak();

        $dataRak = [];
        foreach ($Rak as $r) {
            $dataRak[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'lokasi'      => $r->lokasi,
                'is_active' => $r->is_active,
            ];
        }

        return $dataRak;
    }

    public function createRak(array $data)
    {
        $dataRak = $this->RakRepository->createRak($data);
        return $dataRak;
    }

    public function findByIdRak($id)
    {
        $Rak = $this->RakRepository->findByIdRak($id);
        return $Rak;
    }

    public function updateRak($id, array $data)
    {
        $Rak = $this->RakRepository->findByIdRak($id);
        $Rak->update($data);
        return $Rak;
    }

    public function updateStatus($id, $status)
    {
        return $this->RakRepository->updateStatus($id, $status);
    }

    public function deleteRak($id)
    {
        return $this->RakRepository->findByIdRak($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Rak_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new RakObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Rak Obat: ' . $e->getMessage());

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
                $kode = trim((string) ($row['A'] ?? ''));
                $nama = trim((string) ($row['B'] ?? ''));
                $lokasi = trim((string) ($row['C'] ?? ''));
                $keterangan = trim((string) ($row['D'] ?? ''));

                if (!$kode || !$nama) continue; // lewati baris kosong

                // cek duplikat
                $exists = RakPenyimpananModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    RakPenyimpananModel::create([
                        'kode' => $kode,
                        'nama' => $nama,
                        'lokasi' => $lokasi,
                        'keterangan' => $keterangan,
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
