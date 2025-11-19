<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Pabrikan\PabrikanObatExport;
use App\Models\PabrikanModel;
use App\Repositories\Settings\Master\PabrikanRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PabrikanService
{
    protected $PabrikanRepository;

    public function __construct(PabrikanRepository $PabrikanRepository)
    {
        $this->PabrikanRepository = $PabrikanRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getPabrikan()
    {
        $dataPabrikan = $this->PabrikanRepository->getPabrikan();
        return $dataPabrikan;
    }

    public function getPabrikanTable()
    {
        $Pabrikan = $this->PabrikanRepository->getPabrikan();

        $dataPabrikan = [];
        foreach ($Pabrikan as $r) {
            $dataPabrikan[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'alamat'    => $r->alamat ?? '-',
                'telepon'   => $r->telepon ?? '-',
                'email'     => $r->email ?? '-',
                'is_active' => $r->is_active,
            ];
        }

        return $dataPabrikan;
    }

    public function createPabrikan(array $data)
    {
        $dataPabrikan = $this->PabrikanRepository->createPabrikan($data);
        return $dataPabrikan;
    }

    public function findByIdPabrikan($id)
    {
        $Pabrikan = $this->PabrikanRepository->findByIdPabrikan($id);
        return $Pabrikan;
    }

    public function updatePabrikan($id, array $data)
    {
        $Pabrikan = $this->PabrikanRepository->findByIdPabrikan($id);
        $Pabrikan->update($data);
        return $Pabrikan;
    }

    public function updateStatus($id, $status)
    {
        return $this->PabrikanRepository->updateStatus($id, $status);
    }

    public function deletePabrikan($id)
    {
        return $this->PabrikanRepository->findByIdPabrikan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Pabrikan_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new PabrikanObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Pabrikan Obat: ' . $e->getMessage());
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
                $alamat = trim($row['C']);
                $telepon = trim($row['D']);
                $email = trim($row['E']);

                if (!$kode || !$nama) continue; // lewati baris kosong

                // cek duplikat
                $exists = PabrikanModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    PabrikanModel::create([
                        'kode' => $kode,
                        'nama' => $nama,
                        'alamat' => $alamat,
                        'telepon' => $telepon,
                        'email' => $email,
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
