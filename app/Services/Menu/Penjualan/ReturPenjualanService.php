<?php

namespace App\Services\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanBatchModel;
use App\Models\Menu\Penjualan\ReturPenjualanDetailModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Services\Menu\Stok\StockService;
use App\Services\Settings\Auth\RoleSettingService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReturPenjualanService
{
    public const REFUND_METHODS = [
        'tunai' => 'Tunai',
        'debit' => 'Kartu Debit',
        'credit_card' => 'Kartu Kredit',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'ewallet' => 'E-Wallet',
        'potong_piutang' => 'Potong Piutang',
        'lainnya' => 'Lainnya',
    ];

    private const QUANTITY_EPSILON = 0.00001;

    public function __construct(
        private readonly StockService $stockService,
        private readonly RoleSettingService $roleSettings
    ) {}

    public function accessibleBranchIds(): array
    {
        return $this->roleSettings->posBranchIds(Auth::user());
    }

    public function generateNumber(int $branchId, ?Carbon $date = null): string
    {
        $date = $date ?: now();
        $branch = BranchModel::find($branchId);
        $branchCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $branch?->code ?: 'BR'.$branchId));
        $branchCode = $branchCode !== '' ? $branchCode : 'BR'.$branchId;
        $prefix = 'RPJ-'.$date->format('Ym').'-'.$branchCode.'-';
        $sequence = ReturPenjualanModel::where('branch_id', $branchId)
            ->where('nomor_retur', 'like', $prefix.'%')
            ->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (ReturPenjualanModel::where('nomor_retur', $number)->exists());

        return $number;
    }

    public function transactionOptions(string $search = '', int $limit = 50): array
    {
        $search = trim($search);
        $transactions = PenjualanTransactionModel::query()
            ->with([
                'branch',
                'details.salesReturnDetails.salesReturn',
            ])
            ->whereIn('branch_id', $this->accessibleBranchIds())
            ->where('status', 'completed')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(function ($query) use ($like) {
                    $query->where('nomor_transaksi', 'like', $like)
                        ->orWhere('customer_name', 'like', $like)
                        ->orWhere('customer_phone', 'like', $like);
                });
            })
            ->latest('tanggal_transaksi')
            ->latest('id')
            ->limit(min(100, max(1, $limit)))
            ->get();

        return $transactions
            ->map(function (PenjualanTransactionModel $transaction) {
                $returnableItems = $transaction->details->filter(function (PenjualanTransactionDetailModel $detail) {
                    $returned = $detail->salesReturnDetails
                        ->filter(fn (ReturPenjualanDetailModel $returnDetail) => in_array($returnDetail->salesReturn?->status, ['draft', 'posted'], true))
                        ->sum('qty_jual');

                    return (float) $detail->qty_jual > (float) $returned + self::QUANTITY_EPSILON;
                })->count();

                if ($returnableItems === 0) {
                    return null;
                }

                return [
                    'id' => $transaction->id,
                    'text' => $transaction->nomor_transaksi.' - '.($transaction->customer_name ?: 'Umum').' - '.($transaction->branch?->name ?: '-'),
                    'nomor_transaksi' => $transaction->nomor_transaksi,
                    'tanggal_transaksi' => optional($transaction->tanggal_transaksi)->format('Y-m-d H:i'),
                    'customer_name' => $transaction->customer_name ?: 'Umum',
                    'branch' => $transaction->branch?->name ?: '-',
                    'grand_total' => (float) $transaction->grand_total,
                    'returnable_items' => $returnableItems,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function transactionPayload(PenjualanTransactionModel $transaction, ?int $ignoreReturnId = null): array
    {
        $transaction->loadMissing(['branch', 'details.batchAllocations']);
        $financials = $this->originalFinancialAllocations($transaction);
        $returned = $this->activeReturnTotals($transaction->id, $ignoreReturnId);

        return [
            'id' => $transaction->id,
            'branch_id' => $transaction->branch_id,
            'branch' => $transaction->branch?->name ?: '-',
            'nomor_transaksi' => $transaction->nomor_transaksi,
            'tanggal_transaksi' => optional($transaction->tanggal_transaksi)->format('Y-m-d H:i'),
            'customer_name' => $transaction->customer_name ?: 'Umum',
            'customer_phone' => $transaction->customer_phone,
            'grand_total' => (float) $transaction->grand_total,
            'embalase' => (float) $transaction->embalase,
            'details' => $transaction->details->map(function (PenjualanTransactionDetailModel $detail) use ($financials, $returned) {
                $originalQty = (float) $detail->qty_jual;
                $returnedDetail = $returned['details']->get($detail->id, $this->emptyReturnTotals());
                $returnableQty = max(0, round($originalQty - (float) $returnedDetail['qty_jual'], 2));
                $original = $financials[$detail->id];
                $remainingValue = $this->remainingFinancialValues($original, $returnedDetail);

                return [
                    'id' => $detail->id,
                    'obat_id' => $detail->obat_id,
                    'kode_obat' => $detail->kode_obat,
                    'nama_obat' => $detail->nama_obat,
                    'satuan_jual' => $detail->satuan_jual,
                    'satuan_stok' => $detail->satuan_stok,
                    'konversi' => (float) $detail->konversi,
                    'qty_jual' => $originalQty,
                    'returned_qty' => (float) $returnedDetail['qty_jual'],
                    'returnable_qty' => $returnableQty,
                    'harga_jual' => (float) $detail->harga_jual,
                    'returnable_value' => $remainingValue['total'],
                    'batches' => $detail->batchAllocations->map(function ($batch) use ($returned) {
                        $returnedQty = (float) ($returned['batches']->get($batch->id) ?? 0);

                        return [
                            'id' => $batch->id,
                            'stok_batch_id' => $batch->stok_batch_id,
                            'no_batch' => $batch->no_batch,
                            'expired_date' => optional($batch->expired_date)->format('Y-m-d'),
                            'qty_stok' => (float) $batch->qty_stok,
                            'returned_qty_stok' => $returnedQty,
                            'returnable_qty_stok' => max(0, round((float) $batch->qty_stok - $returnedQty, 2)),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    public function createDraft(array $payload): ReturPenjualanModel
    {
        return DB::transaction(function () use ($payload) {
            $transaction = $this->lockAccessibleTransaction((int) $payload['penjualan_transaction_id']);
            $this->assertReturnableTransaction($transaction);
            $computed = $this->buildDetails($transaction, $payload['details'], null);
            $date = $this->returnDate($payload['tanggal_retur'], $transaction);

            $return = ReturPenjualanModel::create($this->headerPayload(
                $payload,
                $transaction,
                $computed,
                $this->generateNumber((int) $transaction->branch_id, $date),
                $date
            ));
            $this->persistDetails($return, $computed['details']);

            return $return->fresh($this->returnRelations());
        });
    }

    public function updateDraft(ReturPenjualanModel $return, array $payload): ReturPenjualanModel
    {
        return DB::transaction(function () use ($return, $payload) {
            $return = $this->lockAccessibleReturn($return->id);

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'Hanya retur berstatus draft yang dapat diubah.']);
            }

            $transaction = $this->lockAccessibleTransaction((int) $payload['penjualan_transaction_id']);
            $this->assertReturnableTransaction($transaction);
            $computed = $this->buildDetails($transaction, $payload['details'], $return->id);
            $date = $this->returnDate($payload['tanggal_retur'], $transaction);

            $return->details()->delete();
            $return->forceFill($this->headerPayload($payload, $transaction, $computed, $return->nomor_retur, $date, false))->save();
            $this->persistDetails($return, $computed['details']);

            return $return->fresh($this->returnRelations());
        });
    }

    public function post(ReturPenjualanModel $return): ReturPenjualanModel
    {
        return DB::transaction(function () use ($return) {
            $return = $this->lockAccessibleReturn($return->id);

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'Hanya retur berstatus draft yang dapat diposting.']);
            }

            $transaction = $this->lockAccessibleTransaction((int) $return->penjualan_transaction_id);
            $this->assertReturnableTransaction($transaction);
            $return->load(['details.batchAllocations']);
            $this->assertStoredQuantitiesRemainValid($return, $transaction);

            $return->forceFill([
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => Auth::id(),
            ])->save();

            foreach ($return->details as $detail) {
                foreach ($detail->batchAllocations as $batchAllocation) {
                    if ($batchAllocation->kartu_stok_id) {
                        continue;
                    }

                    $movement = $this->stockService->recordSaleReturn($return, $detail, $batchAllocation, Auth::id());
                    $batchAllocation->forceFill(['kartu_stok_id' => $movement->id])->save();
                }
            }

            return $return->fresh($this->returnRelations());
        });
    }

    public function cancel(ReturPenjualanModel $return, string $reason): ReturPenjualanModel
    {
        return DB::transaction(function () use ($return, $reason) {
            $return = $this->lockAccessibleReturn($return->id);

            if ($return->status === 'cancelled') {
                throw ValidationException::withMessages(['status' => 'Retur penjualan sudah dibatalkan.']);
            }

            $return->load(['details.batchAllocations']);
            $return->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => trim($reason),
            ])->save();

            if ($return->posted_at) {
                foreach ($return->details as $detail) {
                    foreach ($detail->batchAllocations as $batchAllocation) {
                        if (! $batchAllocation->kartu_stok_id || $batchAllocation->cancel_kartu_stok_id) {
                            continue;
                        }

                        $movement = $this->stockService->recordSaleReturnCancellation($return, $detail, $batchAllocation, Auth::id());
                        $batchAllocation->forceFill(['cancel_kartu_stok_id' => $movement->id])->save();
                    }
                }
            }

            return $return->fresh($this->returnRelations());
        });
    }

    public function deleteDraft(ReturPenjualanModel $return): void
    {
        DB::transaction(function () use ($return) {
            $return = $this->lockAccessibleReturn($return->id);

            if ($return->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'Hanya retur berstatus draft yang dapat dihapus.']);
            }

            $return->delete();
        });
    }

    public function findAccessibleReturn(int $id): ReturPenjualanModel
    {
        return ReturPenjualanModel::whereIn('branch_id', $this->accessibleBranchIds())->findOrFail($id);
    }

    public function findAccessibleTransaction(int $id): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::whereIn('branch_id', $this->accessibleBranchIds())->findOrFail($id);
    }

    private function buildDetails(PenjualanTransactionModel $transaction, array $items, ?int $ignoreReturnId): array
    {
        $transaction->load(['details.batchAllocations']);
        $requested = collect($items)
            ->mapWithKeys(fn (array $item) => [(int) $item['penjualan_transaction_detail_id'] => $item]);

        if ($requested->count() !== count($items)) {
            throw ValidationException::withMessages(['details' => 'Item retur tidak boleh duplikat.']);
        }

        $saleDetails = $transaction->details->keyBy('id');
        $financials = $this->originalFinancialAllocations($transaction);
        $returned = $this->activeReturnTotals($transaction->id, $ignoreReturnId);
        $computedDetails = [];

        foreach ($requested as $detailId => $item) {
            /** @var PenjualanTransactionDetailModel|null $saleDetail */
            $saleDetail = $saleDetails->get($detailId);

            if (! $saleDetail) {
                throw ValidationException::withMessages(['details' => 'Item yang dipilih bukan bagian dari transaksi penjualan.']);
            }

            $qty = round((float) ($item['qty'] ?? 0), 2);
            $existing = $returned['details']->get($detailId, $this->emptyReturnTotals());
            $availableQty = max(0, round((float) $saleDetail->qty_jual - (float) $existing['qty_jual'], 2));

            if ($qty <= 0) {
                throw ValidationException::withMessages(['details' => 'Qty retur harus lebih dari 0.']);
            }

            if ($qty > $availableQty + self::QUANTITY_EPSILON) {
                throw ValidationException::withMessages([
                    'details' => 'Qty retur '.$saleDetail->nama_obat.' melebihi sisa yang dapat diretur ('.number_format($availableQty, 2, ',', '.').' '.$saleDetail->satuan_jual.').',
                ]);
            }

            $conversion = max(0.0001, (float) $saleDetail->konversi);
            $qtyStock = round($qty * $conversion, 2);
            $batches = $this->allocateReturnBatches($saleDetail, $qtyStock, $returned['batches']);
            $isFinal = abs($qty - $availableQty) <= self::QUANTITY_EPSILON;
            $original = $financials[$detailId];
            $subtotalGross = $this->returnComponent($original['subtotal_gross'], $existing['subtotal_gross'], $qty, (float) $saleDetail->qty_jual, $isFinal);
            $discountItem = $this->returnComponent($original['diskon_item_nominal'], $existing['diskon_item_nominal'], $qty, (float) $saleDetail->qty_jual, $isFinal);
            $discountTransaction = $this->returnComponent($original['diskon_transaksi_nominal'], $existing['diskon_transaksi_nominal'], $qty, (float) $saleDetail->qty_jual, $isFinal);
            $tax = $this->returnComponent($original['pajak_nominal'], $existing['pajak_nominal'], $qty, (float) $saleDetail->qty_jual, $isFinal);
            $total = round(max(0, $subtotalGross - $discountItem - $discountTransaction + $tax), 2);

            $computedDetails[] = [
                'detail' => [
                    'penjualan_transaction_detail_id' => $saleDetail->id,
                    'obat_id' => $saleDetail->obat_id,
                    'satuan_id' => $saleDetail->satuan_id,
                    'kode_obat' => $saleDetail->kode_obat,
                    'nama_obat' => $saleDetail->nama_obat,
                    'satuan_jual' => $saleDetail->satuan_jual,
                    'satuan_stok' => $saleDetail->satuan_stok,
                    'konversi' => $conversion,
                    'qty_jual' => $qty,
                    'qty_stok' => $qtyStock,
                    'harga_jual' => (float) $saleDetail->harga_jual,
                    'subtotal_gross' => $subtotalGross,
                    'diskon_item_nominal' => $discountItem,
                    'diskon_transaksi_nominal' => $discountTransaction,
                    'pajak_nominal' => $tax,
                    'total' => $total,
                    'alasan_item' => $this->nullableString($item['alasan_item'] ?? null),
                ],
                'batches' => $batches,
            ];
        }

        return [
            'details' => $computedDetails,
            'total_item' => count($computedDetails),
            'total_qty' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['qty_jual']), 2),
            'subtotal_gross' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['subtotal_gross']), 2),
            'diskon_item_total' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['diskon_item_nominal']), 2),
            'diskon_transaksi_total' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['diskon_transaksi_nominal']), 2),
            'pajak_total' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['pajak_nominal']), 2),
            'grand_total' => round(collect($computedDetails)->sum(fn ($row) => $row['detail']['total']), 2),
        ];
    }

    private function allocateReturnBatches(PenjualanTransactionDetailModel $saleDetail, float $qtyStock, Collection $returnedBatches): array
    {
        $remaining = $qtyStock;
        $allocations = [];

        foreach ($saleDetail->batchAllocations->sortBy('id') as $saleBatch) {
            if ($remaining <= self::QUANTITY_EPSILON) {
                break;
            }

            if (! $saleBatch->stok_batch_id) {
                continue;
            }

            $alreadyReturned = (float) ($returnedBatches->get($saleBatch->id) ?? 0);
            $available = max(0, round((float) $saleBatch->qty_stok - $alreadyReturned, 2));
            $take = min($available, $remaining);

            if ($take <= self::QUANTITY_EPSILON) {
                continue;
            }

            $allocations[] = [
                'penjualan_transaction_batch_id' => $saleBatch->id,
                'branch_id' => $saleBatch->branch_id,
                'obat_id' => $saleBatch->obat_id,
                'stok_batch_id' => $saleBatch->stok_batch_id,
                'no_batch' => $saleBatch->no_batch,
                'expired_date' => optional($saleBatch->expired_date)->format('Y-m-d'),
                'qty_stok' => round($take, 2),
            ];
            $remaining = round($remaining - $take, 2);
        }

        if ($remaining > self::QUANTITY_EPSILON) {
            throw ValidationException::withMessages([
                'details' => 'Alokasi batch asal untuk '.$saleDetail->nama_obat.' tidak mencukupi qty retur.',
            ]);
        }

        return $allocations;
    }

    private function activeReturnTotals(int $transactionId, ?int $ignoreReturnId = null): array
    {
        $detailRows = ReturPenjualanDetailModel::query()
            ->join('retur_penjualan', 'retur_penjualan.id', '=', 'retur_penjualan_details.retur_penjualan_id')
            ->where('retur_penjualan.penjualan_transaction_id', $transactionId)
            ->whereIn('retur_penjualan.status', ['draft', 'posted'])
            ->when($ignoreReturnId, fn ($query) => $query->where('retur_penjualan.id', '!=', $ignoreReturnId))
            ->groupBy('retur_penjualan_details.penjualan_transaction_detail_id')
            ->selectRaw('retur_penjualan_details.penjualan_transaction_detail_id as detail_id')
            ->selectRaw('SUM(retur_penjualan_details.qty_jual) as qty_jual')
            ->selectRaw('SUM(retur_penjualan_details.subtotal_gross) as subtotal_gross')
            ->selectRaw('SUM(retur_penjualan_details.diskon_item_nominal) as diskon_item_nominal')
            ->selectRaw('SUM(retur_penjualan_details.diskon_transaksi_nominal) as diskon_transaksi_nominal')
            ->selectRaw('SUM(retur_penjualan_details.pajak_nominal) as pajak_nominal')
            ->selectRaw('SUM(retur_penjualan_details.total) as total')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->detail_id => [
                'qty_jual' => (float) $row->qty_jual,
                'subtotal_gross' => (float) $row->subtotal_gross,
                'diskon_item_nominal' => (float) $row->diskon_item_nominal,
                'diskon_transaksi_nominal' => (float) $row->diskon_transaksi_nominal,
                'pajak_nominal' => (float) $row->pajak_nominal,
                'total' => (float) $row->total,
            ]]);

        $batchRows = ReturPenjualanBatchModel::query()
            ->join('retur_penjualan_details', 'retur_penjualan_details.id', '=', 'retur_penjualan_batches.retur_penjualan_detail_id')
            ->join('retur_penjualan', 'retur_penjualan.id', '=', 'retur_penjualan_details.retur_penjualan_id')
            ->where('retur_penjualan.penjualan_transaction_id', $transactionId)
            ->whereIn('retur_penjualan.status', ['draft', 'posted'])
            ->when($ignoreReturnId, fn ($query) => $query->where('retur_penjualan.id', '!=', $ignoreReturnId))
            ->groupBy('retur_penjualan_batches.penjualan_transaction_batch_id')
            ->selectRaw('retur_penjualan_batches.penjualan_transaction_batch_id as batch_id')
            ->selectRaw('SUM(retur_penjualan_batches.qty_stok) as qty_stok')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->batch_id => (float) $row->qty_stok]);

        return ['details' => $detailRows, 'batches' => $batchRows];
    }

    private function originalFinancialAllocations(PenjualanTransactionModel $transaction): array
    {
        $details = $transaction->details->sortBy('id')->values();
        $transactionDiscounts = $this->allocateMoney(
            $details,
            (float) $transaction->diskon_transaksi_nominal,
            fn (PenjualanTransactionDetailModel $detail) => (float) $detail->subtotal_net
        );
        $taxAllocations = $this->allocateMoney(
            $details,
            (float) $transaction->pajak_total,
            fn (PenjualanTransactionDetailModel $detail) => max(0, (float) $detail->subtotal_net - ($transactionDiscounts[$detail->id] ?? 0))
        );

        return $details->mapWithKeys(fn (PenjualanTransactionDetailModel $detail) => [$detail->id => [
            'subtotal_gross' => (float) $detail->subtotal_gross,
            'diskon_item_nominal' => (float) $detail->diskon_nominal,
            'diskon_transaksi_nominal' => (float) ($transactionDiscounts[$detail->id] ?? 0),
            'pajak_nominal' => (float) ($taxAllocations[$detail->id] ?? 0),
        ]])->all();
    }

    private function allocateMoney(Collection $details, float $amount, callable $weight): array
    {
        $amount = round(max(0, $amount), 2);
        $weights = $details->mapWithKeys(fn (PenjualanTransactionDetailModel $detail) => [$detail->id => max(0, (float) $weight($detail))]);
        $totalWeight = (float) $weights->sum();
        $remaining = $amount;
        $allocations = [];
        $eligible = $details->filter(fn (PenjualanTransactionDetailModel $detail) => ($weights[$detail->id] ?? 0) > 0)->values();

        foreach ($eligible as $index => $detail) {
            $isLast = $index === $eligible->count() - 1;
            $allocated = $isLast ? $remaining : round($amount * (($weights[$detail->id] ?? 0) / $totalWeight), 2);
            $allocated = min($remaining, max(0, $allocated));
            $allocations[$detail->id] = $allocated;
            $remaining = round($remaining - $allocated, 2);
        }

        return $allocations;
    }

    private function returnComponent(float $original, float $returned, float $qty, float $originalQty, bool $isFinal): float
    {
        $remaining = max(0, round($original - $returned, 2));

        if ($isFinal) {
            return $remaining;
        }

        return min($remaining, round($originalQty > 0 ? $original * ($qty / $originalQty) : 0, 2));
    }

    private function remainingFinancialValues(array $original, array $returned): array
    {
        $gross = max(0, round($original['subtotal_gross'] - $returned['subtotal_gross'], 2));
        $itemDiscount = max(0, round($original['diskon_item_nominal'] - $returned['diskon_item_nominal'], 2));
        $transactionDiscount = max(0, round($original['diskon_transaksi_nominal'] - $returned['diskon_transaksi_nominal'], 2));
        $tax = max(0, round($original['pajak_nominal'] - $returned['pajak_nominal'], 2));

        return [
            'subtotal_gross' => $gross,
            'diskon_item_nominal' => $itemDiscount,
            'diskon_transaksi_nominal' => $transactionDiscount,
            'pajak_nominal' => $tax,
            'total' => round(max(0, $gross - $itemDiscount - $transactionDiscount + $tax), 2),
        ];
    }

    private function headerPayload(array $payload, PenjualanTransactionModel $transaction, array $computed, string $number, Carbon $date, bool $includeCreator = true): array
    {
        $data = [
            'branch_id' => $transaction->branch_id,
            'penjualan_transaction_id' => $transaction->id,
            'nomor_retur' => $number,
            'tanggal_retur' => $date->toDateString(),
            'status' => 'draft',
            'refund_method' => $payload['refund_method'] ?? null,
            'refund_reference' => $this->nullableString($payload['refund_reference'] ?? null),
            'total_item' => $computed['total_item'],
            'total_qty' => $computed['total_qty'],
            'subtotal_gross' => $computed['subtotal_gross'],
            'diskon_item_total' => $computed['diskon_item_total'],
            'diskon_transaksi_total' => $computed['diskon_transaksi_total'],
            'pajak_total' => $computed['pajak_total'],
            'grand_total' => $computed['grand_total'],
            'alasan' => $this->nullableString($payload['alasan'] ?? null),
            'catatan' => $this->nullableString($payload['catatan'] ?? null),
        ];

        if ($includeCreator) {
            $data['created_by'] = Auth::id();
        }

        return $data;
    }

    private function persistDetails(ReturPenjualanModel $return, array $details): void
    {
        foreach ($details as $row) {
            $detail = $return->details()->create($row['detail']);
            $detail->batchAllocations()->createMany($row['batches']);
        }
    }

    private function assertStoredQuantitiesRemainValid(ReturPenjualanModel $return, PenjualanTransactionModel $transaction): void
    {
        $otherReturns = $this->activeReturnTotals($transaction->id, $return->id);
        $saleDetails = $transaction->details()->get()->keyBy('id');

        foreach ($return->details as $detail) {
            $saleDetail = $saleDetails->get($detail->penjualan_transaction_detail_id);
            $otherQty = (float) ($otherReturns['details']->get($detail->penjualan_transaction_detail_id)['qty_jual'] ?? 0);

            if (! $saleDetail || $otherQty + (float) $detail->qty_jual > (float) $saleDetail->qty_jual + self::QUANTITY_EPSILON) {
                throw ValidationException::withMessages(['details' => 'Qty retur tidak lagi tersedia. Muat ulang dan periksa draft retur lainnya.']);
            }
        }
    }

    private function assertReturnableTransaction(PenjualanTransactionModel $transaction): void
    {
        if ($transaction->status !== 'completed') {
            throw ValidationException::withMessages(['penjualan_transaction_id' => 'Hanya transaksi penjualan selesai yang dapat diretur.']);
        }
    }

    private function returnDate(string $value, PenjualanTransactionModel $transaction): Carbon
    {
        $date = Carbon::parse($value)->startOfDay();

        if ($transaction->tanggal_transaksi && $date->lt($transaction->tanggal_transaksi->copy()->startOfDay())) {
            throw ValidationException::withMessages(['tanggal_retur' => 'Tanggal retur tidak boleh lebih awal dari tanggal transaksi.']);
        }

        return $date;
    }

    private function lockAccessibleTransaction(int $id): PenjualanTransactionModel
    {
        return PenjualanTransactionModel::whereIn('branch_id', $this->accessibleBranchIds())
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function lockAccessibleReturn(int $id): ReturPenjualanModel
    {
        return ReturPenjualanModel::whereIn('branch_id', $this->accessibleBranchIds())
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function returnRelations(): array
    {
        return [
            'branch',
            'transaction',
            'details.batchAllocations',
            'createdBy',
            'postedBy',
            'cancelledBy',
        ];
    }

    private function emptyReturnTotals(): array
    {
        return [
            'qty_jual' => 0.0,
            'subtotal_gross' => 0.0,
            'diskon_item_nominal' => 0.0,
            'diskon_transaksi_nominal' => 0.0,
            'pajak_nominal' => 0.0,
            'total' => 0.0,
        ];
    }

    private function nullableString($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
