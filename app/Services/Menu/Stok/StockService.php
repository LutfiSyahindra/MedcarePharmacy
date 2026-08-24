<?php

namespace App\Services\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangDetailModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanBatchModel;
use App\Models\Menu\Penjualan\ReturPenjualanDetailModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\RiwayatHargaModel;
use App\Models\Menu\Stok\StockOpnameDetailModel;
use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\Menu\Stok\StockOpnameMovementModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Settings\Margins\MarginsService;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private readonly MarginsService $marginsService) {}

    public function recordReceipt(
        PenerimaanBarangModel $penerimaan,
        PenerimaanBarangDetailModel $detail,
        ?float $hargaJual = null,
        ?string $alasanHarga = null
    ): KartuStokModel {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $penerimaan->loadMissing('purchaseOrder');
        $branchId = (int) ($penerimaan->purchaseOrder?->branch_id ?: BranchAccess::requireUserBranchId());
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        $movement = $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => $hargaJual,
            'alasan_harga' => $alasanHarga ?: 'Posting penerimaan '.$penerimaan->nomor_penerimaan.' dari PO '.($penerimaan->purchaseOrder->no_po ?? '-'),
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'jenis_mutasi' => 'masuk',
            'tanggal_mutasi' => $penerimaan->posted_at ?: now(),
            'reference_type' => PenerimaanBarangModel::class,
            'reference_id' => $penerimaan->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $penerimaan->nomor_penerimaan,
            'keterangan' => 'Penerimaan barang dari PO '.($penerimaan->purchaseOrder->no_po ?? '-').' - '.$this->conversionNote($detail, $qtyStock),
            'created_by' => $penerimaan->posted_by ?: Auth::id(),
        ]);

        if (! $detail->stok_batch_id && $movement->stok_batch_id) {
            $detail->forceFill(['stok_batch_id' => $movement->stok_batch_id])->save();
        }

        return $movement;
    }

    public function reverseReceipt(PenerimaanBarangModel $penerimaan, PenerimaanBarangDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $penerimaan->loadMissing('purchaseOrder');
        $branchId = (int) ($penerimaan->purchaseOrder?->branch_id ?: BranchAccess::requireUserBranchId());
        $qtyStock = $this->receiptStockQuantity($detail);
        $basePrice = $this->receiptBasePrice($detail);

        return $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => null,
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'allow_identity_outbound' => true,
            'jenis_mutasi' => 'pembatalan_penerimaan',
            'tanggal_mutasi' => $penerimaan->cancelled_at ?: now(),
            'reference_type' => PenerimaanBarangModel::class,
            'reference_id' => $penerimaan->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $penerimaan->nomor_penerimaan,
            'keterangan' => 'Pembatalan penerimaan barang - '.$this->conversionNote($detail, $qtyStock),
            'created_by' => $penerimaan->cancelled_by ?: Auth::id(),
        ]);
    }

    public function recordPurchaseReturn(ReturPembelianModel $retur, ReturPembelianDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $retur->loadMissing(['purchaseOrder', 'penerimaanBarang']);
        $branchId = (int) ($retur->purchaseOrder?->branch_id ?: BranchAccess::requireUserBranchId());
        $qtyStock = $this->returnStockQuantity($detail);
        $basePrice = $this->returnBasePrice($detail);

        return $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => null,
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'jenis_mutasi' => 'retur_pembelian',
            'tanggal_mutasi' => $retur->posted_at ?: now(),
            'reference_type' => ReturPembelianModel::class,
            'reference_id' => $retur->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $retur->nomor_retur,
            'keterangan' => 'Retur pembelian ke supplier dari penerimaan '.($retur->penerimaanBarang->nomor_penerimaan ?? '-').' - '.$this->returnConversionNote($detail, $qtyStock),
            'created_by' => $retur->posted_by ?: Auth::id(),
        ]);
    }

    public function reversePurchaseReturn(ReturPembelianModel $retur, ReturPembelianDetailModel $detail): KartuStokModel
    {
        $detail->loadMissing(['purchaseOrderDetail.satuanKonversi.satuan', 'obat.satuan']);
        $retur->loadMissing(['purchaseOrder', 'penerimaanBarang']);
        $branchId = (int) ($retur->purchaseOrder?->branch_id ?: BranchAccess::requireUserBranchId());
        $qtyStock = $this->returnStockQuantity($detail);
        $basePrice = $this->returnBasePrice($detail);

        return $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $detail->no_batch,
            'expired_date' => $detail->expired_date,
            'qty' => $qtyStock,
            'harga_beli' => $basePrice,
            'harga_jual' => null,
            'diskon' => $detail->diskon ?? 0,
            'ppn' => $detail->ppn ?? 0,
            'preserve_batch_cost' => true,
            'jenis_mutasi' => 'pembatalan_retur_pembelian',
            'tanggal_mutasi' => $retur->cancelled_at ?: now(),
            'reference_type' => ReturPembelianModel::class,
            'reference_id' => $retur->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $retur->nomor_retur,
            'keterangan' => 'Pembatalan retur pembelian dari penerimaan '.($retur->penerimaanBarang->nomor_penerimaan ?? '-').' - '.$this->returnConversionNote($detail, $qtyStock),
            'created_by' => $retur->cancelled_by ?: Auth::id(),
        ]);
    }

    public function recordManualMutation(array $data): KartuStokModel
    {
        $jenisMutasi = $data['jenis_mutasi'];
        $isInbound = $this->isInboundMutation($jenisMutasi);

        $payload = [
            'branch_id' => BranchAccess::requireUserBranchId(),
            'obat_id' => $data['obat_id'],
            'stok_batch_id' => $data['stok_batch_id'] ?? null,
            'no_batch' => $data['no_batch'] ?? null,
            'expired_date' => $data['expired_date'] ?? null,
            'qty' => $data['qty'],
            'harga_beli' => $data['harga_beli'] ?? 0,
            'harga_jual' => $data['harga_jual'] ?? null,
            'alasan_harga' => $data['alasan_harga'] ?? null,
            'diskon' => array_key_exists('diskon', $data) ? $data['diskon'] : null,
            'ppn' => array_key_exists('ppn', $data) ? $data['ppn'] : null,
            'jenis_mutasi' => $jenisMutasi,
            'tanggal_mutasi' => $data['tanggal_mutasi'] ?? now(),
            'nomor_referensi' => $data['nomor_referensi'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
            'created_by' => Auth::id(),
        ];

        if (! $isInbound && empty($payload['stok_batch_id'])) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch untuk mutasi stok keluar.',
            ]);
        }

        return $this->recordMovement($payload);
    }

    public function recordStockOpnameAdjustment(
        StockOpnameModel $opname,
        StockOpnameDetailModel $detail,
        ?int $createdBy = null
    ): KartuStokModel {
        $difference = round((float) ($detail->selisih_validasi ?? $detail->selisih), 2);
        $batchNumber = $detail->stok_batch_id
            ? $detail->no_batch
            : 'SO-'.$opname->id.'-'.$detail->obat_id;

        if (abs($difference) < 0.005) {
            throw ValidationException::withMessages([
                'selisih' => 'Baris tanpa selisih tidak memerlukan penyesuaian.',
            ]);
        }

        return $this->recordMovement([
            'branch_id' => $opname->branch_id,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $detail->stok_batch_id,
            'no_batch' => $batchNumber,
            'expired_date' => $detail->expired_date,
            'qty' => abs($difference),
            'harga_beli' => (float) ($detail->hpp ?? 0),
            'preserve_batch_cost' => true,
            'bypass_stock_opname_lock' => true,
            'skip_stock_opname_tracking' => true,
            'jenis_mutasi' => $difference > 0 ? 'penyesuaian_opname_masuk' : 'penyesuaian_opname_keluar',
            'tanggal_mutasi' => now(),
            'reference_type' => StockOpnameModel::class,
            'reference_id' => $opname->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $opname->nomor,
            'keterangan' => 'Penyesuaian stock opname '.$opname->nomor.' - '.$detail->nama_obat
                .' batch '.$batchNumber.'. Alasan: '.($detail->alasan_selisih ?: '-'),
            'created_by' => $createdBy ?: Auth::id(),
        ]);
    }

    public function recordSaleOutbound(
        int $branchId,
        PenjualanTransactionDetailModel $detail,
        int $batchId,
        float $qtyStock,
        string $nomorTransaksi,
        ?int $createdBy = null
    ): KartuStokModel {
        $detail->loadMissing(['transaction', 'obat.satuan']);

        return $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $batchId,
            'qty' => $qtyStock,
            'jenis_mutasi' => 'penjualan',
            'tanggal_mutasi' => $detail->transaction?->tanggal_transaksi ?: now(),
            'reference_type' => PenjualanTransactionModel::class,
            'reference_id' => $detail->penjualan_transaction_id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $nomorTransaksi,
            'keterangan' => 'Penjualan POS '.$nomorTransaksi.' - '.$detail->nama_obat.' ('
                .number_format((float) $detail->qty_jual, 2, ',', '.').' '
                .($detail->satuan_jual ?: $detail->obat?->satuan?->nama ?: 'satuan').')',
            'created_by' => $createdBy ?: Auth::id(),
        ]);
    }

    public function recordSaleCancellation(
        int $branchId,
        PenjualanTransactionDetailModel $detail,
        int $batchId,
        float $qtyStock,
        string $nomorTransaksi,
        ?int $createdBy = null
    ): KartuStokModel {
        $detail->loadMissing(['transaction', 'obat.satuan']);

        return $this->recordMovement([
            'branch_id' => $branchId,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $batchId,
            'qty' => $qtyStock,
            'preserve_batch_cost' => true,
            'jenis_mutasi' => 'pembatalan_penjualan',
            'tanggal_mutasi' => now(),
            'reference_type' => PenjualanTransactionModel::class,
            'reference_id' => $detail->penjualan_transaction_id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $nomorTransaksi,
            'keterangan' => 'Pembatalan penjualan POS '.$nomorTransaksi.' - '.$detail->nama_obat.' ('
                .number_format($qtyStock, 2, ',', '.').' '
                .($detail->satuan_stok ?: $detail->obat?->satuan?->nama ?: 'satuan stok').')',
            'created_by' => $createdBy ?: Auth::id(),
        ]);
    }

    public function recordSaleReturn(
        ReturPenjualanModel $return,
        ReturPenjualanDetailModel $detail,
        ReturPenjualanBatchModel $batchAllocation,
        ?int $createdBy = null
    ): KartuStokModel {
        return $this->recordMovement([
            'branch_id' => $return->branch_id,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $batchAllocation->stok_batch_id,
            'qty' => (float) $batchAllocation->qty_stok,
            'preserve_batch_cost' => true,
            'jenis_mutasi' => 'retur_penjualan',
            'tanggal_mutasi' => $return->posted_at ?: now(),
            'reference_type' => ReturPenjualanModel::class,
            'reference_id' => $return->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $return->nomor_retur,
            'keterangan' => 'Retur penjualan '.$return->nomor_retur.' dari transaksi '
                .($return->transaction?->nomor_transaksi ?: '-').' - '.$detail->nama_obat.' ('
                .number_format((float) $batchAllocation->qty_stok, 2, ',', '.').' '
                .($detail->satuan_stok ?: 'satuan stok').')',
            'created_by' => $createdBy ?: Auth::id(),
        ]);
    }

    public function recordSaleReturnCancellation(
        ReturPenjualanModel $return,
        ReturPenjualanDetailModel $detail,
        ReturPenjualanBatchModel $batchAllocation,
        ?int $createdBy = null
    ): KartuStokModel {
        return $this->recordMovement([
            'branch_id' => $return->branch_id,
            'obat_id' => $detail->obat_id,
            'stok_batch_id' => $batchAllocation->stok_batch_id,
            'qty' => (float) $batchAllocation->qty_stok,
            'jenis_mutasi' => 'pembatalan_retur_penjualan',
            'tanggal_mutasi' => $return->cancelled_at ?: now(),
            'reference_type' => ReturPenjualanModel::class,
            'reference_id' => $return->id,
            'reference_detail_id' => $detail->id,
            'nomor_referensi' => $return->nomor_retur,
            'keterangan' => 'Pembatalan retur penjualan '.$return->nomor_retur.' - '.$detail->nama_obat.' ('
                .number_format((float) $batchAllocation->qty_stok, 2, ',', '.').' '
                .($detail->satuan_stok ?: 'satuan stok').')',
            'created_by' => $createdBy ?: Auth::id(),
        ]);
    }

    public function updateBatchSellingPrice(int $batchId, float $hargaJualBaru, string $alasan, ?int $changedBy = null): array
    {
        $batch = BranchAccess::scope(StokBatchModel::query())
            ->lockForUpdate()
            ->findOrFail($batchId);

        return $this->applyBatchSellingPrice($batch, $hargaJualBaru, $alasan, $changedBy);
    }

    public function updateBatchSellingPriceFromMargin(int $batchId, string $alasan, ?int $changedBy = null): array
    {
        $batch = BranchAccess::scope(StokBatchModel::with(['obat.golongan', 'obat.mainGolongan', 'obat.subGolongan']))
            ->lockForUpdate()
            ->findOrFail($batchId);
        $marginPrice = $this->batchSellingPriceMarginPreview($batch);

        return $this->applyBatchSellingPrice($batch, $marginPrice['harga_jual'], $alasan, $changedBy) + [
            'margin_price' => $marginPrice,
        ];
    }

    public function batchSellingPriceMarginPreview(StokBatchModel $batch): array
    {
        $batch->loadMissing(['obat.golongan', 'obat.mainGolongan', 'obat.subGolongan']);

        return $this->calculateBatchSellingPriceFromMargin($batch);
    }

    private function applyBatchSellingPrice(StokBatchModel $batch, float $hargaJualBaru, string $alasan, ?int $changedBy = null): array
    {
        $hargaJualLama = (float) ($batch->harga_jual ?? 0);
        $hargaJualBaru = max(0, round($hargaJualBaru, 2));

        if ($this->samePrice($hargaJualLama, $hargaJualBaru)) {
            return [
                'batch' => $batch,
                'changed' => false,
                'harga_jual_lama' => $hargaJualLama,
                'harga_jual_baru' => $hargaJualBaru,
            ];
        }

        $batch->harga_jual = $hargaJualBaru;
        $batch->save();

        $this->recordPriceHistory($batch, $hargaJualLama, $hargaJualBaru, [
            'alasan_harga' => $alasan,
            'jenis_mutasi' => 'perubahan_harga',
            'created_by' => $changedBy ?? Auth::id(),
        ]);

        return [
            'batch' => $batch,
            'changed' => true,
            'harga_jual_lama' => $hargaJualLama,
            'harga_jual_baru' => $hargaJualBaru,
        ];
    }

    private function recordMovement(array $payload): KartuStokModel
    {
        $qty = (float) ($payload['qty'] ?? 0);

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Qty mutasi harus lebih dari 0.',
            ]);
        }

        $jenisMutasi = $payload['jenis_mutasi'];
        $branchId = (int) ($payload['branch_id'] ?? 0);

        if (! $branchId) {
            $branchId = BranchAccess::requireUserBranchId();
        }

        $isInbound = $this->isInboundMutation($jenisMutasi);
        $direction = $isInbound ? 1 : -1;
        $tanggalMutasi = $this->parseDateTime($payload['tanggal_mutasi'] ?? now());
        $preserveBatchCost = ! empty($payload['preserve_batch_cost']);
        $hasDiscountPayload = ! $preserveBatchCost && array_key_exists('diskon', $payload) && $payload['diskon'] !== null && $payload['diskon'] !== '';
        $hasTaxPayload = ! $preserveBatchCost && array_key_exists('ppn', $payload) && $payload['ppn'] !== null && $payload['ppn'] !== '';
        $diskon = $hasDiscountPayload ? $this->discountPercent($payload['diskon']) : 0;
        $ppn = $hasTaxPayload ? $this->percent($payload['ppn']) : 0;
        $obat = MasterObatModel::lockForUpdate()->findOrFail($payload['obat_id']);
        $activeOpnames = $this->activeStockOpnamesFor($branchId, $obat->rak_id);

        if (empty($payload['bypass_stock_opname_lock'])) {
            $frozenOpname = $activeOpnames->first(fn ($opname) => in_array($opname->status, StockOpnameModel::lockingStatuses(), true));

            if ($frozenOpname) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok sedang dibekukan oleh stock opname '.$frozenOpname->nomor.'.',
                ]);
            }
        }

        $batch = $this->resolveBatch($payload, $obat, $isInbound, $tanggalMutasi, $diskon, $ppn, $branchId, $hasDiscountPayload, $hasTaxPayload);
        $diskon = $hasDiscountPayload ? $diskon : (float) ($batch->diskon ?? 0);
        $ppn = $hasTaxPayload ? $ppn : (float) ($batch->ppn ?? 0);
        $hargaJualLama = (float) ($batch->harga_jual ?? 0);
        $hargaJualBaru = null;
        $nextBatchQty = (float) $batch->qty + ($direction * $qty);

        if ($nextBatchQty < -0.00001) {
            throw ValidationException::withMessages([
                'qty' => 'Qty keluar melebihi stok batch tersedia.',
            ]);
        }

        $batch->qty = max(0, $nextBatchQty);
        $batch->last_movement_at = $tanggalMutasi;

        if ($isInbound && empty($payload['preserve_batch_cost'])) {
            $batch->harga_beli = (float) ($payload['harga_beli'] ?: $batch->harga_beli);
            $batch->diskon = $diskon;
            $batch->ppn = $ppn;
            $hargaJual = $this->optionalPrice($payload['harga_jual'] ?? null);

            if ($hargaJual !== null) {
                $hargaJualBaru = $hargaJual;
                $batch->harga_jual = $hargaJual;
            }
        }

        $batch->save();

        if ($isInbound && $hargaJualBaru !== null && ! $this->samePrice($hargaJualLama, $hargaJualBaru)) {
            $this->recordPriceHistory($batch, $hargaJualLama, $hargaJualBaru, $payload);
        }

        $saldoTotal = (float) StokBatchModel::where('branch_id', $branchId)
            ->where('obat_id', $obat->id)
            ->sum('qty');

        $movement = KartuStokModel::create([
            'branch_id' => $branchId,
            'obat_id' => $obat->id,
            'stok_batch_id' => $batch->id,
            'tanggal_mutasi' => $tanggalMutasi,
            'jenis_mutasi' => $jenisMutasi,
            'qty_masuk' => $isInbound ? $qty : 0,
            'qty_keluar' => $isInbound ? 0 : $qty,
            'saldo_batch' => $batch->qty,
            'saldo_total' => $saldoTotal,
            'no_batch' => $batch->no_batch,
            'expired_date' => $batch->expired_date,
            'harga_beli' => $batch->harga_beli,
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
            'reference_detail_id' => $payload['reference_detail_id'] ?? null,
            'nomor_referensi' => $payload['nomor_referensi'] ?? null,
            'keterangan' => $payload['keterangan'] ?? null,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);

        if (empty($payload['skip_stock_opname_tracking'])) {
            $this->trackStockOpnameMovements($activeOpnames, $movement);
        }

        return $movement;
    }

    private function activeStockOpnamesFor(int $branchId, ?int $rackId)
    {
        return StockOpnameModel::query()
            ->where('branch_id', $branchId)
            ->whereIn('status', StockOpnameModel::activeStatuses())
            ->where(function ($query) use ($rackId) {
                $query->whereNull('rak_id');

                if ($rackId !== null) {
                    $query->orWhere('rak_id', $rackId);
                }
            })
            ->get();
    }

    private function trackStockOpnameMovements($activeOpnames, KartuStokModel $movement): void
    {
        foreach ($activeOpnames as $opname) {
            $detail = StockOpnameDetailModel::query()
                ->where('stock_opname_id', $opname->id)
                ->where('stok_batch_id', $movement->stok_batch_id)
                ->first();

            if (! $detail) {
                $detail = StockOpnameDetailModel::query()
                    ->where('stock_opname_id', $opname->id)
                    ->where('obat_id', $movement->obat_id)
                    ->whereNull('stok_batch_id')
                    ->first();

                if ($detail) {
                    $detail->forceFill([
                        'stok_batch_id' => $movement->stok_batch_id,
                        'no_batch' => $movement->no_batch,
                        'expired_date' => $movement->expired_date,
                        'hpp' => $movement->harga_beli,
                    ])->save();
                }
            }

            if (! $detail) {
                $batch = StokBatchModel::with(['obat.satuan'])->find($movement->stok_batch_id);
                $countingClosed = $opname->status !== StockOpnameModel::STATUS_COUNTING;

                $detail = StockOpnameDetailModel::firstOrCreate(
                    [
                        'stock_opname_id' => $opname->id,
                        'stok_batch_id' => $movement->stok_batch_id,
                    ],
                    [
                        'obat_id' => $movement->obat_id,
                        'rak_id' => $batch?->obat?->rak_id,
                        'kode_obat' => $batch?->obat?->kode_obat,
                        'nama_obat' => $batch?->obat?->nama_obat ?: 'Obat #'.$movement->obat_id,
                        'satuan' => $batch?->obat?->satuan?->nama,
                        'no_batch' => $movement->no_batch ?: ($batch?->no_batch ?: '-'),
                        'expired_date' => $movement->expired_date,
                        'hpp' => $movement->harga_beli,
                        'stok_sistem_awal' => 0,
                        'stok_sistem_hitung' => $countingClosed ? 0 : null,
                        'stok_fisik' => $countingClosed ? 0 : null,
                        'selisih' => $countingClosed ? 0 : null,
                        'alasan_selisih' => $countingClosed ? 'Batch masuk setelah periode penghitungan fisik.' : null,
                        'counted_by' => $countingClosed ? $opname->submitted_by : null,
                        'counted_at' => $countingClosed ? ($opname->submitted_at ?: now()) : null,
                    ]
                );
            }

            StockOpnameMovementModel::firstOrCreate(
                ['kartu_stok_id' => $movement->id],
                [
                    'stock_opname_id' => $opname->id,
                    'stock_opname_detail_id' => $detail->id,
                    'stok_batch_id' => $movement->stok_batch_id,
                    'jenis_mutasi' => $movement->jenis_mutasi,
                    'qty_masuk' => $movement->qty_masuk,
                    'qty_keluar' => $movement->qty_keluar,
                    'saldo_batch' => $movement->saldo_batch,
                    'occurred_at' => $movement->tanggal_mutasi,
                ]
            );
        }
    }

    private function resolveBatch(
        array $payload,
        MasterObatModel $obat,
        bool $isInbound,
        Carbon $tanggalMutasi,
        float $diskon,
        float $ppn,
        int $branchId,
        bool $hasDiscountPayload = true,
        bool $hasTaxPayload = true
    ): StokBatchModel {
        if (! empty($payload['stok_batch_id'])) {
            $batch = StokBatchModel::where('obat_id', $obat->id)
                ->where('branch_id', $branchId)
                ->where('id', $payload['stok_batch_id'])
                ->lockForUpdate()
                ->first();

            if (! $batch) {
                throw ValidationException::withMessages([
                    'stok_batch_id' => 'Batch stok tidak ditemukan.',
                ]);
            }

            if ($isInbound && ! $hasDiscountPayload && ! $hasTaxPayload) {
                return $batch;
            }

            $discountMatches = ! $hasDiscountPayload || $this->samePercent((float) ($batch->diskon ?? 0), $diskon);
            $taxMatches = ! $hasTaxPayload || $this->samePercent((float) ($batch->ppn ?? 0), $ppn);

            if ($isInbound && (! $discountMatches || ! $taxMatches)) {
                return $this->resolveInboundBatchByIdentity(
                    $payload,
                    $obat,
                    $tanggalMutasi,
                    $branchId,
                    $hasDiscountPayload ? $diskon : (float) ($batch->diskon ?? 0),
                    $hasTaxPayload ? $ppn : (float) ($batch->ppn ?? 0)
                );
            }

            return $batch;
        }

        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        if (! $isInbound) {
            if (! empty($payload['allow_identity_outbound'])) {
                return $this->resolveOutboundBatchByIdentity($payload, $obat, $branchId, $diskon, $ppn);
            }

            throw ValidationException::withMessages([
                'stok_batch_id' => 'Pilih batch stok untuk mutasi keluar.',
            ]);
        }

        return $this->resolveInboundBatchByIdentity($payload, $obat, $tanggalMutasi, $branchId, $diskon, $ppn);
    }

    private function resolveInboundBatchByIdentity(array $payload, MasterObatModel $obat, Carbon $tanggalMutasi, int $branchId, float $diskon, float $ppn): StokBatchModel
    {
        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        $expiredDate = $this->parseOptionalDate($payload['expired_date'] ?? null, 'expired_date');
        $batch = StokBatchModel::where('obat_id', $obat->id)
            ->where('branch_id', $branchId)
            ->where('no_batch', $batchNumber)
            ->when(
                $expiredDate === null,
                fn ($query) => $query->whereNull('expired_date'),
                fn ($query) => $query->whereDate('expired_date', $expiredDate)
            )
            ->where('diskon', $diskon)
            ->where('ppn', $ppn)
            ->lockForUpdate()
            ->first();

        if ($batch) {
            return $batch;
        }

        return StokBatchModel::create([
            'branch_id' => $branchId,
            'obat_id' => $obat->id,
            'no_batch' => $batchNumber,
            'expired_date' => $expiredDate,
            'qty' => 0,
            'harga_beli' => (float) ($payload['harga_beli'] ?? 0),
            'harga_jual' => 0,
            'diskon' => $diskon,
            'ppn' => $ppn,
            'last_movement_at' => $tanggalMutasi,
            'created_by' => $payload['created_by'] ?? Auth::id(),
        ]);
    }

    private function recordPriceHistory(StokBatchModel $batch, float $hargaJualLama, float $hargaJualBaru, array $payload): void
    {
        RiwayatHargaModel::create([
            'obat_id' => $batch->obat_id,
            'stok_batch_id' => $batch->id,
            'harga_jual_lama' => $hargaJualLama,
            'harga_jual_baru' => $hargaJualBaru,
            'alasan' => $this->priceHistoryReason($payload),
            'changed_by' => $payload['created_by'] ?? Auth::id(),
            'created_at' => now(),
        ]);
    }

    private function calculateBatchSellingPriceFromMargin(StokBatchModel $batch): array
    {
        $obat = $batch->obat;

        if (! $obat) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Obat pada batch stok tidak ditemukan.',
            ]);
        }

        $margin = $this->marginsService->activeMarginForObat($obat);
        $faktorJual = $margin ? (float) $margin->faktor_jual : 1.0;
        $marginReference = $this->marginsService->marginReferenceLabelForObat($obat, $margin);
        $diskon = $this->discountPercent($batch->diskon ?? 0);
        $ppn = $this->percent($batch->ppn ?? 0);
        $hargaBeli = max(0, (float) ($batch->harga_beli ?? 0));
        $hargaBeliDasar = max(0, $hargaBeli - ($hargaBeli * ($diskon / 100)));
        $hargaBeliIncludePpn = $hargaBeliDasar * (1 + ($ppn / 100));

        return [
            'harga_jual' => round($hargaBeliIncludePpn * $faktorJual, 2),
            'harga_beli_dasar' => round($hargaBeliDasar, 2),
            'harga_beli_include_ppn' => round($hargaBeliIncludePpn, 2),
            'faktor_jual' => round($faktorJual, 3),
            'ppn' => $ppn,
            'has_margin' => (bool) $margin,
            'margin_tingkat' => $margin?->tingkat,
            'margin_reference' => $marginReference,
        ];
    }

    private function priceHistoryReason(array $payload): string
    {
        $reason = trim((string) ($payload['alasan_harga'] ?? ''));

        if ($reason !== '') {
            return $reason;
        }

        $label = match ($payload['jenis_mutasi'] ?? '') {
            'masuk' => 'Mutasi stok masuk',
            'penyesuaian_masuk' => 'Penyesuaian stok masuk',
            default => 'Perubahan harga jual batch',
        };
        $reference = trim((string) ($payload['nomor_referensi'] ?? ''));
        $note = trim((string) ($payload['keterangan'] ?? ''));

        return collect([$label, $reference, $note])
            ->filter()
            ->implode(' - ');
    }

    private function resolveOutboundBatchByIdentity(array $payload, MasterObatModel $obat, int $branchId, float $diskon, float $ppn): StokBatchModel
    {
        $batchNumber = trim((string) ($payload['no_batch'] ?? ''));

        if ($batchNumber === '') {
            throw ValidationException::withMessages([
                'no_batch' => 'Nomor batch wajib diisi.',
            ]);
        }

        $expiredDate = $this->parseOptionalDate($payload['expired_date'] ?? null, 'expired_date');
        $batch = StokBatchModel::where('obat_id', $obat->id)
            ->where('branch_id', $branchId)
            ->where('no_batch', $batchNumber)
            ->when(
                $expiredDate === null,
                fn ($query) => $query->whereNull('expired_date'),
                fn ($query) => $query->whereDate('expired_date', $expiredDate)
            )
            ->where('diskon', $diskon)
            ->where('ppn', $ppn)
            ->lockForUpdate()
            ->first();

        if (! $batch) {
            throw ValidationException::withMessages([
                'stok_batch_id' => 'Batch stok penerimaan tidak ditemukan.',
            ]);
        }

        return $batch;
    }

    private function optionalPrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return max(0, (float) $value);
    }

    private function discountPercent($value): float
    {
        return $this->percent($value);
    }

    private function sameDiscount(float $left, float $right): bool
    {
        return $this->samePercent($left, $right);
    }

    private function percent($value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    private function samePercent(float $left, float $right): bool
    {
        return abs($this->percent($left) - $this->percent($right)) < 0.00001;
    }

    private function samePrice(float $left, float $right): bool
    {
        return abs(round($left, 2) - round($right, 2)) < 0.01;
    }

    private function isInboundMutation(string $jenisMutasi): bool
    {
        return in_array($jenisMutasi, [
            'masuk',
            'penyesuaian_masuk',
            'penyesuaian_opname_masuk',
            'pembatalan_retur_pembelian',
            'pembatalan_penjualan',
            'retur_penjualan',
        ], true);
    }

    private function receiptStockQuantity(PenerimaanBarangDetailModel $detail): float
    {
        $storedQty = (float) ($detail->qty_diterima_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_diterima * $this->receiptConversion($detail);
    }

    private function receiptBasePrice(PenerimaanBarangDetailModel $detail): float
    {
        $storedPrice = (float) ($detail->harga_beli_stok ?? 0);

        if ($storedPrice > 0) {
            return $storedPrice;
        }

        $conversion = $this->receiptConversion($detail);

        return $conversion > 0 ? (float) $detail->harga_beli / $conversion : (float) $detail->harga_beli;
    }

    private function receiptConversion(PenerimaanBarangDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return $storedConversion;
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function conversionNote(PenerimaanBarangDetailModel $detail, float $qtyStock): string
    {
        $conversion = $this->receiptConversion($detail);
        $purchaseUnit = $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? 'satuan');
        $stockUnit = $detail->satuan_stok
            ?: ($detail->obat?->satuan?->nama ?? 'satuan stok');

        return number_format((float) $detail->qty_diterima, 2, ',', '.').' '.$purchaseUnit
            .' x '.number_format($conversion, 2, ',', '.')
            .' = '.number_format($qtyStock, 2, ',', '.').' '.$stockUnit;
    }

    private function returnStockQuantity(ReturPembelianDetailModel $detail): float
    {
        $storedQty = (float) ($detail->qty_retur_stok ?? 0);

        if ($storedQty > 0) {
            return $storedQty;
        }

        return (float) $detail->qty_retur * $this->returnConversion($detail);
    }

    private function returnBasePrice(ReturPembelianDetailModel $detail): float
    {
        $storedPrice = (float) ($detail->harga_beli_stok ?? 0);

        if ($storedPrice > 0) {
            return $storedPrice;
        }

        $conversion = $this->returnConversion($detail);

        return $conversion > 0 ? (float) $detail->harga_beli / $conversion : (float) $detail->harga_beli;
    }

    private function returnConversion(ReturPembelianDetailModel $detail): float
    {
        $storedConversion = (float) ($detail->konversi_satuan ?? 0);

        if ($storedConversion > 0) {
            return $storedConversion;
        }

        return max(1, (float) ($detail->purchaseOrderDetail?->satuanKonversi?->konversi ?? 1));
    }

    private function returnConversionNote(ReturPembelianDetailModel $detail, float $qtyStock): string
    {
        $conversion = $this->returnConversion($detail);
        $purchaseUnit = $detail->satuan_beli
            ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? 'satuan');
        $stockUnit = $detail->satuan_stok
            ?: ($detail->obat?->satuan?->nama ?? 'satuan stok');

        return number_format((float) $detail->qty_retur, 2, ',', '.').' '.$purchaseUnit
            .' x '.number_format($conversion, 2, ',', '.')
            .' = '.number_format($qtyStock, 2, ',', '.').' '.$stockUnit;
    }

    private function parseDate($date, string $field): string
    {
        $value = trim((string) $date);

        if ($value === '') {
            throw ValidationException::withMessages([
                $field => 'Expired date wajib diisi.',
            ]);
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function parseOptionalDate($date, string $field): ?string
    {
        $value = trim((string) $date);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function parseDateTime($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return now();
        }
    }
}
