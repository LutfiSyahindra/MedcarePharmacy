<?php

namespace App\Services\Settings\Master;

use App\Exports\MasterData\MasterObat\MasterObatExport;
use App\Models\CategoryModel;
use App\Models\DistributorModel;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\PabrikanModel;
use App\Models\RakPenyimpananModel;
use App\Models\SatuansModel;
use App\Models\SediaanModel;
use App\Models\SubGolonganModel;
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

    public function getMainGolongan($golongan)
    {
        return $this->MasterObatRepository->getMainGolongan($golongan);
    }

    public function getSubGolongan($mainGolongan)
    {
        return $this->MasterObatRepository->getSubGolongan($mainGolongan);
    }

    public function getFilterOptions(): array
    {
        $summary = MasterObatModel::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN category_id IS NULL THEN 1 ELSE 0 END) as categories_without_value')
            ->selectRaw('SUM(CASE WHEN golongan_id IS NULL THEN 1 ELSE 0 END) as golongan_without_value')
            ->selectRaw('SUM(CASE WHEN main_golongan_id IS NULL THEN 1 ELSE 0 END) as main_golongan_without_value')
            ->selectRaw('SUM(CASE WHEN sub_golongan_id IS NULL THEN 1 ELSE 0 END) as sub_golongan_without_value')
            ->first();

        $categories = CategoryModel::query()
            ->join('master_obats', 'master_obats.category_id', '=', 'categories.id')
            ->select('categories.id', 'categories.code', 'categories.name')
            ->selectRaw('COUNT(master_obats.id) as item_count')
            ->groupBy('categories.id', 'categories.code', 'categories.name')
            ->orderBy('categories.name')
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'count' => (int) $item->item_count,
            ])
            ->values()
            ->all();

        $golongan = GolonganModel::query()
            ->join('master_obats', 'master_obats.golongan_id', '=', 'golongan_obats.id')
            ->select('golongan_obats.id', 'golongan_obats.kode', 'golongan_obats.nama')
            ->selectRaw('COUNT(master_obats.id) as item_count')
            ->groupBy('golongan_obats.id', 'golongan_obats.kode', 'golongan_obats.nama')
            ->orderBy('golongan_obats.nama')
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->id,
                'code' => $item->kode,
                'name' => $item->nama,
                'count' => (int) $item->item_count,
            ])
            ->values()
            ->all();

        $mainGolongan = MainGolonganModel::query()
            ->join('master_obats', 'master_obats.main_golongan_id', '=', 'main_golongan_obats.id')
            ->leftJoin('golongan_obats', 'golongan_obats.id', '=', 'main_golongan_obats.golongan_id')
            ->select(
                'main_golongan_obats.id',
                'main_golongan_obats.kode',
                'main_golongan_obats.nama',
                'golongan_obats.nama as golongan_name'
            )
            ->selectRaw('COUNT(master_obats.id) as item_count')
            ->groupBy(
                'main_golongan_obats.id',
                'main_golongan_obats.kode',
                'main_golongan_obats.nama',
                'golongan_obats.nama'
            )
            ->orderBy('golongan_obats.nama')
            ->orderBy('main_golongan_obats.nama')
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->id,
                'code' => $item->kode,
                'name' => $item->nama,
                'golongan_name' => $item->golongan_name,
                'count' => (int) $item->item_count,
            ])
            ->values()
            ->all();

        $subGolongan = SubGolonganModel::query()
            ->join('master_obats', 'master_obats.sub_golongan_id', '=', 'sub_golongan_obats.id')
            ->leftJoin('main_golongan_obats', 'main_golongan_obats.id', '=', 'sub_golongan_obats.main_golongan_id')
            ->leftJoin('golongan_obats', 'golongan_obats.id', '=', 'main_golongan_obats.golongan_id')
            ->select(
                'sub_golongan_obats.id',
                'sub_golongan_obats.kode',
                'sub_golongan_obats.nama',
                'main_golongan_obats.nama as main_golongan_name',
                'golongan_obats.nama as golongan_name'
            )
            ->selectRaw('COUNT(master_obats.id) as item_count')
            ->groupBy(
                'sub_golongan_obats.id',
                'sub_golongan_obats.kode',
                'sub_golongan_obats.nama',
                'main_golongan_obats.nama',
                'golongan_obats.nama'
            )
            ->orderBy('golongan_obats.nama')
            ->orderBy('main_golongan_obats.nama')
            ->orderBy('sub_golongan_obats.nama')
            ->get()
            ->map(fn ($item) => [
                'id' => (string) $item->id,
                'code' => $item->kode,
                'name' => $item->nama,
                'golongan_name' => $item->golongan_name,
                'main_golongan_name' => $item->main_golongan_name,
                'count' => (int) $item->item_count,
            ])
            ->values()
            ->all();

        $this->appendEmptyClassificationOption(
            $categories,
            (int) ($summary?->categories_without_value ?? 0),
            'Tanpa kategori'
        );
        $this->appendEmptyClassificationOption(
            $golongan,
            (int) ($summary?->golongan_without_value ?? 0),
            'Tanpa golongan'
        );
        $this->appendEmptyClassificationOption(
            $mainGolongan,
            (int) ($summary?->main_golongan_without_value ?? 0),
            'Tanpa main golongan'
        );
        $this->appendEmptyClassificationOption(
            $subGolongan,
            (int) ($summary?->sub_golongan_without_value ?? 0),
            'Tanpa sub golongan'
        );

        return [
            'total' => (int) ($summary?->total ?? 0),
            'filters' => [
                'categories' => $categories,
                'golongan' => $golongan,
                'main_golongan' => $mainGolongan,
                'sub_golongan' => $subGolongan,
            ],
        ];
    }

    public function getMasterObatTable()
    {
        $MasterObat = $this->MasterObatRepository->getMasterObat()->load(['kategori', 'golongan', 'mainGolongan', 'subGolongan', 'satuan', 'sediaan', 'pabrikan', 'distributor', 'rakPenyimpanan']);

        $dataMasterObat = [];
        foreach ($MasterObat as $r) {
            $dataMasterObat[] = [
                'id'        => $r->id,
                'kode_obat' => $r->kode_obat,
                'nama_obat'      => $r->nama_obat,
                'category_id' => $r->kategori->name ?? '-',
                'golongan_id'   => $r->golongan->nama ?? '-',
                'main_golongan_id' => $r->mainGolongan->nama ?? '-',
                'sub_golongan_id' => $r->subGolongan->nama ?? '-',
                'golongan_display' => $r->subGolongan->nama
                    ?? $r->mainGolongan->nama
                    ?? $r->golongan->nama
                    ?? '-',
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
                'category_filter' => $this->classificationFilterToken('category', $r->category_id),
                'golongan_filter' => $this->classificationFilterToken('golongan', $r->golongan_id),
                'main_golongan_filter' => $this->classificationFilterToken('main_golongan', $r->main_golongan_id),
                'sub_golongan_filter' => $this->classificationFilterToken('sub_golongan', $r->sub_golongan_id),
            ];
        }

        return $dataMasterObat;
    }

    public function createMasterObat(array $data)
    {
        return DB::transaction(function () use ($data) {
            $prefix = $this->normalizeCodePrefix((string) $data['kode_prefix']);
            unset($data['kode_prefix']);
            $data['kode_obat'] = $this->nextMedicineCode($prefix, true);

            return $this->MasterObatRepository->createMasterObat($data);
        }, 5);
    }

    public function previewNextCode(string $prefix): string
    {
        return $this->nextMedicineCode($this->normalizeCodePrefix($prefix));
    }

    public function getCodePrefixes(): array
    {
        return MasterObatModel::query()
            ->pluck('kode_obat')
            ->map(function ($code) {
                return preg_match('/^([A-Z]{3})/i', trim((string) $code), $matches)
                    ? strtoupper($matches[1])
                    : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($prefix) => [
                'id' => $prefix,
                'text' => $prefix,
            ])
            ->all();
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
                $golongan_id = trim((string) ($row['D'] ?? ''));
                $main_golongan_id = trim((string) ($row['E'] ?? ''));
                $sub_golongan_id = trim((string) ($row['F'] ?? ''));
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
                $golongan_id = GolonganModel::where('kode', $golongan_id)->first();
                $main_golongan_id = MainGolonganModel::where('kode', $main_golongan_id)->first();
                $sub_golongan_id = SubGolonganModel::where('kode', $sub_golongan_id)->first();
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
                        'golongan_id' => $golongan_id?->id,
                        'main_golongan_id' => $main_golongan_id?->id,
                        'sub_golongan_id' => $sub_golongan_id?->id,
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

    private function normalizeCodePrefix(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        if (! preg_match('/^[A-Z]{3}$/', $prefix)) {
            throw new \InvalidArgumentException('Awalan kode obat harus terdiri dari tepat 3 huruf.');
        }

        return $prefix;
    }

    private function nextMedicineCode(string $prefix, bool $lockForUpdate = false): string
    {
        $query = MasterObatModel::query()
            ->where('kode_obat', 'like', $prefix.'%');

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $highestNumber = 0;
        $numberWidth = 3;
        $pattern = '/^'.preg_quote($prefix, '/').'-?(\d+)$/';

        foreach ($query->pluck('kode_obat') as $code) {
            if (! preg_match($pattern, (string) $code, $matches)) {
                continue;
            }

            $number = (int) $matches[1];

            if ($number > $highestNumber) {
                $highestNumber = $number;
                $numberWidth = max(3, strlen($matches[1]));
            } elseif ($number === $highestNumber) {
                $numberWidth = max($numberWidth, strlen($matches[1]));
            }
        }

        $nextNumber = $highestNumber + 1;
        $numberWidth = max($numberWidth, strlen((string) $nextNumber));

        return $prefix.'-'.str_pad((string) $nextNumber, $numberWidth, '0', STR_PAD_LEFT);
    }

    private function appendEmptyClassificationOption(array &$options, int $count, string $name): void
    {
        if ($count === 0) {
            return;
        }

        $options[] = [
            'id' => '__none__',
            'code' => null,
            'name' => $name,
            'count' => $count,
            'is_empty' => true,
        ];
    }

    private function classificationFilterToken(string $classification, $id): string
    {
        return sprintf('|%s:%s|', $classification, $id ?: 'none');
    }
}
