<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\golongan\SubGolonganObatExport;
use App\Models\MainGolonganModel;
use App\Models\SubGolonganModel;
use App\Repositories\Settings\Master\SubGolonganRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SubGolonganService
{
    protected $SubGolonganRepository;

    public function __construct(SubGolonganRepository $SubGolonganRepository)
    {
        $this->SubGolonganRepository = $SubGolonganRepository;
    }

    public function getSubGolongan()
    {
        return $this->SubGolonganRepository->getSubGolongan();
    }

    public function getSubGolonganTable()
    {
        $subGolongan = $this->SubGolonganRepository->getSubGolongan()->load('mainGolongan');

        $dataSubGolongan = [];
        foreach ($subGolongan as $r) {
            $dataSubGolongan[] = [
                'id' => $r->id,
                'main_golongan_id' => $r->mainGolongan->nama ?? '-',
                'kode' => $r->kode,
                'nama' => $r->nama,
                'keterangan' => $r->keterangan ?? '-',
            ];
        }

        return $dataSubGolongan;
    }

    public function createSubGolongan(array $data)
    {
        return $this->SubGolonganRepository->createSubGolongan($data);
    }

    public function findByIdSubGolongan($id)
    {
        return $this->SubGolonganRepository->findByIdSubGolongan($id);
    }

    public function updateSubGolongan($id, array $data)
    {
        $subGolongan = $this->SubGolonganRepository->findByIdSubGolongan($id);
        $subGolongan->update($data);

        return $subGolongan;
    }

    public function deleteSubGolongan($id)
    {
        return $this->SubGolonganRepository->findByIdSubGolongan($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            return Excel::download(new SubGolonganObatExport, 'Template_SubGolongan_Obat.xlsx');
        } catch (Exception $e) {
            Log::error('Gagal export template Sub Golongan Obat: ' . $e->getMessage());

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
                $mainGolonganKode = trim((string) ($row['A'] ?? ''));
                $kode = trim((string) ($row['B'] ?? ''));
                $nama = trim((string) ($row['C'] ?? ''));
                $keterangan = trim((string) ($row['D'] ?? ''));

                if (!$mainGolonganKode || !$kode || !$nama) {
                    continue;
                }

                $mainGolongan = MainGolonganModel::where('kode', $mainGolonganKode)->first();
                $exists = SubGolonganModel::where('kode', $kode)->exists();

                if (!$mainGolongan || $exists) {
                    $skipped++;
                    continue;
                }

                SubGolonganModel::create([
                    'main_golongan_id' => $mainGolongan->id,
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
