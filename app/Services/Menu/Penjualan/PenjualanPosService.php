<?php

namespace App\Services\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionBatchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Stok\StockService;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PenjualanPosService
{
    public const TRANSACTION_TYPES = [
        'penjualan_bebas' => 'Penjualan Bebas',
        'penjualan_resep' => 'Resep Non Racikan',
        'penjualan_racikan' => 'Resep Racikan',
        'penjualan_kredit' => 'Penjualan Kredit',
        'penjualan_instansi' => 'Penjualan Instansi',
    ];

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'debit' => 'Debit',
        'credit_card' => 'Kartu Kredit',
        'transfer' => 'Transfer',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
        'piutang' => 'Piutang',
        'instansi' => 'Instansi',
    ];

    private const STOCK_EPSILON = 0.00001;

    public function __construct(private readonly StockService $stockService) {}

    public function searchProducts(string $search = '', int $limit = 30): array
    {
        $branchIds = BranchAccess::userBranchIds();

        if (empty($branchIds)) {
            return [];
        }

        $search = trim($search);

        $query = MasterObatModel::with([
            'kategori',
            'golongan',
            'mainGolongan',
            'subGolongan',
            'satuan',
            'sediaan',
            'pabrikan',
            'distributor',
            'rakPenyimpanan',
            'konversiSatuan.satuan',
            'stokBatches' => function ($query) use ($branchIds) {
                $query->whereIn('branch_id', $branchIds)
                    ->where('qty', '>', 0)
                    ->where(fn ($query) => $query->whereNull('expired_date')->orWhereDate('expired_date', '>=', today()))
                    ->orderByRaw('CASE WHEN expired_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('expired_date')
                    ->orderBy('id');
            },
        ])
            ->withSum([
                'stokBatches as total_stok' => function ($query) use ($branchIds) {
                    $query->whereIn('branch_id', $branchIds)
                        ->where(fn ($query) => $query->whereNull('expired_date')->orWhereDate('expired_date', '>=', today()));
                },
            ], 'qty')
            ->where('is_active', true);

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where('kode_obat', 'like', $like)
                    ->orWhere('nama_obat', 'like', $like)
                    ->orWhere('komposisi', 'like', $like)
                    ->orWhere('indikasi', 'like', $like)
                    ->orWhere('dosis', 'like', $like)
                    ->orWhere('kemasan', 'like', $like)
                    ->orWhereHas('kategori', fn ($query) => $query->where('name', 'like', $like)->orWhere('code', 'like', $like))
                    ->orWhereHas('golongan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('mainGolongan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('subGolongan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('satuan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('konversiSatuan.satuan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('sediaan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('pabrikan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('distributor', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('rakPenyimpanan', fn ($query) => $query->where('nama', 'like', $like)->orWhere('kode', 'like', $like))
                    ->orWhereHas('stokBatches', fn ($query) => $query->where('no_batch', 'like', $like));
            });
        }

        return $query
            ->orderBy('nama_obat')
            ->limit(min(80, max(1, $limit)))
            ->get()
            ->map(fn (MasterObatModel $obat) => $this->productPayload($obat))
            ->values()
            ->all();
    }

    public function productQuote(int $obatId, ?int $satuanId, float $qtyJual = 1): array
    {
        $branchId = BranchAccess::requireUserBranchId();
        $obat = $this->loadProduct($obatId);
        $unit = $this->resolveUnit($obat, $satuanId);
        $qtyJual = $this->quantity($qtyJual);
        $qtyStock = $this->quantity($qtyJual * (float) $unit['konversi']);
        $allocations = $this->allocateFefo($branchId, $obat->id, $qtyStock, false, false);
        $subtotalGross = $this->allocationSubtotal($allocations);
        $availableStock = $this->availableStock($branchId, $obat->id);
        $available = $qtyStock <= $availableStock + self::STOCK_EPSILON;
        $unitPrice = $qtyJual > 0 ? round($subtotalGross / $qtyJual, 2) : 0;

        return [
            'obat_id' => $obat->id,
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'satuan_id' => $unit['satuan_id'],
            'satuan' => $unit['nama'],
            'satuan_stok' => $obat->satuan->nama ?? 'satuan stok',
            'konversi' => (float) $unit['konversi'],
            'qty_jual' => $qtyJual,
            'qty_stok' => $qtyStock,
            'stock_available' => round($availableStock, 2),
            'is_available' => $available,
            'harga_jual' => $unitPrice,
            'subtotal_gross' => round($subtotalGross, 2),
            'allocations' => $this->allocationSummary($allocations),
        ];
    }

    public function saveDraft(array $payload): PenjualanTransactionModel
    {
        return DB::transaction(function () use ($payload) {
            $branchId = BranchAccess::requireUserBranchId();
            $transaction = $this->transactionForWrite($payload, $branchId, true);
            $this->clearDraftLines($transaction);
            $this->fillBaseHeader($transaction, $payload, $branchId, 'draft');
            $transaction->save();

            $totals = $this->persistDetails($transaction, $payload, false);
            $this->applyTotals($transaction, $payload, $totals, [
                'total_bayar' => 0,
                'kembalian' => 0,
                'sisa_tagihan' => $totals['grand_total'],
                'payment_status' => 'unpaid',
            ]);

            return $transaction->fresh([
                'branch',
                'details.batchAllocations',
                'payments',
                'createdBy',
            ]);
        });
    }

    public function completeTransaction(array $payload): PenjualanTransactionModel
    {
        return DB::transaction(function () use ($payload) {
            $branchId = BranchAccess::requireUserBranchId();
            $transaction = $this->transactionForWrite($payload, $branchId, false);
            $this->clearDraftLines($transaction);
            $this->fillBaseHeader($transaction, $payload, $branchId, 'completed');
            $transaction->save();

            $totals = $this->persistDetails($transaction, $payload, true);
            $paymentTotals = $this->persistPayments($transaction, $payload, $totals['grand_total']);
            $this->applyTotals($transaction, $payload, $totals, $paymentTotals + [
                'payment_status' => $paymentTotals['sisa_tagihan'] > 0 ? 'credit' : 'paid',
            ]);

            $transaction->forceFill([
                'completed_at' => now(),
                'completed_by' => Auth::id(),
            ])->save();

            return $transaction->fresh([
                'branch',
                'details.batchAllocations',
                'payments',
                'createdBy',
                'completedBy',
            ]);
        });
    }

    public function cancelTransaction(PenjualanTransactionModel $transaction, string $reason): PenjualanTransactionModel
    {
        return DB::transaction(function () use ($transaction, $reason) {
            $branchIds = BranchAccess::userBranchIds();

            $transaction = PenjualanTransactionModel::with(['details.batchAllocations'])
                ->whereKey($transaction->id)
                ->whereIn('branch_id', $branchIds)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Transaksi sudah dibatalkan.',
                ]);
            }

            if ($transaction->status === 'completed') {
                foreach ($transaction->details as $detail) {
                    foreach ($detail->batchAllocations as $allocation) {
                        if ($allocation->cancel_kartu_stok_id) {
                            continue;
                        }

                        $movement = $this->stockService->recordSaleCancellation(
                            (int) $transaction->branch_id,
                            $detail,
                            (int) $allocation->stok_batch_id,
                            (float) $allocation->qty_stok,
                            $transaction->nomor_transaksi,
                            Auth::id()
                        );

                        $allocation->forceFill(['cancel_kartu_stok_id' => $movement->id])->save();
                    }
                }
            }

            $transaction->forceFill([
                'status' => 'cancelled',
                'payment_status' => 'void',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $reason,
            ])->save();

            return $transaction->fresh([
                'branch',
                'details.batchAllocations',
                'payments',
                'createdBy',
                'cancelledBy',
            ]);
        });
    }

    public function generateNoTransaction(int $branchId, ?Carbon $date = null): string
    {
        $date = $date ?: now();
        $branch = BranchModel::find($branchId);
        $branchCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $branch?->code ?: 'BR'.$branchId));
        $branchCode = $branchCode !== '' ? $branchCode : 'BR'.$branchId;
        $prefix = 'PJ-'.$date->format('Ymd').'-'.$branchCode.'-';
        $sequence = PenjualanTransactionModel::where('branch_id', $branchId)
            ->whereDate('tanggal_transaksi', $date->toDateString())
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (PenjualanTransactionModel::where('nomor_transaksi', $number)->exists());

        return $number;
    }

    private function productPayload(MasterObatModel $obat): array
    {
        $units = $this->unitsForProduct($obat);
        $firstBatch = $obat->stokBatches->first();
        $totalStock = (float) ($obat->total_stok ?? 0);

        return [
            'id' => $obat->id,
            'text' => $obat->kode_obat.' - '.$obat->nama_obat,
            'kode_obat' => $obat->kode_obat,
            'nama_obat' => $obat->nama_obat,
            'kategori' => $obat->kategori->name ?? '-',
            'golongan' => $obat->golongan->nama ?? '-',
            'main_golongan' => $obat->mainGolongan->nama ?? '-',
            'sub_golongan' => $obat->subGolongan->nama ?? '-',
            'sediaan' => $obat->sediaan->nama ?? '-',
            'pabrikan' => $obat->pabrikan->nama ?? '-',
            'indikasi' => $obat->indikasi,
            'komposisi' => $obat->komposisi,
            'dosis' => $obat->dosis,
            'satuan_stok' => $obat->satuan->nama ?? '-',
            'total_stok' => round($totalStock, 2),
            'harga_jual' => (float) ($firstBatch?->harga_jual ?? 0),
            'next_batch' => $firstBatch ? [
                'id' => $firstBatch->id,
                'no_batch' => $firstBatch->no_batch,
                'expired_date' => optional($firstBatch->expired_date)->format('Y-m-d'),
                'qty' => (float) $firstBatch->qty,
                'harga_jual' => (float) $firstBatch->harga_jual,
            ] : null,
            'units' => $units,
        ];
    }

    private function loadProduct(int $obatId): MasterObatModel
    {
        return MasterObatModel::with([
            'kategori',
            'golongan',
            'mainGolongan',
            'subGolongan',
            'satuan',
            'konversiSatuan.satuan',
        ])->findOrFail($obatId);
    }

    private function unitsForProduct(MasterObatModel $obat): array
    {
        $units = collect();

        if ($obat->satuan_id && $obat->satuan) {
            $units->push([
                'satuan_id' => (int) $obat->satuan_id,
                'nama' => $obat->satuan->nama,
                'konversi' => 1.0,
                'is_default' => true,
            ]);
        }

        foreach ($obat->konversiSatuan as $conversion) {
            if (! $conversion->satuan_id || ! $conversion->satuan) {
                continue;
            }

            $exists = $units->contains(fn ($unit) => (int) $unit['satuan_id'] === (int) $conversion->satuan_id);

            if ($exists && (float) $conversion->konversi === 1.0) {
                continue;
            }

            $units->push([
                'satuan_id' => (int) $conversion->satuan_id,
                'nama' => $conversion->satuan->nama,
                'konversi' => max(1, (float) $conversion->konversi),
                'is_default' => (bool) $conversion->is_default,
            ]);
        }

        return $units
            ->sortBy('konversi')
            ->values()
            ->all();
    }

    private function resolveUnit(MasterObatModel $obat, ?int $satuanId): array
    {
        $units = collect($this->unitsForProduct($obat));

        if ($units->isEmpty()) {
            throw ValidationException::withMessages([
                'satuan_id' => 'Satuan jual obat belum tersedia.',
            ]);
        }

        if ($satuanId) {
            $unit = $units->first(fn ($unit) => (int) $unit['satuan_id'] === $satuanId);

            if ($unit) {
                return $unit;
            }

            throw ValidationException::withMessages([
                'satuan_id' => 'Satuan jual tidak terdaftar untuk obat ini.',
            ]);
        }

        return $units->firstWhere('is_default', true) ?: $units->first();
    }

    private function transactionForWrite(array $payload, int $branchId, bool $draftMode): PenjualanTransactionModel
    {
        $draftId = (int) ($payload['draft_id'] ?? 0);

        if ($draftId) {
            $transaction = PenjualanTransactionModel::where('branch_id', $branchId)
                ->whereKey($draftId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status !== 'draft') {
                throw ValidationException::withMessages([
                    'draft_id' => 'Hanya transaksi sementara yang bisa dilanjutkan.',
                ]);
            }

            return $transaction;
        }

        $date = $this->parseDateTime($payload['tanggal_transaksi'] ?? now());

        return new PenjualanTransactionModel([
            'branch_id' => $branchId,
            'nomor_transaksi' => $this->generateNoTransaction($branchId, $date),
            'tanggal_transaksi' => $date,
            'created_by' => Auth::id(),
            'status' => $draftMode ? 'draft' : 'completed',
        ]);
    }

    private function fillBaseHeader(PenjualanTransactionModel $transaction, array $payload, int $branchId, string $status): void
    {
        $date = $this->parseDateTime($payload['tanggal_transaksi'] ?? $transaction->tanggal_transaksi ?? now());
        $type = $this->transactionType($payload['jenis_transaksi'] ?? 'penjualan_bebas');
        $isPrescription = in_array($type, ['penjualan_resep', 'penjualan_racikan'], true);

        if (! $transaction->nomor_transaksi) {
            $transaction->nomor_transaksi = $this->generateNoTransaction($branchId, $date);
        }

        $transaction->forceFill([
            'branch_id' => $branchId,
            'tanggal_transaksi' => $date,
            'jenis_transaksi' => $type,
            'status' => $status,
            'customer_name' => $this->nullableString($payload['customer_name'] ?? null),
            'customer_phone' => $this->nullableString($payload['customer_phone'] ?? null),
            'nomor_resep' => $isPrescription ? $this->nullableString($payload['nomor_resep'] ?? null) : null,
            'tanggal_resep' => $isPrescription ? ($payload['tanggal_resep'] ?? null) : null,
            'dokter_name' => $isPrescription ? $this->nullableString($payload['dokter_name'] ?? null) : null,
            'asal_resep' => $isPrescription ? $this->nullableString($payload['asal_resep'] ?? null) : null,
            'instansi_name' => $this->nullableString($payload['instansi_name'] ?? null),
            'catatan' => $this->nullableString($payload['catatan'] ?? null),
            'created_by' => $transaction->created_by ?: Auth::id(),
        ]);
    }

    private function clearDraftLines(PenjualanTransactionModel $transaction): void
    {
        if (! $transaction->exists) {
            return;
        }

        if ($transaction->status !== 'draft') {
            throw ValidationException::withMessages([
                'status' => 'Transaksi final tidak bisa ditimpa.',
            ]);
        }

        $transaction->details()->delete();
        $transaction->payments()->delete();
    }

    private function persistDetails(PenjualanTransactionModel $transaction, array $payload, bool $commitStock): array
    {
        $details = collect($payload['details'] ?? []);

        if ($details->isEmpty()) {
            throw ValidationException::withMessages([
                'details' => 'Minimal satu item obat wajib dipilih.',
            ]);
        }

        $subtotalGross = 0.0;
        $diskonItemTotal = 0.0;
        $subtotalNet = 0.0;

        foreach ($details as $index => $item) {
            $prepared = $this->prepareLine(
                $item,
                (int) $transaction->branch_id,
                $index,
                $commitStock,
                (string) $transaction->jenis_transaksi
            );
            $detail = PenjualanTransactionDetailModel::create($prepared['detail'] + [
                'penjualan_transaction_id' => $transaction->id,
            ]);

            if ($commitStock) {
                $this->persistBatchAllocations($transaction, $detail, $prepared['allocations']);
            }

            $subtotalGross += $prepared['totals']['subtotal_gross'];
            $diskonItemTotal += $prepared['totals']['diskon_nominal'];
            $subtotalNet += $prepared['totals']['subtotal_net'];
        }

        $transactionDiscount = $this->transactionDiscount($subtotalNet, $payload);
        $taxBase = max(0, $subtotalNet - $transactionDiscount);
        $taxPercent = ! empty($payload['use_pajak']) ? $this->percent($payload['pajak_percent'] ?? 0) : 0.0;
        $taxTotal = round($taxBase * ($taxPercent / 100), 2);
        $embalase = $transaction->jenis_transaksi === 'penjualan_racikan'
            ? $this->money($payload['embalase'] ?? 0)
            : 0.0;
        $grandTotal = round($taxBase + $taxTotal + $embalase, 2);

        return [
            'subtotal_gross' => round($subtotalGross, 2),
            'diskon_item_total' => round($diskonItemTotal, 2),
            'diskon_transaksi_percent' => $this->percent($payload['diskon_transaksi_percent'] ?? 0),
            'diskon_transaksi_nominal' => round($transactionDiscount, 2),
            'subtotal_net' => round($taxBase, 2),
            'embalase' => $embalase,
            'pajak_percent' => $taxPercent,
            'pajak_total' => $taxTotal,
            'grand_total' => $grandTotal,
        ];
    }

    private function prepareLine(array $item, int $branchId, int $index, bool $commitStock, string $transactionType): array
    {
        $isPrescription = in_array($transactionType, ['penjualan_resep', 'penjualan_racikan'], true);
        $isCompoundPrescription = $transactionType === 'penjualan_racikan';
        $obat = $this->loadProduct((int) ($item['obat_id'] ?? 0));
        $unit = $this->resolveUnit($obat, isset($item['satuan_id']) ? (int) $item['satuan_id'] : null);
        $qtyJual = $this->quantity($item['qty'] ?? 0);
        $qtyStock = $this->quantity($qtyJual * (float) $unit['konversi']);
        $allocations = $this->allocateFefo($branchId, $obat->id, $qtyStock, $commitStock, true);
        $subtotalGross = round($this->allocationSubtotal($allocations), 2);

        if ($subtotalGross <= 0) {
            throw ValidationException::withMessages([
                "details.$index.obat_id" => 'Harga jual batch belum tersedia untuk '.$obat->nama_obat.'.',
            ]);
        }

        $discountPercent = $this->percent($item['diskon_percent'] ?? 0);
        $discountInputNominal = $this->money($item['diskon_nominal'] ?? 0);
        $discountNominal = min($subtotalGross, round(($subtotalGross * ($discountPercent / 100)) + $discountInputNominal, 2));
        $subtotalNet = round(max(0, $subtotalGross - $discountNominal), 2);
        $hargaJual = $qtyJual > 0 ? round($subtotalGross / $qtyJual, 2) : 0;

        return [
            'detail' => [
                'obat_id' => $obat->id,
                'satuan_id' => $unit['satuan_id'],
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => $obat->nama_obat,
                'satuan_jual' => $unit['nama'],
                'satuan_stok' => $obat->satuan->nama ?? null,
                'konversi' => $unit['konversi'],
                'qty_jual' => $qtyJual,
                'qty_stok' => $qtyStock,
                'harga_jual' => $hargaJual,
                'subtotal_gross' => $subtotalGross,
                'diskon_percent' => $discountPercent,
                'diskon_nominal' => round($discountNominal, 2),
                'subtotal_net' => $subtotalNet,
                'pajak_percent' => 0,
                'pajak_nominal' => 0,
                'total_line' => $subtotalNet,
                'batch_summary' => $this->allocationSummary($allocations),
                'keterangan' => $this->nullableString($item['keterangan'] ?? null),
                'aturan_pakai' => $isPrescription ? $this->nullableString($item['aturan_pakai'] ?? null) : null,
                'waktu_konsumsi' => $isPrescription ? $this->nullableString($item['waktu_konsumsi'] ?? null) : null,
                'durasi_hari' => $isPrescription && ! empty($item['durasi_hari']) ? (int) $item['durasi_hari'] : null,
                'racikan_group' => $isCompoundPrescription ? $this->nullableString($item['racikan_group'] ?? null) : null,
                'dosis_komponen' => $isCompoundPrescription ? $this->nullableString($item['dosis_komponen'] ?? null) : null,
            ],
            'allocations' => $allocations,
            'totals' => [
                'subtotal_gross' => $subtotalGross,
                'diskon_nominal' => round($discountNominal, 2),
                'subtotal_net' => $subtotalNet,
            ],
        ];
    }

    private function persistBatchAllocations(PenjualanTransactionModel $transaction, PenjualanTransactionDetailModel $detail, array $allocations): void
    {
        foreach ($allocations as $allocation) {
            $movement = $this->stockService->recordSaleOutbound(
                (int) $transaction->branch_id,
                $detail,
                (int) $allocation['stok_batch_id'],
                (float) $allocation['qty_stok'],
                $transaction->nomor_transaksi,
                Auth::id()
            );

            PenjualanTransactionBatchModel::create([
                'penjualan_transaction_detail_id' => $detail->id,
                'branch_id' => $transaction->branch_id,
                'obat_id' => $detail->obat_id,
                'stok_batch_id' => $allocation['stok_batch_id'],
                'no_batch' => $allocation['no_batch'],
                'expired_date' => $allocation['expired_date'],
                'qty_stok' => $allocation['qty_stok'],
                'harga_beli' => $allocation['harga_beli'],
                'harga_jual' => $allocation['harga_jual'],
                'subtotal_gross' => $allocation['subtotal_gross'],
                'kartu_stok_id' => $movement->id,
            ]);
        }
    }

    private function allocateFefo(int $branchId, int $obatId, float $qtyStock, bool $lock, bool $throwIfInsufficient): array
    {
        if ($qtyStock <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Qty jual harus lebih dari 0.',
            ]);
        }

        $remaining = $qtyStock;
        $allocations = [];
        $batches = $this->fefoBatchQuery($branchId, $obatId, $lock)->get();

        foreach ($batches as $batch) {
            if ($remaining <= self::STOCK_EPSILON) {
                break;
            }

            $availableQty = (float) $batch->qty;
            $take = min($availableQty, $remaining);

            if ($take <= self::STOCK_EPSILON) {
                continue;
            }

            $allocations[] = [
                'stok_batch_id' => $batch->id,
                'no_batch' => $batch->no_batch,
                'expired_date' => optional($batch->expired_date)->format('Y-m-d'),
                'qty_stok' => round($take, 2),
                'harga_beli' => (float) $batch->harga_beli,
                'harga_jual' => (float) $batch->harga_jual,
                'subtotal_gross' => round($take * (float) $batch->harga_jual, 2),
            ];

            $remaining -= $take;
        }

        if ($throwIfInsufficient && $remaining > self::STOCK_EPSILON) {
            $obat = MasterObatModel::with('satuan')->find($obatId);
            $available = $qtyStock - $remaining;

            throw ValidationException::withMessages([
                'stok' => 'Stok '.$obat?->nama_obat.' tidak cukup. Tersedia '
                    .number_format($available, 2, ',', '.').' '.($obat?->satuan?->nama ?: 'satuan stok').'.',
            ]);
        }

        return $allocations;
    }

    private function fefoBatchQuery(int $branchId, int $obatId, bool $lock)
    {
        $query = StokBatchModel::where('branch_id', $branchId)
            ->where('obat_id', $obatId)
            ->where('qty', '>', 0)
            ->where(fn ($query) => $query->whereNull('expired_date')->orWhereDate('expired_date', '>=', today()))
            ->orderByRaw('CASE WHEN expired_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expired_date')
            ->orderBy('id');

        return $lock ? $query->lockForUpdate() : $query;
    }

    private function availableStock(int $branchId, int $obatId): float
    {
        return (float) StokBatchModel::where('branch_id', $branchId)
            ->where('obat_id', $obatId)
            ->where(fn ($query) => $query->whereNull('expired_date')->orWhereDate('expired_date', '>=', today()))
            ->sum('qty');
    }

    private function allocationSubtotal(array $allocations): float
    {
        return collect($allocations)->sum(fn ($allocation) => (float) $allocation['subtotal_gross']);
    }

    private function allocationSummary(array $allocations): array
    {
        return collect($allocations)
            ->map(fn ($allocation) => [
                'stok_batch_id' => $allocation['stok_batch_id'],
                'no_batch' => $allocation['no_batch'],
                'expired_date' => $allocation['expired_date'],
                'qty_stok' => (float) $allocation['qty_stok'],
                'harga_jual' => (float) $allocation['harga_jual'],
                'subtotal_gross' => (float) $allocation['subtotal_gross'],
            ])
            ->values()
            ->all();
    }

    private function transactionDiscount(float $subtotalNet, array $payload): float
    {
        $percent = $this->percent($payload['diskon_transaksi_percent'] ?? 0);
        $nominal = $this->money($payload['diskon_transaksi_nominal'] ?? 0);

        return min($subtotalNet, round(($subtotalNet * ($percent / 100)) + $nominal, 2));
    }

    private function applyTotals(PenjualanTransactionModel $transaction, array $payload, array $totals, array $paymentTotals): void
    {
        $transaction->forceFill([
            'subtotal_gross' => $totals['subtotal_gross'],
            'diskon_item_total' => $totals['diskon_item_total'],
            'diskon_transaksi_percent' => $totals['diskon_transaksi_percent'],
            'diskon_transaksi_nominal' => $totals['diskon_transaksi_nominal'],
            'subtotal_net' => $totals['subtotal_net'],
            'embalase' => $totals['embalase'],
            'pajak_percent' => $totals['pajak_percent'],
            'pajak_total' => $totals['pajak_total'],
            'grand_total' => $totals['grand_total'],
            'total_bayar' => $paymentTotals['total_bayar'],
            'kembalian' => $paymentTotals['kembalian'],
            'sisa_tagihan' => $paymentTotals['sisa_tagihan'],
            'payment_status' => $paymentTotals['payment_status'],
        ])->save();
    }

    private function persistPayments(PenjualanTransactionModel $transaction, array $payload, float $grandTotal): array
    {
        $transaction->payments()->delete();
        $type = $this->transactionType($payload['jenis_transaksi'] ?? $transaction->jenis_transaksi);
        $isCreditType = in_array($type, ['penjualan_kredit', 'penjualan_instansi'], true);
        $payments = collect($payload['payments'] ?? [])
            ->map(function ($payment) {
                return [
                    'metode' => $this->paymentMethod($payment['metode'] ?? $payment['method'] ?? ''),
                    'amount' => $this->money($payment['amount'] ?? 0),
                    'reference_no' => $this->nullableString($payment['reference_no'] ?? null),
                    'catatan' => $this->nullableString($payment['catatan'] ?? null),
                ];
            })
            ->filter(fn ($payment) => $payment['metode'] !== '' && $payment['amount'] > 0)
            ->values();

        if ($payments->isEmpty() && $isCreditType) {
            $payments->push([
                'metode' => $type === 'penjualan_instansi' ? 'instansi' : 'piutang',
                'amount' => 0,
                'reference_no' => null,
                'catatan' => 'Penjualan dicatat sebagai tagihan.',
            ]);
        }

        if ($payments->isEmpty()) {
            throw ValidationException::withMessages([
                'payments' => 'Pembayaran wajib diisi sebelum transaksi disimpan.',
            ]);
        }

        $totalPaid = round((float) $payments->sum('amount'), 2);
        $remaining = round(max(0, $grandTotal - $totalPaid), 2);

        if (! $isCreditType && $remaining > 0.009) {
            throw ValidationException::withMessages([
                'payments' => 'Jumlah pembayaran belum mencukupi total transaksi.',
            ]);
        }

        foreach ($payments as $payment) {
            PenjualanPaymentModel::create([
                'penjualan_transaction_id' => $transaction->id,
                'metode' => $payment['metode'],
                'amount' => $payment['amount'],
                'reference_no' => $payment['reference_no'],
                'catatan' => $payment['catatan'],
                'paid_at' => now(),
                'received_by' => Auth::id(),
            ]);
        }

        return [
            'total_bayar' => $totalPaid,
            'kembalian' => round(max(0, $totalPaid - $grandTotal), 2),
            'sisa_tagihan' => $remaining,
        ];
    }

    private function transactionType(string $type): string
    {
        return array_key_exists($type, self::TRANSACTION_TYPES) ? $type : 'penjualan_bebas';
    }

    private function paymentMethod(string $method): string
    {
        return array_key_exists($method, self::PAYMENT_METHODS) ? $method : '';
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

    private function quantity($value): float
    {
        $value = round((float) ($value ?: 0), 2);

        if ($value <= 0) {
            throw ValidationException::withMessages([
                'qty' => 'Qty jual harus lebih dari 0.',
            ]);
        }

        return $value;
    }

    private function percent($value): float
    {
        return round(min(100, max(0, (float) ($value ?: 0))), 2);
    }

    private function money($value): float
    {
        return round(max(0, (float) ($value ?: 0)), 2);
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
