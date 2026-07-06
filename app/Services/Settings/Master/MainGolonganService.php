<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\golongan\MainGolonganObatExport;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Repositories\Settings\Master\MainGolonganRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MainGolonganService
{
    protected $MainGolonganRepository;

    public function __construct(MainGolonganRepository $MainGolonganRepository)
    {
        $this->MainGolonganRepository = $MainGolonganRepository;
    }

    public function getMainGolongan()
    {
        return $this->MainGolonganRepository->getMainGolongan();
    }

    public function getMainGolonganTable()
    {
        $mainGolongan = $this->MainGolonganRepository->getMainGolongan()->load('golongan');

        $dataMainGolongan = [];
        foreach ($mainGolongan as $r) {
            $dataMainGolongan[] = [
                'id' => $r->id,
                'golongan_id' => $r->golongan->nama ?? '-',
                'kode' => $r->kode,
                'nama' => $r->nama,
                'keterangan' => $r->keterangan ?? '-',
            ];
        }

        return $dataMainGolongan;
    }

    public function createMainGolongan(array $data)
    {
        return $this->MainGolonganRepository->createMainGolongan($data);
    }

    public function findByIdMainGolongan($id)
    {
        return $this->MainGolonganRepository->findByIdMainGolongan($id);
    }

    public function updateMainGolongan($id, array $data)
    {
        $mainGolongan = $this->MainGolonganRepository->findByIdMainGolongan($id);
        $mainGolongan->update($data);

        return $mainGolongan;
    }

    public function deleteMainGolongan($id)
    {
        return $this->MainGolonganRepository->findByIdMainGolongan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            return Excel::download(new MainGolonganObatExport, 'Template_MainGolongan_Obat.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export template Main Golongan Obat: ' . $e->getMessage());

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

            foreach (array_slice($rows, 1) as $row) {
                $golonganKode = trim((string) ($row['A'] ?? ''));
                $kode = trim((string) ($row['B'] ?? ''));
                $nama = trim((string) ($row['C'] ?? ''));
                $keterangan = trim((string) ($row['D'] ?? ''));

                if (!$golonganKode || !$kode || !$nama) {
                    continue;
                }

                $golongan = GolonganModel::where('kode', $golonganKode)->first();
                $exists = MainGolonganModel::where('kode', $kode)->exists();

                if (!$golongan || $exists) {
                    $skipped++;
                    continue;
                }

                MainGolonganModel::create([
                    'golongan_id' => $golongan->id,
                    'kode' => $kode,
                    'nama' => $nama,
                    'keterangan' => $keterangan ?: null,
                ]);

                $added++;
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
