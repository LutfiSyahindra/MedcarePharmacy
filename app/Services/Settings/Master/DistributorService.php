<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\Distributor\DistributorObatExport;
use App\Models\DistributorModel;
use App\Repositories\Settings\Master\DistributorRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DistributorService
{
    protected $DistributorRepository;

    public function __construct(DistributorRepository $DistributorRepository)
    {
        $this->DistributorRepository = $DistributorRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getDistributor()
    {
        $dataDistributor = $this->DistributorRepository->getDistributor();
        return $dataDistributor;
    }

    public function getDistributorTable()
    {
        $Distributor = $this->DistributorRepository->getDistributor();

        $dataDistributor = [];
        foreach ($Distributor as $r) {
            $dataDistributor[] = [
                'id'        => $r->id,
                'kode'      => $r->kode,
                'nama'      => $r->nama,
                'alamat'    => $r->alamat ?? '-',
                'telepon'   => $r->telepon ?? '-',
                'email'     => $r->email ?? '-',
                'is_active' => $r->is_active,
            ];
        }

        return $dataDistributor;
    }

    public function createDistributor(array $data)
    {
        $dataDistributor = $this->DistributorRepository->createDistributor($data);
        return $dataDistributor;
    }

    public function findByIdDistributor($id)
    {
        $Distributor = $this->DistributorRepository->findByIdDistributor($id);
        return $Distributor;
    }

    public function updateDistributor($id, array $data)
    {
        $Distributor = $this->DistributorRepository->findByIdDistributor($id);
        $Distributor->update($data);
        return $Distributor;
    }

    public function updateStatus($id, $status)
    {
        return $this->DistributorRepository->updateStatus($id, $status);
    }

    public function deleteDistributor($id)
    {
        return $this->DistributorRepository->findByIdDistributor($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_Distributor_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new DistributorObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template Distributor Obat: ' . $e->getMessage());
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
                $exists = DistributorModel::where('kode', $kode)->exists();

                if ($exists) {
                    $skipped++;
                } else {
                    DistributorModel::create([
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
