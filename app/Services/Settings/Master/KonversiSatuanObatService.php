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
use Illuminate\Validation\ValidationException;
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
        return $this->getObatKonversiTable();
    }

    public function getObatKonversiTable(string $status = 'all')
    {
        $obats = $this->KonversiSatuanObatRepository->getObatWithKonversi();

        if ($status === 'with') {
            $obats = $obats->filter(fn ($obat) => $obat->konversiSatuan->isNotEmpty());
        }

        if ($status === 'without') {
            $obats = $obats->filter(fn ($obat) => $obat->konversiSatuan->isEmpty());
        }

        return $obats
            ->values()
            ->map(fn ($obat) => $this->formatObatKonversiRow($obat))
            ->all();
    }

    public function getKonversiSummary(): array
    {
        $totalObat = MasterObatModel::count();
        $sudahKonversi = MasterObatModel::has('konversiSatuan')->count();

        return [
            'total_obat' => $totalObat,
            'sudah_konversi' => $sudahKonversi,
            'belum_konversi' => max(0, $totalObat - $sudahKonversi),
            'total_konversi' => KonversiSatuanModel::count(),
        ];
    }

    public function getObatKonversiDetail($obatId): array
    {
        $obat = MasterObatModel::with([
            'satuan',
            'konversiSatuan' => function ($query) {
                $query->with('satuan')
                    ->orderByDesc('is_default')
                    ->orderBy('id');
            },
        ])->findOrFail($obatId);

        return $this->formatObatKonversiRow($obat);
    }

    private function formatObatKonversiRow(MasterObatModel $obat): array
    {
        $satuanStok = $obat->satuan->nama ?? 'PCS';
        $conversions = $obat->konversiSatuan->map(function ($konversi) use ($satuanStok) {
            $satuanNama = $konversi->satuan->nama ?? '-';

            return [
                'id' => $konversi->id,
                'obat_id' => $konversi->obat_id,
                'satuan_id' => $konversi->satuan_id,
                'satuan_nama' => $satuanNama,
                'konversi' => $konversi->konversi,
                'is_default' => (int) $konversi->is_default,
                'label' => '1 ' . $satuanNama . ' = ' . $konversi->konversi . ' ' . $satuanStok,
            ];
        })->values();

        $default = $conversions->firstWhere('is_default', 1);
        $hasKonversi = $conversions->isNotEmpty();

        return [
            'id' => $obat->id,
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'satuan_stok' => $satuanStok,
            'is_active' => (int) $obat->is_active,
            'conversion_count' => $conversions->count(),
            'has_konversi' => $hasKonversi,
            'status_key' => $hasKonversi ? 'with' : 'without',
            'status_label' => $hasKonversi ? 'Sudah Ada' : 'Belum Ada',
            'conversion_summary' => $hasKonversi
                ? $conversions->pluck('label')->implode(' | ')
                : 'Belum ada konversi satuan',
            'default_conversion' => $default['label'] ?? null,
            'conversions' => $conversions->all(),
            'search_text' => trim(implode(' ', [
                $obat->kode_obat,
                $obat->nama_obat,
                $satuanStok,
                $hasKonversi ? 'sudah ada konversi' : 'belum ada konversi',
                $conversions->pluck('label')->implode(' '),
            ])),
        ];
    }

    public function syncKonversiForObat($obatId, array $data): array
    {
        $obat = MasterObatModel::findOrFail($obatId);
        $satuanIds = array_values($data['satuan_id'] ?? []);
        $konversiValues = array_values($data['konversi'] ?? []);
        $conversionIds = array_values($data['conversion_id'] ?? []);
        $defaultValues = array_values($data['is_default'] ?? []);

        $filledSatuan = array_filter($satuanIds, fn ($value) => $value !== null && $value !== '');

        if (count($filledSatuan) !== count(array_unique($filledSatuan))) {
            throw ValidationException::withMessages([
                'satuan_id' => 'Satuan pembelian tidak boleh sama untuk obat yang sama.',
            ]);
        }

        DB::transaction(function () use ($obat, $satuanIds, $konversiValues, $conversionIds, $defaultValues) {
            $defaultIndex = null;

            foreach ($defaultValues as $index => $value) {
                if ((int) $value === 1) {
                    $defaultIndex = $index;
                    break;
                }
            }

            if ($defaultIndex !== null) {
                KonversiSatuanModel::where('obat_id', $obat->id)->update(['is_default' => 0]);
            }

            foreach ($satuanIds as $index => $satuanId) {
                $payload = [
                    'obat_id' => $obat->id,
                    'satuan_id' => $satuanId,
                    'konversi' => $konversiValues[$index] ?? 1,
                    'is_default' => $defaultIndex === $index ? 1 : 0,
                ];

                $conversionId = $conversionIds[$index] ?? null;

                if ($conversionId) {
                    $conversion = KonversiSatuanModel::where('obat_id', $obat->id)
                        ->where('id', $conversionId)
                        ->firstOrFail();
                    $conversion->update($payload);

                    continue;
                }

                KonversiSatuanModel::updateOrCreate(
                    [
                        'obat_id' => $obat->id,
                        'satuan_id' => $satuanId,
                    ],
                    $payload
                );
            }
        });

        return $this->getObatKonversiDetail($obat->id);
    }

    public function ensureSingleDefault($obatId, $activeConversionId): void
    {
        KonversiSatuanModel::where('obat_id', $obatId)
            ->where('id', '!=', $activeConversionId)
            ->update(['is_default' => 0]);
    }

    public function createOrUpdateKonversi($data)
    {
        $konversi = KonversiSatuanModel::updateOrCreate(
            [
                'obat_id' => $data['obat_id'],
                'satuan_id' => $data['satuan_id'],
            ],
            $data
        );

        if ((int) ($data['is_default'] ?? 0) === 1) {
            $this->ensureSingleDefault($konversi->obat_id, $konversi->id);
        }

        return $konversi;
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
        $konversi = $this->KonversiSatuanObatRepository->findByIdKonversi($id);
        $konversi->update($data);

        if ((int) ($data['is_default'] ?? 0) === 1) {
            $this->ensureSingleDefault($konversi->obat_id, $konversi->id);
        }

        return $konversi;
    }

    public function createKonversi($data)
    {
        return $this->createOrUpdateKonversi($data);
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

                $obat_id = MasterObatModel::where('nama_obat', $obat)
                    ->orWhere('kode_obat', $obat)
                    ->first();
                $satuan_id = SatuansModel::where('nama', $satuan)
                    ->orWhere('kode', $satuan)
                    ->first();

                if (!$obat_id || !$satuan_id) {
                    $skipped++;
                    continue;
                }

                $exists = KonversiSatuanModel::where('obat_id', $obat_id->id)
                    ->where('satuan_id', $satuan_id->id)
                    ->exists();

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
