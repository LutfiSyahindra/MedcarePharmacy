<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\MasterObat\MasterObatExport;
use App\Models\CategoryModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\MainCategoryModel;
use App\Models\MasterObatModel;
use App\Models\PabrikanModel;
use App\Models\RakPenyimpananModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Models\SubCategoryModel;
use App\Repositories\Settings\Master\MasterObatRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MasterObatService
{
    protected $MasterObatRepository;

    public function __construct(MasterObatRepository $MasterObatRepository)
    {
        $this->MasterObatRepository = $MasterObatRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getMasterObat()
    {
        $dataMasterObat = $this->MasterObatRepository->getMasterObat();
        return $dataMasterObat;
    }

    public function getSubKategori($kategori)
    {
        $subKategoris = $this->MasterObatRepository->getSubKategori($kategori);
        return $subKategoris;
    }

    public function getMainKategori($kategoriUtama)
    {
        $mainKategori = $this->MasterObatRepository->getMainKategori($kategoriUtama);
        return $mainKategori;
    }

    public function getMasterObatTable()
    {
        $MasterObat = $this->MasterObatRepository->getMasterObat()->load(['kategoriUtama', 'kategori', 'subKategori', 'golongan', 'satuan', 'sediaan', 'pabrikan', 'distributor', 'rakPenyimpanan']);

        $dataMasterObat = [];
        foreach ($MasterObat as $r) {
            $dataMasterObat[] = [
                'id'        => $r->id,
                'kode_obat' => $r->kode_obat,
                'nama_obat'      => $r->nama_obat,
                'category_id' => $r->kategoriUtama->name ?? '-',
                'main_category_id' => $r->kategori->name ?? '-',
                'sub_kategori_id'   => $r->subKategori->name ?? '-',
                'golongan_id'   => $r->golongan->nama ?? '-',
                'satuan_id'   => $r->satuan->nama ?? '-',
                'sediaan_id'   => $r->sediaan->nama ?? '-',
                'pabrikan_id'   => $r->pabrikan->nama ?? '-',
                'distributor_id'   => $r->distributor->nama ?? '-',
                'rak_id'   => $r->rakPenyimpanan->nama ?? '-',
                'kemasan'   => $r->kemasan ?? '-',
                'komposisi'   => $r->komposisi ?? '-',
                'indikasi'   => $r->indikasi ?? '-',
                'dosis'   => $r->dosis ?? '-',
                'stok_minimum'   => $r->stok_minimum ?? '-',
                'harga_beli'   => $r->harga_beli ?? '-',
                'tgl_kadaluarsa'   => $r->tgl_kadaluarsa ?? '-',
                'no_batch'   => $r->no_batch ?? '-',
                'jenis'   => $r->is_generik == 1 ? 'Generik' : 'Paten',
                'is_active' => $r->is_active,
            ];
        }

        return $dataMasterObat;
    }

    public function createMasterObat($data)
    {
        $dataMasterObat = $this->MasterObatRepository->createMasterObat($data);
        return $dataMasterObat;
    }

    public function findByIdMasterObat($id)
    {
        $MasterObat = $this->MasterObatRepository->findByIdMasterObat($id);
        return $MasterObat;
    }

    public function updateMasterObat($id, array $data)
    {
        $MasterObat = $this->MasterObatRepository->findByIdMasterObat($id);
        $MasterObat->update($data);
        return $MasterObat;
    }

    public function updateStatus($id, $status)
    {
        return $this->MasterObatRepository->updateStatus($id, $status);
    }

    public function deleteMasterObat($id)
    {
        return $this->MasterObatRepository->findByIdMasterObat($id)->delete();
    }

    public function exportTemplate()
    {
        try {
            $fileName = 'Template_MasterObat_Obat.xlsx';

            // Bisa langsung dikembalikan ke controller
            return Excel::download(new MasterObatExport, $fileName);
        } catch (Exception $e) {
            Log::error('Gagal export template MasterObat Obat: ' . $e->getMessage());
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
                $kode_obat = trim((string) ($row['A'] ?? ''));
                $nama_obat = trim((string) ($row['B'] ?? ''));
                $category_id = trim((string) ($row['C'] ?? ''));
                $main_category_id = trim((string) ($row['D'] ?? ''));
                $sub_kategori_id = trim((string) ($row['E'] ?? ''));
                $golongan_id = trim((string) ($row['F'] ?? ''));
                $satuan_id = trim((string) ($row['G'] ?? ''));
                $sediaan_id = trim((string) ($row['H'] ?? ''));
                $pabrikan_id = trim((string) ($row['I'] ?? ''));
                $distributor_id = trim((string) ($row['J'] ?? ''));
                $rak_id = trim((string) ($row['K'] ?? ''));
                $komposisi = trim((string) ($row['L'] ?? ''));
                $indikasi = trim((string) ($row['M'] ?? ''));
                $dosis = trim((string) ($row['N'] ?? ''));
                $kemasan = trim((string) ($row['O'] ?? ''));
                $stok_minimum = trim((string) ($row['P'] ?? '')) ?: 0;
                $harga_beli = trim((string) ($row['Q'] ?? '')) ?: 0;
                $is_generik = $this->normalizeBoolean($row['R'] ?? 1, true);
                $is_active = $this->normalizeBoolean($row['S'] ?? 1, true);

                if (!$kode_obat || !$nama_obat) continue; // lewati baris kosong

                // cek duplikat
                $exists = MasterObatModel::where('kode_obat', $kode_obat)->exists();

                // cari
                $category_id = CategoryModel::where('code', $category_id)->first();
                $main_category_id = MainCategoryModel::where('code', $main_category_id)->first();
                $sub_kategori_id = SubCategoryModel::where('code', $sub_kategori_id)->first();
                $golongan_id = GolonganModel::where('kode', $golongan_id)->first();
                $satuan_id = SatuansModel::where('kode', $satuan_id)->first();
                $sediaan_id = SediaanModel::where('kode', $sediaan_id)->first();
                $pabrikan_id = PabrikanModel::where('kode', $pabrikan_id)->first();
                $distributor_id = DistributorModel::where('kode', $distributor_id)->first();
                $rak_id = RakPenyimpananModel::where('kode', $rak_id)->first();

                if ($exists) {
                    $skipped++;
                } else {
                    MasterObatModel::create([
                        'kode_obat' => $kode_obat,
                        'nama_obat' => $nama_obat,
                        'category_id' => $category_id?->id,
                        'main_category_id' => $main_category_id?->id,
                        'sub_kategori_id' => $sub_kategori_id?->id,
                        'golongan_id' => $golongan_id?->id,
                        'satuan_id' => $satuan_id?->id,
                        'sediaan_id' => $sediaan_id?->id,
                        'pabrikan_id' => $pabrikan_id?->id,
                        'distributor_id' => $distributor_id?->id,
                        'rak_id' => $rak_id?->id,
                        'komposisi' => $komposisi ?: null,
                        'indikasi' => $indikasi ?: null,
                        'dosis' => $dosis ?: null,
                        'kemasan' => $kemasan ?: null,
                        'stok_minimum' => $stok_minimum ?: 0,
                        'harga_beli' => $harga_beli ?: 0,
                        'is_generik' => $is_generik,
                        'is_active' => $is_active,
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

    private function normalizeBoolean($value, bool $default = true): bool
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'true', 'ya', 'yes', 'aktif', 'generik'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'tidak', 'no', 'nonaktif', 'paten'], true)) {
            return false;
        }

        return $default;
    }
}
