<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Faktur;

use App\Http\Controllers\Controller;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class FakturController extends Controller
{
    public function faktur()
    {
        return view('medcare.menu.pembelianPenerimaan.faktur.faktur');
    }

    public function table(Request $request)
    {
        $query = $this->fakturQuery([
            'purchaseOrder.branch',
            'purchaseOrder.distributor',
            'distributor',
            'createdBy',
        ])->latest(DB::raw('COALESCE(tanggal_faktur, tanggal_penerimaan)'))
            ->latest('id');

        if ($request->filled('date_start')) {
            $query->whereDate(DB::raw('COALESCE(tanggal_faktur, tanggal_penerimaan)'), '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate(DB::raw('COALESCE(tanggal_faktur, tanggal_penerimaan)'), '<=', $request->date_end);
        }

        if ($request->filled('payment_status')) {
            $query->where('status_pembayaran', $request->payment_status);
        }

        if ($request->filled('receipt_status')) {
            $query->where('status', $request->receipt_status);
        }

        $this->applyDueFilter($query, (string) $request->input('due_status', ''));

        $faktur = $query->get();
        $summary = $this->summary($faktur);

        return DataTables::of($faktur)
            ->addIndexColumn()
            ->addColumn('no_po', fn (PenerimaanBarangModel $row) => $row->purchaseOrder->no_po ?? '-')
            ->addColumn('branch', fn (PenerimaanBarangModel $row) => $row->purchaseOrder->branch->name ?? '-')
            ->addColumn('supplier', fn (PenerimaanBarangModel $row) => $row->distributor->nama ?? ($row->purchaseOrder->distributor->nama ?? '-'))
            ->addColumn('tanggal_faktur_iso', fn (PenerimaanBarangModel $row) => optional($row->tanggal_faktur ?: $row->tanggal_penerimaan)->format('Y-m-d'))
            ->addColumn('tanggal_jatuh_tempo_iso', fn (PenerimaanBarangModel $row) => optional($row->tanggal_jatuh_tempo)->format('Y-m-d'))
            ->addColumn('payment_status_label', fn (PenerimaanBarangModel $row) => $this->paymentStatusLabel((string) $row->status_pembayaran))
            ->addColumn('due_state', fn (PenerimaanBarangModel $row) => $this->dueState($row))
            ->addColumn('due_label', fn (PenerimaanBarangModel $row) => $this->dueLabel($row))
            ->addColumn('payment_progress', fn (PenerimaanBarangModel $row) => $this->paymentProgress($row))
            ->addColumn('total_faktur_value', fn (PenerimaanBarangModel $row) => $this->invoiceTotal($row))
            ->addColumn('jumlah_dibayar_value', fn (PenerimaanBarangModel $row) => (float) ($row->jumlah_dibayar ?? 0))
            ->addColumn('sisa_hutang_value', fn (PenerimaanBarangModel $row) => $this->remainingDebt($row))
            ->addColumn('user', fn (PenerimaanBarangModel $row) => $row->createdBy->name ?? '-')
            ->addColumn('actions', fn (PenerimaanBarangModel $row) => $this->actionButtons($row))
            ->rawColumns(['actions'])
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function show($id)
    {
        return response()->json($this->fakturPayload((int) $id));
    }

    public function update(Request $request, $id)
    {
        $this->normalizeMoneyFields($request, ['biaya_lain', 'jumlah_dibayar']);

        $request->validate([
            'nomor_faktur' => ['required', 'string', 'max:100'],
            'tanggal_faktur' => ['required', 'string'],
            'tanggal_jatuh_tempo' => ['nullable', 'string'],
            'biaya_lain' => ['nullable', 'numeric', 'min:0'],
            'jumlah_dibayar' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $id) {
            $faktur = $this->fakturQuery()->lockForUpdate()->findOrFail($id);

            $this->ensureEditable($faktur);

            $biayaLain = $this->moneyValue($request->input('biaya_lain'));
            $totalFaktur = $this->computedInvoiceTotal($faktur, $biayaLain);
            $jumlahDibayar = min($this->moneyValue($request->input('jumlah_dibayar')), $totalFaktur);
            $sisaHutang = max(0, $totalFaktur - $jumlahDibayar);

            $faktur->update([
                'nomor_faktur' => trim((string) $request->nomor_faktur),
                'tanggal_faktur' => $this->parseDate($request->tanggal_faktur, 'tanggal_faktur'),
                'tanggal_jatuh_tempo' => $this->parseNullableDate($request->tanggal_jatuh_tempo, 'tanggal_jatuh_tempo'),
                'biaya_lain' => $biayaLain,
                'total_faktur' => $totalFaktur,
                'grand_total' => $totalFaktur,
                'jumlah_dibayar' => $jumlahDibayar,
                'sisa_hutang' => $sisaHutang,
                'status_pembayaran' => $this->paymentStatus($totalFaktur, $jumlahDibayar),
                'catatan' => $request->catatan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Faktur berhasil diperbarui.',
                'data' => $this->fakturPayload((int) $faktur->id),
            ]);
        });
    }

    public function markPaid($id)
    {
        return DB::transaction(function () use ($id) {
            $faktur = $this->fakturQuery()->lockForUpdate()->findOrFail($id);

            $this->ensureEditable($faktur);

            $totalFaktur = $this->invoiceTotal($faktur);

            $faktur->update([
                'jumlah_dibayar' => $totalFaktur,
                'sisa_hutang' => 0,
                'status_pembayaran' => 'lunas',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Faktur ditandai lunas.',
            ]);
        });
    }

    public function resetPayment($id)
    {
        return DB::transaction(function () use ($id) {
            $faktur = $this->fakturQuery()->lockForUpdate()->findOrFail($id);

            $this->ensureEditable($faktur);

            $totalFaktur = $this->invoiceTotal($faktur);

            $faktur->update([
                'jumlah_dibayar' => 0,
                'sisa_hutang' => $totalFaktur,
                'status_pembayaran' => 'belum_dibayar',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran faktur direset.',
            ]);
        });
    }

    private function fakturPayload(int $id): array
    {
        $faktur = $this->fakturQuery([
            'purchaseOrder.branch',
            'purchaseOrder.distributor',
            'distributor',
            'createdBy',
            'postedBy',
            'cancelledBy',
            'details.obat.satuan',
            'details.purchaseOrderDetail.satuanKonversi.satuan',
        ])->findOrFail($id);

        $details = $faktur->details
            ->map(fn ($detail) => [
                'id' => $detail->id,
                'kode_obat' => $detail->obat->kode_obat ?? '-',
                'nama_obat' => $detail->obat->nama_obat ?? '-',
                'satuan_beli' => $detail->satuan_beli ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama ?? '-'),
                'satuan_stok' => $detail->satuan_stok ?: ($detail->obat->satuan->nama ?? '-'),
                'qty_diterima' => (float) ($detail->qty_diterima ?? 0),
                'qty_diterima_stok' => (float) ($detail->qty_diterima_stok ?? 0),
                'konversi_satuan' => (float) ($detail->konversi_satuan ?? 1),
                'no_batch' => $detail->no_batch,
                'expired_date' => optional($detail->expired_date)->format('Y-m-d'),
                'harga_beli' => (float) ($detail->harga_beli ?? 0),
                'diskon' => (float) ($detail->diskon ?? 0),
                'ppn' => (float) ($detail->ppn ?? 0),
                'subtotal' => (float) ($detail->subtotal ?? 0),
                'nilai_diskon' => (float) ($detail->nilai_diskon ?? 0),
                'nilai_ppn' => (float) ($detail->nilai_ppn ?? 0),
                'total' => (float) ($detail->total ?? 0),
            ])
            ->values();

        $totalFaktur = $this->invoiceTotal($faktur);
        $jumlahDibayar = (float) ($faktur->jumlah_dibayar ?? 0);
        $sisaHutang = $this->remainingDebt($faktur);

        return [
            'header' => [
                'id' => $faktur->id,
                'nomor_faktur' => $faktur->nomor_faktur,
                'nomor_penerimaan' => $faktur->nomor_penerimaan,
                'nomor_surat_jalan' => $faktur->nomor_surat_jalan,
                'no_po' => $faktur->purchaseOrder->no_po ?? '-',
                'supplier' => $faktur->distributor->nama ?? ($faktur->purchaseOrder->distributor->nama ?? '-'),
                'branch' => $faktur->purchaseOrder->branch->name ?? '-',
                'tanggal_penerimaan' => optional($faktur->tanggal_penerimaan)->format('Y-m-d'),
                'tanggal_faktur' => optional($faktur->tanggal_faktur)->format('Y-m-d'),
                'tanggal_jatuh_tempo' => optional($faktur->tanggal_jatuh_tempo)->format('Y-m-d'),
                'status_penerimaan' => $faktur->status,
                'status_pembayaran' => $faktur->status_pembayaran,
                'payment_status_label' => $this->paymentStatusLabel((string) $faktur->status_pembayaran),
                'due_state' => $this->dueState($faktur),
                'due_label' => $this->dueLabel($faktur),
                'total_barang' => (int) ($faktur->total_barang ?? 0),
                'total_qty' => (float) ($faktur->total_qty ?? 0),
                'subtotal' => (float) ($faktur->subtotal ?? 0),
                'diskon' => (float) ($faktur->total_diskon ?? $faktur->diskon ?? 0),
                'pajak' => (float) ($faktur->total_ppn ?? $faktur->pajak ?? 0),
                'biaya_lain' => (float) ($faktur->biaya_lain ?? 0),
                'grand_total' => (float) ($faktur->grand_total ?? 0),
                'total_faktur' => $totalFaktur,
                'jumlah_dibayar' => $jumlahDibayar,
                'sisa_hutang' => $sisaHutang,
                'payment_progress' => $this->paymentProgress($faktur),
                'catatan' => $faktur->catatan,
                'created_by' => $faktur->createdBy->name ?? '-',
                'posted_by' => $faktur->postedBy->name ?? '-',
                'cancelled_by' => $faktur->cancelledBy->name ?? '-',
                'posted_at' => optional($faktur->posted_at)->format('Y-m-d H:i:s'),
                'cancelled_at' => optional($faktur->cancelled_at)->format('Y-m-d H:i:s'),
            ],
            'details' => $details,
        ];
    }

    private function actionButtons(PenerimaanBarangModel $row): string
    {
        $button = function (string $type, string $icon, string $label, string $handler) use ($row): string {
            return '<button type="button" class="invoice-action-btn is-'.$type.'" onclick="'.$handler.'('.$row->id.')" title="'.$label.'" aria-label="'.$label.'">
                <i class="mdi '.$icon.'"></i>
            </button>';
        };

        $buttons = [
            $button('detail', 'mdi-eye-outline', 'Lihat detail faktur', 'lihatFaktur'),
        ];

        if ($row->status !== 'cancelled') {
            $buttons[] = $button('edit', 'mdi-pencil-outline', 'Edit pembayaran faktur', 'editFaktur');

            if ($this->remainingDebt($row) > 0) {
                $buttons[] = $button('paid', 'mdi-check-decagram-outline', 'Tandai lunas', 'markFakturPaid');
            }

            if ((float) ($row->jumlah_dibayar ?? 0) > 0) {
                $buttons[] = $button('reset', 'mdi-backup-restore', 'Reset pembayaran', 'resetFakturPayment');
            }
        }

        return '<div class="invoice-action-group" role="group" aria-label="Aksi faktur">'.implode('', $buttons).'</div>';
    }

    private function applyDueFilter(Builder $query, string $dueStatus): void
    {
        $today = now()->toDateString();
        $soon = now()->addDays(7)->toDateString();

        match ($dueStatus) {
            'lunas' => $query->where(function (Builder $subQuery) {
                $subQuery->where('status_pembayaran', 'lunas')
                    ->orWhere('sisa_hutang', '<=', 0);
            }),
            'overdue' => $query->where('status', '!=', 'cancelled')
                ->where('sisa_hutang', '>', 0)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', $today),
            'due_soon' => $query->where('status', '!=', 'cancelled')
                ->where('sisa_hutang', '>', 0)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '>=', $today)
                ->whereDate('tanggal_jatuh_tempo', '<=', $soon),
            'not_due' => $query->where('status', '!=', 'cancelled')
                ->where('sisa_hutang', '>', 0)
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '>', $soon),
            'no_due' => $query->where('status', '!=', 'cancelled')
                ->where('sisa_hutang', '>', 0)
                ->whereNull('tanggal_jatuh_tempo'),
            'cancelled' => $query->where('status', 'cancelled'),
            default => null,
        };
    }

    private function summary($faktur): array
    {
        $active = $faktur->where('status', '!=', 'cancelled');

        return [
            'total' => $faktur->count(),
            'active' => $active->count(),
            'unpaid' => $active->where('status_pembayaran', 'belum_dibayar')->count(),
            'partial' => $active->where('status_pembayaran', 'sebagian')->count(),
            'paid' => $active->where('status_pembayaran', 'lunas')->count(),
            'overdue' => $active->filter(fn (PenerimaanBarangModel $row) => $this->dueState($row) === 'overdue')->count(),
            'due_soon' => $active->filter(fn (PenerimaanBarangModel $row) => $this->dueState($row) === 'due_soon')->count(),
            'total_value' => $active->sum(fn (PenerimaanBarangModel $row) => $this->invoiceTotal($row)),
            'paid_value' => $active->sum(fn (PenerimaanBarangModel $row) => (float) ($row->jumlah_dibayar ?? 0)),
            'remaining_debt' => $active->sum(fn (PenerimaanBarangModel $row) => $this->remainingDebt($row)),
        ];
    }

    private function dueState(PenerimaanBarangModel $faktur): string
    {
        if ($faktur->status === 'cancelled') {
            return 'cancelled';
        }

        if ((string) $faktur->status_pembayaran === 'lunas' || $this->remainingDebt($faktur) <= 0) {
            return 'lunas';
        }

        if (! $faktur->tanggal_jatuh_tempo) {
            return 'no_due';
        }

        $dueDate = $faktur->tanggal_jatuh_tempo->copy()->startOfDay();
        $today = now()->startOfDay();

        if ($dueDate->lt($today)) {
            return 'overdue';
        }

        if ($dueDate->lte($today->copy()->addDays(7))) {
            return 'due_soon';
        }

        return 'not_due';
    }

    private function dueLabel(PenerimaanBarangModel $faktur): string
    {
        return match ($this->dueState($faktur)) {
            'cancelled' => 'Dibatalkan',
            'lunas' => 'Lunas',
            'overdue' => 'Jatuh tempo',
            'due_soon' => 'Segera jatuh tempo',
            'not_due' => 'Belum jatuh tempo',
            default => 'Tanpa jatuh tempo',
        };
    }

    private function paymentStatus(float $totalFaktur, float $jumlahDibayar): string
    {
        if ($totalFaktur <= 0 || $jumlahDibayar <= 0) {
            return 'belum_dibayar';
        }

        return $jumlahDibayar >= $totalFaktur ? 'lunas' : 'sebagian';
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'lunas' => 'Lunas',
            'sebagian' => 'Sebagian',
            default => 'Belum Dibayar',
        };
    }

    private function paymentProgress(PenerimaanBarangModel $faktur): int
    {
        $total = $this->invoiceTotal($faktur);

        if ($total <= 0) {
            return 0;
        }

        return (int) min(100, round(((float) ($faktur->jumlah_dibayar ?? 0) / $total) * 100));
    }

    private function computedInvoiceTotal(PenerimaanBarangModel $faktur, float $biayaLain): float
    {
        $subtotal = (float) ($faktur->subtotal ?? 0);
        $diskon = (float) ($faktur->total_diskon ?? $faktur->diskon ?? 0);
        $pajak = (float) ($faktur->total_ppn ?? $faktur->pajak ?? 0);

        return round(max(0, $subtotal - $diskon + $pajak + $biayaLain), 2);
    }

    private function invoiceTotal(PenerimaanBarangModel $faktur): float
    {
        $total = (float) ($faktur->total_faktur ?? 0);

        if ($total <= 0) {
            $total = (float) ($faktur->grand_total ?? 0);
        }

        return round(max(0, $total), 2);
    }

    private function remainingDebt(PenerimaanBarangModel $faktur): float
    {
        $storedDebt = (float) ($faktur->sisa_hutang ?? 0);

        if ($storedDebt > 0) {
            return round($storedDebt, 2);
        }

        return round(max(0, $this->invoiceTotal($faktur) - (float) ($faktur->jumlah_dibayar ?? 0)), 2);
    }

    private function ensureEditable(PenerimaanBarangModel $faktur): void
    {
        if ($faktur->status === 'cancelled') {
            throw ValidationException::withMessages([
                'status' => 'Faktur dari penerimaan yang dibatalkan tidak bisa diedit.',
            ]);
        }
    }

    private function normalizeMoneyFields(Request $request, array $fields): void
    {
        $normalized = [];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $normalized[$field] = $this->moneyValue($request->input($field));
            }
        }

        $request->merge($normalized);
    }

    private function moneyValue($value): float
    {
        if (is_numeric($value)) {
            return round(max(0, (float) $value), 2);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.\-]/', '', $value) ?: '0';

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
            $value = str_replace('.', '', $value);
        } elseif (substr_count($value, '.') > 1) {
            $value = str_replace('.', '', $value);
        }

        return round(max(0, (float) $value), 2);
    }

    private function parseNullableDate($date, string $field): ?string
    {
        if (trim((string) $date) === '') {
            return null;
        }

        return $this->parseDate($date, $field);
    }

    private function parseDate($date, string $field): string
    {
        $date = trim((string) $date);

        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            }

            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Format tanggal tidak valid.',
            ]);
        }
    }

    private function fakturQuery(array $with = []): Builder
    {
        $query = PenerimaanBarangModel::with($with);

        $this->scopeFakturBranch($query);

        return $query;
    }

    private function scopeFakturBranch(Builder $query): void
    {
        $branchIds = BranchAccess::userBranchIds();

        $query->whereHas('purchaseOrder', function (Builder $purchaseOrderQuery) use ($branchIds) {
            if (empty($branchIds)) {
                $purchaseOrderQuery->whereRaw('1 = 0');

                return;
            }

            $purchaseOrderQuery->whereIn('branch_id', $branchIds);
        });
    }
}
