<?php

namespace App\Services\Settings\Master;

use App\Exports\Menu\Konversi\KonversiExport;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Repositories\Settings\Master\KonversiSatuanObatRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class KonversiSatuanObatService
{
    private const UPDATABLE_PO_STATUSES = ['draft', 'waiting_approval', 'approved'];

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

    public function getObatKonversiTable(string $status = 'all', array $branchIds = [])
    {
        $query = $this->KonversiSatuanObatRepository->obatWithKonversiQuery();
        $this->applyStatusFilter($query, $status, $branchIds);
        $obats = $query->get();
        $purchaseOrders = $this->purchaseOrderReferences($obats->modelKeys(), $branchIds);
        $missingUnits = $this->missingPurchaseOrderUnitCounts($obats->modelKeys(), $branchIds);

        return $obats
            ->values()
            ->map(fn ($obat) => $this->formatObatKonversiRow($obat, $purchaseOrders[$obat->id] ?? [], $missingUnits[$obat->id] ?? 0))
            ->all();
    }

    public function getKonversiSummary(array $branchIds = []): array
    {
        $totalObat = MasterObatModel::count();
        $sudahKonversi = MasterObatModel::has('konversiSatuan')->count();

        return [
            'total_obat' => $totalObat,
            'sudah_konversi' => $sudahKonversi,
            'belum_konversi' => max(0, $totalObat - $sudahKonversi),
            'total_konversi' => KonversiSatuanModel::count(),
            'po_belum_konversi' => $this->poWithoutConversionQuery($branchIds)->count(),
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

    private function formatObatKonversiRow(MasterObatModel $obat, array $purchaseOrders = [], int $missingPoUnits = 0): array
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
            'satuan_stok_id' => $obat->satuan_id,
            'purchase_orders' => $purchaseOrders,
            'po_count' => count($purchaseOrders),
            'po_missing_unit_count' => $missingPoUnits,
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
                implode(' ', $purchaseOrders),
                $hasKonversi ? 'sudah ada konversi' : 'belum ada konversi',
                $conversions->pluck('label')->implode(' '),
            ])),
        ];
    }

    private function purchaseOrderMedicineIds(array $branchIds)
    {
        return DB::table('purchase_order_details as details')
            ->join('purchase_orders as orders', 'orders.id', '=', 'details.purchase_order_id')
            ->whereIn('orders.branch_id', $branchIds)
            ->select('details.obat_id');
    }

    private function purchaseOrderReferences(array $medicineIds, array $branchIds): array
    {
        return DB::table('purchase_order_details as details')
            ->join('purchase_orders as orders', 'orders.id', '=', 'details.purchase_order_id')
            ->whereIn('orders.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->select('details.obat_id', 'orders.id', 'orders.no_po')
            ->distinct()
            ->orderByDesc('orders.id')
            ->get()
            ->groupBy('obat_id')
            ->map(fn ($orders) => $orders->pluck('no_po')->all())
            ->all();
    }

    private function missingPurchaseOrderUnitCounts(array $medicineIds, array $branchIds): array
    {
        return DB::table('purchase_order_details as details')
            ->join('purchase_orders as orders', 'orders.id', '=', 'details.purchase_order_id')
            ->whereIn('orders.branch_id', $branchIds)
            ->whereIn('details.obat_id', $medicineIds)
            ->whereNull('details.satuan_konversi')
            ->selectRaw('details.obat_id, COUNT(*) as missing_count')
            ->groupBy('details.obat_id')
            ->pluck('missing_count', 'obat_id')->map(fn ($count) => (int) $count)->all();
    }

    private function poWithoutConversionQuery(array $branchIds)
    {
        $query = MasterObatModel::query();
        $this->applyStatusFilter($query, 'po_without', $branchIds);

        return $query;
    }

    private function applyStatusFilter($query, string $status, array $branchIds): void
    {
        if ($status === 'with') {
            $query->has('konversiSatuan');
        }

        if ($status === 'without') {
            $query->doesntHave('konversiSatuan');
        }

        if ($status === 'po_without') {
            $missingUnits = $this->purchaseOrderMedicineIds($branchIds)
                ->whereNull('details.satuan_konversi')
                ->whereIn('orders.status', self::UPDATABLE_PO_STATUSES)
                ->whereNotExists(function ($receiptQuery) {
                    $receiptQuery->selectRaw('1')->from('penerimaan_barang as receipts')
                        ->whereColumn('receipts.purchase_order_id', 'orders.id');
                });
            $query->whereIn('id', $this->purchaseOrderMedicineIds($branchIds))
                ->where(function ($medicineQuery) use ($missingUnits) {
                    $medicineQuery->doesntHave('konversiSatuan')->orWhereIn('id', $missingUnits);
                });
        }
    }

    private function batchTargetQuery(array $data, array $branchIds)
    {
        $stockUnits = $data['satuan_stok_ids'] ?? [];
        $medicineIds = $data['obat_ids'] ?? [];
        $scope = $data['scope'] ?? 'all';

        if (empty($stockUnits) && empty($medicineIds) && $scope !== 'po_without') {
            throw ValidationException::withMessages([
                'obat_ids' => 'Pilih satuan stok atau obat untuk menentukan target batch.',
            ]);
        }

        $query = $this->KonversiSatuanObatRepository->obatWithKonversiQuery();
        $this->applyStatusFilter($query, $scope, $branchIds);

        if ($stockUnits) {
            $query->whereIn('satuan_id', $stockUnits);
        }

        if ($medicineIds) {
            $query->whereIn('id', $medicineIds);
        }

        return $query;
    }

    public function previewBatch(array $data, array $branchIds): array
    {
        $medicines = $this->batchTargetQuery($data, $branchIds)->get();
        $purchaseOrders = $this->purchaseOrderReferences($medicines->modelKeys(), $branchIds);
        $missingUnits = $this->missingPurchaseOrderUnitCounts($medicines->modelKeys(), $branchIds);

        return $medicines->map(fn ($medicine) => $this->formatObatKonversiRow(
            $medicine, $purchaseOrders[$medicine->id] ?? [], $missingUnits[$medicine->id] ?? 0
        ))->all();
    }

    public function storeBatch(array $data, array $branchIds): array
    {
        return DB::transaction(function () use ($data, $branchIds) {
            $medicines = $this->batchTargetQuery($data, $branchIds)->lockForUpdate()->get();
            $targetIds = collect($data['target_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();
            $currentIds = collect($medicines->modelKeys())->sort()->values()->all();

            if (empty($currentIds) || $targetIds !== $currentIds) {
                throw ValidationException::withMessages([
                    'target_ids' => 'Target obat berubah atau kosong. Muat ulang pratinjau sebelum menyimpan.',
                ]);
            }

            $conversions = $data['conversions'];
            if (collect($conversions)->where('is_default', 1)->count() > 1) {
                throw ValidationException::withMessages([
                    'conversions' => 'Hanya satu satuan konversi yang boleh dijadikan default.',
                ]);
            }

            foreach ($medicines as $medicine) {
                foreach ($conversions as $conversion) {
                    if ((int) $medicine->satuan_id === (int) $conversion['satuan_id'] && (int) $conversion['konversi'] !== 1) {
                        throw ValidationException::withMessages([
                            'conversions' => 'Konversi untuk satuan stok harus bernilai 1. Periksa obat '.$medicine->nama_obat.'.',
                        ]);
                    }
                }
            }

            $added = 0;
            $skipped = 0;
            $updatedMedicines = 0;
            foreach ($medicines as $medicine) {
                $medicineChanged = false;
                foreach ($conversions as $conversion) {
                    // Preserve factors for conversions already used by purchase orders.
                    $existing = $medicine->konversiSatuan->firstWhere('satuan_id', $conversion['satuan_id']);
                    if ($existing) {
                        if ((int) ($conversion['is_default'] ?? 0) === 1) {
                            $medicineChanged = $medicineChanged || (int) $existing->is_default !== 1;
                            $existing->update(['is_default' => 1]);
                            $this->ensureSingleDefault($medicine->id, $existing->id);
                        }
                        $skipped++;
                        continue;
                    }

                    $this->createKonversi([
                        'obat_id' => $medicine->id,
                        'satuan_id' => $conversion['satuan_id'],
                        'konversi' => $conversion['konversi'],
                        'is_default' => (int) ($conversion['is_default'] ?? 0),
                    ]);
                    $added++;
                    $medicineChanged = true;
                }
                $updatedMedicines += (int) $medicineChanged;
            }

            return [
                'target_count' => $medicines->count(), 'updated_medicines' => $updatedMedicines,
                'added' => $added, 'skipped' => $skipped,
                'po_sync' => $this->syncMissingPurchaseOrderUnits($medicines->modelKeys(), $branchIds),
            ];
        }, 3);
    }

    public function syncKonversiForObat($obatId, array $data, array $branchIds = []): array
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

        $poSync = DB::transaction(function () use ($obat, $satuanIds, $konversiValues, $conversionIds, $defaultValues, $branchIds) {
            MasterObatModel::whereKey($obat->id)->lockForUpdate()->firstOrFail();
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
                        ->lockForUpdate()
                        ->firstOrFail();
                    $this->assertConversionCanChange($conversion, $payload);
                    $conversion->update($payload);

                    continue;
                }

                $this->createOrUpdateKonversi($payload);
            }

            return $this->syncMissingPurchaseOrderUnits([$obat->id], $branchIds);
        }, 3);

        return [...$this->getObatKonversiDetail($obat->id), 'po_sync' => $poSync];
    }

    public function syncMissingPurchaseOrderUnits(array $medicineIds, array $branchIds): array
    {
        return DB::transaction(function () use ($medicineIds, $branchIds) {
            $summary = ['updated_items' => 0, 'updated_orders' => 0, 'protected_items' => 0, 'ambiguous_items' => 0];
            $conversions = KonversiSatuanModel::whereIn('obat_id', $medicineIds)
                ->orderBy('id')->lockForUpdate()->get()->groupBy('obat_id');
            $orders = PembelianModel::whereIn('branch_id', $branchIds)
                ->whereHas('details', fn ($query) => $query->whereIn('obat_id', $medicineIds)->whereNull('satuan_konversi'))
                ->orderBy('id')->lockForUpdate()->get();

            foreach ($orders as $order) {
                // Current reads under the PO lock also protect against concurrent receipt creation.
                $hasReceipt = PenerimaanBarangModel::where('purchase_order_id', $order->id)
                    ->orderBy('id')->lockForUpdate()->get(['id'])->isNotEmpty();
                $details = PembelianDetailModel::where('purchase_order_id', $order->id)
                    ->whereIn('obat_id', $medicineIds)->whereNull('satuan_konversi')
                    ->orderBy('id')->lockForUpdate()->get();
                $hasReceiptDetail = PenerimaanBarangDetailModel::whereIn('purchase_order_detail_id', $details->modelKeys())
                    ->orderBy('id')->lockForUpdate()->get(['id'])->isNotEmpty();

                if ($hasReceipt || $hasReceiptDetail || ! in_array($order->status, self::UPDATABLE_PO_STATUSES, true)) {
                    $summary['protected_items'] += $details->count();
                    continue;
                }

                $updatedOrder = false;
                foreach ($details as $detail) {
                    $units = $conversions->get($detail->obat_id, collect());
                    $defaults = $units->filter(fn ($unit) => (int) $unit->is_default === 1);
                    $conversion = $defaults->count() === 1 ? $defaults->first() : ($units->count() === 1 ? $units->first() : null);

                    if (! $conversion || (int) $conversion->konversi < 1) {
                        $summary['ambiguous_items']++;
                        continue;
                    }

                    // Only attach the purchase unit; preserve quantities, prices, taxes and totals.
                    $detail->update(['satuan_konversi' => $conversion->id]);
                    $summary['updated_items']++;
                    $updatedOrder = true;
                }
                $summary['updated_orders'] += (int) $updatedOrder;
            }

            return $summary;
        }, 3);
    }

    private function assertConversionCanChange(KonversiSatuanModel $conversion, array $data): void
    {
        $changesUnit = (int) $conversion->obat_id !== (int) ($data['obat_id'] ?? $conversion->obat_id)
            || (int) $conversion->satuan_id !== (int) ($data['satuan_id'] ?? $conversion->satuan_id)
            || (int) $conversion->konversi !== (int) ($data['konversi'] ?? $conversion->konversi);

        if ($changesUnit && PembelianDetailModel::where('satuan_konversi', $conversion->id)->lockForUpdate()->first(['id'])) {
            throw ValidationException::withMessages([
                'konversi' => 'Satuan dan isi konversi ini sudah dipakai PO sehingga tidak dapat diubah. Tambahkan satuan lain untuk konversi baru.',
            ]);
        }
    }

    public function ensureSingleDefault($obatId, $activeConversionId): void
    {
        KonversiSatuanModel::where('obat_id', $obatId)
            ->where('id', '!=', $activeConversionId)
            ->update(['is_default' => 0]);
    }

    public function createOrUpdateKonversi($data)
    {
        return DB::transaction(function () use ($data) {
            $konversi = KonversiSatuanModel::where('obat_id', $data['obat_id'])
                ->where('satuan_id', $data['satuan_id'])->lockForUpdate()->first();
            if ($konversi) {
                $this->assertConversionCanChange($konversi, $data);
                $konversi->update($data);
            } else {
                $konversi = KonversiSatuanModel::create($data);
            }

            if ((int) ($data['is_default'] ?? 0) === 1) {
                $this->ensureSingleDefault($konversi->obat_id, $konversi->id);
            }

            return $konversi;
        }, 3);
    }

    public function editKonversi($id, array $data)
    {
        return $this->updateKonversi($id, $data);
    }

    public function findKonversi($id)
    {
        return $this->KonversiSatuanObatRepository->findByIdKonversi($id);
    }

    public function updateKonversi($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $konversi = KonversiSatuanModel::whereKey($id)->lockForUpdate()->firstOrFail();
            $this->assertConversionCanChange($konversi, $data);
            $konversi->update($data);

            if ((int) ($data['is_default'] ?? 0) === 1) {
                $this->ensureSingleDefault($konversi->obat_id, $konversi->id);
            }

            return $konversi;
        }, 3);
    }

    public function createKonversi($data)
    {
        return $this->createOrUpdateKonversi($data);
    }

    public function deleteKonversi($id)
    {
        return DB::transaction(function () use ($id) {
            $conversion = KonversiSatuanModel::whereKey($id)->lockForUpdate()->firstOrFail();
            if (PembelianDetailModel::where('satuan_konversi', $id)->lockForUpdate()->first(['id'])) {
                throw ValidationException::withMessages([
                    'konversi' => 'Satuan konversi ini sudah dipakai PO sehingga tidak dapat dihapus.',
                ]);
            }

            return $conversion->delete();
        }, 3);
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

    public function importExcel($file, array $branchIds = [])
    {
        DB::beginTransaction();
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $added = 0;
            $skipped = 0;
            $medicineIds = [];

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

                $medicineIds[] = $obat_id->id;

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

            $poSync = $this->syncMissingPurchaseOrderUnits(array_unique($medicineIds), $branchIds);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import selesai!',
                'added' => $added,
                'skipped' => $skipped,
                'po_sync' => $poSync,
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
