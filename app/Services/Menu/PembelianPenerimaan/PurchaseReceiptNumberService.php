<?php

namespace App\Services\Menu\PembelianPenerimaan;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class PurchaseReceiptNumberService
{
    public function previewPurchaseOrder(): string
    {
        return $this->preview('PO', 'purchase_orders', 'no_po');
    }

    public function nextPurchaseOrder(): string
    {
        return $this->reserve('PO', 'purchase_orders', 'no_po');
    }

    public function previewReceipt(): string
    {
        return $this->preview('PB', 'penerimaan_barang', 'nomor_penerimaan');
    }

    public function nextReceipt(): string
    {
        return $this->reserve('PB', 'penerimaan_barang', 'nomor_penerimaan');
    }

    public function validateManualPurchaseOrder(string $number, ?int $ignoreId = null): void
    {
        $this->requireTransaction();

        // Manual numbers may use the automatic format, so share its monthly lock.
        if (preg_match('/^(PO-\d{4}-\d{2}-)\d+$/iD', $number, $matches)) {
            $this->lockSequence(strtoupper($matches[1]));
        }

        $existing = DB::table('purchase_orders')->where('no_po', $number)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->lockForUpdate()->first(['id']);

        if ($existing) {
            throw ValidationException::withMessages([
                'no_po' => 'Nomor PO sudah digunakan.',
            ]);
        }
    }

    private function preview(string $type, string $table, string $column): string
    {
        $prefix = $this->prefix($type);
        $lastNumber = (int) DB::table('purchase_receipt_number_sequences')
            ->where('prefix', $prefix)->value('last_number');

        return $this->format($prefix, max($lastNumber, $this->existingMaximum($table, $column, $prefix)) + 1);
    }

    private function reserve(string $type, string $table, string $column): string
    {
        $this->requireTransaction();
        $prefix = $this->prefix($type);
        $lastNumber = $this->lockSequence($prefix);
        $nextNumber = max($lastNumber, $this->existingMaximum($table, $column, $prefix, true)) + 1;

        DB::table('purchase_receipt_number_sequences')->where('prefix', $prefix)
            ->update(['last_number' => $nextNumber]);

        return $this->format($prefix, $nextNumber);
    }

    private function lockSequence(string $prefix): int
    {
        // The unique prefix also serializes the first submissions of a new month.
        DB::table('purchase_receipt_number_sequences')->upsert([
            'prefix' => $prefix,
            'last_number' => 0,
        ], ['prefix'], ['prefix']);

        return (int) DB::table('purchase_receipt_number_sequences')
            ->where('prefix', $prefix)->lockForUpdate()->value('last_number');
    }

    private function existingMaximum(string $table, string $column, string $prefix, bool $lock = false): int
    {
        $query = DB::table($table)->where($column, 'like', $prefix.'%');

        if ($lock) {
            // A locking read sees committed numbers even with MySQL repeatable read.
            $query->lockForUpdate();
        }

        return (int) ($query->pluck($column)->map(static function (string $number) use ($prefix): int {
            $sequence = substr($number, strlen($prefix));

            return ctype_digit($sequence) ? (int) $sequence : 0;
        })->max() ?? 0);
    }

    private function prefix(string $type): string
    {
        return $type.'-'.now()->format('Y-m').'-';
    }

    private function format(string $prefix, int $number): string
    {
        return $prefix.str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    private function requireTransaction(): void
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Document numbers must be assigned inside the document transaction.');
        }
    }
}
