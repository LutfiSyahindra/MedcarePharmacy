<?php

namespace App\Services\Menu\Laporan;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseReportService
{
    public const TYPES = [
        'pembelian' => [
            'title' => 'Laporan Pembelian',
            'short_title' => 'Seluruh PO',
            'description' => 'Pantau seluruh purchase order, komitmen nilai, realisasi penerimaan, dan sisa pesanan. ',
            'icon' => 'mdi-cart-arrow-down',
            'tone' => 'navy',
        ],
        'detail' => [
            'title' => 'Detail Pembelian',
            'short_title' => 'Detail PO',
            'description' => 'Telusuri item, kuantitas, harga estimasi, dan diskon pada setiap purchase order.',
            'icon' => 'mdi-clipboard-text-search-outline',
            'tone' => 'blue',
        ],
        'penerimaan' => [
            'title' => 'Penerimaan Barang',
            'short_title' => 'Penerimaan',
            'description' => 'Audit barang yang benar-benar diterima dan diposting dari setiap supplier.',
            'icon' => 'mdi-package-variant-closed-check',
            'tone' => 'teal',
        ],
        'supplier' => [
            'title' => 'Pembelian per Supplier',
            'short_title' => 'Per Supplier',
            'description' => 'Bandingkan nilai aktual, kelengkapan pasokan, kecepatan kirim, retur, dan skor supplier.',
            'icon' => 'mdi-truck-check-outline',
            'tone' => 'green',
        ],
        'obat' => [
            'title' => 'Pembelian per Obat',
            'short_title' => 'Per Obat',
            'description' => 'Lihat histori kuantitas, nilai, rentang harga, dan pilihan supplier setiap obat.',
            'icon' => 'mdi-pill-multiple',
            'tone' => 'violet',
        ],
        'retur' => [
            'title' => 'Retur Pembelian',
            'short_title' => 'Retur Supplier',
            'description' => 'Pantau barang yang dikembalikan, alasan, nilai retur, dan penyelesaian ganti rugi supplier.',
            'icon' => 'mdi-keyboard-return',
            'tone' => 'orange',
        ],
        'faktur' => [
            'title' => 'Faktur Pembelian',
            'short_title' => 'Daftar Faktur',
            'description' => 'Tinjau seluruh invoice supplier, nilai tagihan, pembayaran, dan potongan kompensasi.',
            'icon' => 'mdi-file-document-multiple-outline',
            'tone' => 'indigo',
        ],
        'belum-lunas' => [
            'title' => 'Faktur Belum Lunas',
            'short_title' => 'Outstanding',
            'description' => 'Kendalikan seluruh invoice dengan saldo hutang yang masih harus dibayar.',
            'icon' => 'mdi-cash-clock',
            'tone' => 'amber',
        ],
        'jatuh-tempo' => [
            'title' => 'Jatuh Tempo Faktur',
            'short_title' => 'Jatuh Tempo',
            'description' => 'Prioritaskan invoice belum lunas berdasarkan tanggal dan kedekatan jatuh tempo.',
            'icon' => 'mdi-calendar-alert-outline',
            'tone' => 'red',
        ],
        'riwayat-harga' => [
            'title' => 'Riwayat Harga Beli',
            'short_title' => 'Harga Beli',
            'description' => 'Bandingkan perubahan harga beli aktual setiap obat pada supplier yang sama.',
            'icon' => 'mdi-chart-timeline-variant',
            'tone' => 'rose',
        ],
    ];

    public function definition(string $type): array
    {
        return self::TYPES[$type] ?? throw ValidationException::withMessages([
            'report' => 'Jenis laporan pembelian tidak tersedia.',
        ]);
    }

    public function defaultPeriod(string $type): array
    {
        $this->definition($type);

        return match ($type) {
            'jatuh-tempo' => ['start' => today(), 'end' => today()->addDays(30)],
            'belum-lunas' => ['start' => today()->subYears(2), 'end' => today()],
            default => ['start' => today()->subDays(29), 'end' => today()],
        };
    }

    public function build(User $user, string $type, array $filters): array
    {
        $definition = $this->definition($type);
        $context = $this->context($user, $filters['branch_id'] ?? null, $filters['supplier_id'] ?? null);
        $filters['supplier_id'] = $context['selected_supplier_id'];
        $metrics = $this->metrics($type, $context['branch_ids'], $filters);
        $report = $this->reportQuery($type, $context['branch_ids'], $filters);
        $paginator = $this->paginate($report, $filters);
        $trend = $this->trend($type, $context['branch_ids'], $filters);

        return [
            'meta' => [
                'type' => $type,
                ...$definition,
                'branch_label' => $context['branch_label'],
                'supplier_label' => $context['supplier_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'selected_supplier_id' => $context['selected_supplier_id'],
                'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
                'suppliers' => $context['suppliers']->map(fn (DistributorModel $supplier) => [
                    'id' => (int) $supplier->id,
                    'code' => $supplier->kode,
                    'name' => $supplier->nama,
                ])->values()->all(),
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'period_days' => $filters['start']->diffInDays($filters['end']) + 1,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'metrics' => $metrics['cards'],
            'insight' => $this->insight($type, $metrics['values'], $trend),
            'chart' => $trend,
            'table' => [
                'title' => $report['title'],
                'columns' => $report['columns'],
                'rows' => $this->normalizeRows(collect($paginator->items()), $report['columns'])->values()->all(),
                'searchable' => $report['searchable'] !== [],
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'default_sort' => $report['default_sort'],
                'default_direction' => $report['default_direction'],
            ],
        ];
    }

    private function context(User $user, ?int $selectedBranchId, ?int $selectedSupplierId): array
    {
        $allowedIds = BranchAccess::userBranchIds($user);
        $branches = BranchModel::query()
            ->whereIn('id', $allowedIds)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        if ($selectedBranchId && ! in_array($selectedBranchId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        $suppliers = DistributorModel::query()
            ->orderByDesc('is_active')
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'is_active']);
        $selectedSupplier = $selectedSupplierId ? $suppliers->firstWhere('id', $selectedSupplierId) : null;

        if ($selectedSupplierId && ! $selectedSupplier) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Supplier tidak tersedia.',
            ]);
        }

        return [
            'branches' => $branches,
            'suppliers' => $suppliers,
            'branch_ids' => $selectedBranchId ? [$selectedBranchId] : $allowedIds,
            'selected_branch_id' => $selectedBranchId,
            'selected_supplier_id' => $selectedSupplierId,
            'branch_label' => $selectedBranchId
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
            'supplier_label' => $selectedSupplier?->nama ?? 'Semua supplier',
        ];
    }

    private function metrics(string $type, array $branchIds, array $filters): array
    {
        $returns = $this->returnBase($branchIds, $filters)
            ->selectRaw('COUNT(returns.id) as document_count')
            ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as return_value')
            ->selectRaw('COALESCE(SUM(returns.total_qty), 0) as return_qty')
            ->first();
        $returnValue = (float) ($returns->return_value ?? 0);

        if (in_array($type, ['pembelian', 'detail'], true)) {
            $orders = $this->poBase($branchIds, $filters)
                ->selectRaw('COUNT(po.id) as document_count')
                ->selectRaw('COALESCE(SUM(po.total_estimasi), 0) as order_value')
                ->first();
            $actual = $this->receiptBase($branchIds, $filters)
                ->selectRaw('COUNT(receipts.id) as receipt_count')
                ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipts').'), 0) as purchase_value')
                ->first();
            $purchaseValue = (float) ($actual->purchase_value ?? 0);
            $orderValue = (float) ($orders->order_value ?? 0);
            $values = [
                'purchase_value' => $purchaseValue,
                'return_value' => $returnValue,
                'order_count' => (int) ($orders->document_count ?? 0),
                'order_value' => $orderValue,
                'receipt_count' => (int) ($actual->receipt_count ?? 0),
            ];
            $cards = [
                $this->card('Nilai estimasi PO', $orderValue, 'currency', 'mdi-cart-outline', 'Komitmen purchase order periode aktif', 'navy'),
                $this->card('Jumlah PO', $values['order_count'], 'number', 'mdi-file-document-multiple-outline', 'Seluruh status purchase order', 'blue'),
                $this->card('Pembelian aktual', $purchaseValue, 'currency', 'mdi-package-variant-closed-check', 'Penerimaan posted pada periode aktif', 'teal'),
                $this->card('Realisasi nilai', $orderValue > 0 ? ($purchaseValue / $orderValue) * 100 : 0, 'percent', 'mdi-chart-donut', 'Aktual dibanding estimasi periode', 'green'),
            ];
        } elseif ($type === 'retur') {
            $compensation = $this->returnBase($branchIds, $filters)
                ->leftJoinSub($this->compensationTotals(), 'comp', 'comp.retur_pembelian_id', '=', 'returns.id')
                ->selectRaw('COALESCE(SUM(comp.received_value), 0) as received_value')
                ->first();
            $received = (float) ($compensation->received_value ?? 0);
            $values = [
                'purchase_value' => 0,
                'return_value' => $returnValue,
                'return_count' => (int) ($returns->document_count ?? 0),
                'return_qty' => (float) ($returns->return_qty ?? 0),
                'compensation_received' => $received,
            ];
            $cards = [
                $this->card('Nilai retur', $returnValue, 'currency', 'mdi-cash-refund', 'Retur supplier berstatus posted', 'orange'),
                $this->card('Dokumen retur', $values['return_count'], 'number', 'mdi-keyboard-return', 'Dokumen berhasil diposting', 'blue'),
                $this->card('Qty dikembalikan', $values['return_qty'], 'number', 'mdi-package-variant-closed-minus', 'Dalam satuan pembelian', 'violet'),
                $this->card('Ganti rugi diterima', $received, 'currency', 'mdi-hand-coin-outline', 'Kompensasi aktif dari supplier', 'green'),
            ];
        } elseif (in_array($type, ['faktur', 'belum-lunas', 'jatuh-tempo'], true)) {
            $invoices = $this->invoiceBase($type, $branchIds, $filters)
                ->selectRaw('COUNT(receipts.id) as invoice_count')
                ->selectRaw('COALESCE(SUM('.$this->invoiceExpression('receipts').'), 0) as invoice_value')
                ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipts').'), 0) as payable_value')
                ->selectRaw('COALESCE(SUM(receipts.jumlah_dibayar), 0) as paid_value')
                ->selectRaw('COALESCE(SUM('.$this->debtExpression('receipts').'), 0) as debt_value')
                ->first();
            $debt = (float) ($invoices->debt_value ?? 0);
            $payable = (float) ($invoices->payable_value ?? 0);
            $values = [
                'purchase_value' => $payable,
                'return_value' => $returnValue,
                'invoice_count' => (int) ($invoices->invoice_count ?? 0),
                'invoice_value' => (float) ($invoices->invoice_value ?? 0),
                'payable_value' => $payable,
                'paid_value' => (float) ($invoices->paid_value ?? 0),
                'debt_value' => $debt,
            ];
            $cards = [
                $this->card('Nilai faktur', $values['invoice_value'], 'currency', 'mdi-file-document-multiple-outline', 'Nilai bruto sebelum potongan kompensasi', 'navy'),
                $this->card('Jumlah faktur', $values['invoice_count'], 'number', 'mdi-receipt-text-outline', 'Invoice sesuai cakupan laporan', 'blue'),
                $this->card('Sudah dibayar', $values['paid_value'], 'currency', 'mdi-cash-check', 'Akumulasi pembayaran tercatat', 'green'),
                $this->card('Sisa hutang', $debt, 'currency', 'mdi-cash-clock', 'Tagihan setelah kompensasi', $debt > 0 ? 'red' : 'teal'),
            ];
        } else {
            $receipts = $this->receiptBase($branchIds, $filters)
                ->selectRaw('COUNT(receipts.id) as receipt_count')
                ->selectRaw('COUNT(DISTINCT receipts.distributor_id) as supplier_count')
                ->selectRaw('COALESCE(SUM(receipts.total_qty), 0) as total_qty')
                ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipts').'), 0) as purchase_value')
                ->first();
            $purchaseValue = (float) ($receipts->purchase_value ?? 0);
            $best = $type === 'supplier'
                ? $this->supplierAggregate($branchIds, $filters)->orderByDesc('supplier_score')->first()
                : null;
            $values = [
                'purchase_value' => $purchaseValue,
                'return_value' => $returnValue,
                'receipt_count' => (int) ($receipts->receipt_count ?? 0),
                'supplier_count' => (int) ($receipts->supplier_count ?? 0),
                'total_qty' => (float) ($receipts->total_qty ?? 0),
                'best_supplier' => $best?->supplier_name,
                'best_supplier_score' => (float) ($best?->supplier_score ?? 0),
            ];
            $cards = [
                $this->card('Pembelian aktual', $purchaseValue, 'currency', 'mdi-cash-multiple', 'Tagihan penerimaan posted setelah kompensasi', 'navy'),
                $this->card('Penerimaan posted', $values['receipt_count'], 'number', 'mdi-package-variant-closed-check', 'Dokumen masuk periode aktif', 'blue'),
                $this->card('Supplier aktif', $values['supplier_count'], 'number', 'mdi-truck-check-outline', 'Supplier dengan penerimaan aktual', 'teal'),
                $this->card('Nilai retur', $returnValue, 'currency', 'mdi-keyboard-return', 'Koreksi retur posted pada periode', 'orange'),
            ];
        }

        $values['net_after_returns'] = (float) ($values['purchase_value'] ?? 0) - $returnValue;

        return compact('cards', 'values');
    }

    private function card(string $label, float|int $value, string $format, string $icon, string $note, string $tone): array
    {
        return compact('label', 'value', 'format', 'icon', 'note', 'tone');
    }

    private function reportQuery(string $type, array $branchIds, array $filters): array
    {
        return match ($type) {
            'pembelian' => $this->purchaseReport($branchIds, $filters),
            'detail' => $this->detailReport($branchIds, $filters),
            'penerimaan' => $this->receiptReport($branchIds, $filters),
            'supplier' => $this->supplierReport($branchIds, $filters),
            'obat' => $this->medicineReport($branchIds, $filters),
            'retur' => $this->returnReport($branchIds, $filters),
            'faktur', 'belum-lunas', 'jatuh-tempo' => $this->invoiceReport($type, $branchIds, $filters),
            'riwayat-harga' => $this->priceHistoryReport($branchIds, $filters),
        };
    }

    private function purchaseReport(array $branchIds, array $filters): array
    {
        $receipts = DB::table('penerimaan_barang as receipt_totals')
            ->where('receipt_totals.status', 'posted')
            ->groupBy('receipt_totals.purchase_order_id')
            ->select('receipt_totals.purchase_order_id')
            ->selectRaw('COUNT(receipt_totals.id) as receipt_count')
            ->selectRaw('COALESCE(SUM(receipt_totals.total_qty), 0) as received_qty')
            ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipt_totals').'), 0) as actual_value');
        $items = DB::table('purchase_order_details as order_items')
            ->groupBy('order_items.purchase_order_id')
            ->select('order_items.purchase_order_id')
            ->selectRaw('COUNT(order_items.id) as item_count')
            ->selectRaw('COALESCE(SUM(order_items.qty), 0) as ordered_qty');

        $query = $this->poBase($branchIds, $filters)
            ->join('distributors as suppliers', 'suppliers.id', '=', 'po.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->leftJoin('users as creators', 'creators.id', '=', 'po.created_by')
            ->leftJoinSub($items, 'items', 'items.purchase_order_id', '=', 'po.id')
            ->leftJoinSub($receipts, 'receipt_totals', 'receipt_totals.purchase_order_id', '=', 'po.id')
            ->select([
                'po.no_po', 'po.tanggal_po as order_date', 'branches.name as branch_name',
                'suppliers.nama as supplier_name', 'po.status', 'creators.name as created_by',
            ])
            ->selectRaw('COALESCE(items.item_count, 0) as item_count')
            ->selectRaw('COALESCE(items.ordered_qty, 0) as ordered_qty')
            ->selectRaw('COALESCE(receipt_totals.receipt_count, 0) as receipt_count')
            ->selectRaw('COALESCE(receipt_totals.received_qty, 0) as received_qty')
            ->selectRaw('po.total_estimasi as estimated_value')
            ->selectRaw('COALESCE(receipt_totals.actual_value, 0) as actual_value');

        return $this->report('Seluruh purchase order', $query, [
            $this->column('no_po', 'Nomor PO'), $this->column('order_date', 'Tanggal PO', 'date'),
            $this->column('branch_name', 'Cabang'), $this->column('supplier_name', 'Supplier'),
            $this->column('status', 'Status', 'status'), $this->column('item_count', 'Item', 'number'),
            $this->column('ordered_qty', 'Qty dipesan', 'number'), $this->column('received_qty', 'Qty diterima', 'number'),
            $this->column('receipt_count', 'Penerimaan', 'number'), $this->column('estimated_value', 'Estimasi', 'currency'),
            $this->column('actual_value', 'Aktual', 'currency'), $this->column('created_by', 'Dibuat oleh'),
        ], ['po.no_po', 'branches.name', 'suppliers.nama', 'creators.name'], [
            'no_po' => 'po.no_po', 'order_date' => 'po.tanggal_po', 'branch_name' => 'branches.name',
            'supplier_name' => 'suppliers.nama', 'status' => 'po.status', 'item_count' => 'item_count',
            'ordered_qty' => 'ordered_qty', 'received_qty' => 'received_qty', 'receipt_count' => 'receipt_count',
            'estimated_value' => 'estimated_value', 'actual_value' => 'actual_value', 'created_by' => 'creators.name',
        ], 'order_date', 'desc');
    }

    private function detailReport(array $branchIds, array $filters): array
    {
        $query = $this->poBase($branchIds, $filters)
            ->join('purchase_order_details as details', 'details.purchase_order_id', '=', 'po.id')
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->join('distributors as suppliers', 'suppliers.id', '=', 'po.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->leftJoin('obat_satuan_conversions as conversions', 'conversions.id', '=', 'details.satuan_konversi')
            ->leftJoin('satuans as units', 'units.id', '=', 'conversions.satuan_id')
            ->select([
                'po.no_po', 'po.tanggal_po as order_date', 'branches.name as branch_name', 'suppliers.nama as supplier_name',
                'medicines.kode_obat as product_code', 'medicines.nama_obat as product_name', 'po.status',
                'details.qty', 'units.nama as purchase_unit', 'details.harga_estimasi as estimated_price',
                'details.diskon_1', 'details.diskon_2', 'details.diskon_3', 'details.subtotal',
            ]);

        return $this->report('Item setiap purchase order', $query, [
            $this->column('order_date', 'Tanggal PO', 'date'), $this->column('no_po', 'Nomor PO'),
            $this->column('branch_name', 'Cabang'), $this->column('supplier_name', 'Supplier'),
            $this->column('product_code', 'Kode Obat'), $this->column('product_name', 'Nama Obat'),
            $this->column('status', 'Status PO', 'status'), $this->column('qty', 'Qty', 'number'),
            $this->column('purchase_unit', 'Satuan', 'text', 'Satuan belum tercatat'),
            $this->column('estimated_price', 'Harga Estimasi', 'currency'),
            $this->column('diskon_1', 'Diskon 1', 'percent'), $this->column('diskon_2', 'Diskon 2', 'percent'),
            $this->column('diskon_3', 'Diskon 3', 'percent'), $this->column('subtotal', 'Subtotal', 'currency'),
        ], ['po.no_po', 'branches.name', 'suppliers.nama', 'medicines.kode_obat', 'medicines.nama_obat'], [
            'order_date' => 'po.tanggal_po', 'no_po' => 'po.no_po', 'branch_name' => 'branches.name',
            'supplier_name' => 'suppliers.nama', 'product_code' => 'medicines.kode_obat', 'product_name' => 'medicines.nama_obat',
            'status' => 'po.status', 'qty' => 'details.qty', 'purchase_unit' => 'units.nama',
            'estimated_price' => 'details.harga_estimasi', 'diskon_1' => 'details.diskon_1', 'diskon_2' => 'details.diskon_2',
            'diskon_3' => 'details.diskon_3', 'subtotal' => 'details.subtotal',
        ], 'order_date', 'desc');
    }

    private function receiptReport(array $branchIds, array $filters): array
    {
        $query = $this->receiptBase($branchIds, $filters)
            ->join('distributors as suppliers', 'suppliers.id', '=', 'receipts.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->leftJoin('users as posters', 'posters.id', '=', 'receipts.posted_by')
            ->select([
                'receipts.nomor_penerimaan as receipt_number', 'receipts.tanggal_penerimaan as receipt_date',
                'po.no_po', 'branches.name as branch_name', 'suppliers.nama as supplier_name', 'receipts.nomor_faktur as invoice_number',
                'receipts.total_barang as item_count', 'receipts.total_qty', 'receipts.subtotal',
                'receipts.total_diskon as discount_value', 'receipts.total_ppn as tax_value', 'posters.name as posted_by',
            ])
            ->selectRaw($this->payableExpression('receipts').' as purchase_value');

        return $this->report('Penerimaan barang posted', $query, [
            $this->column('receipt_date', 'Tanggal Terima', 'date'), $this->column('receipt_number', 'No. Penerimaan'),
            $this->column('no_po', 'Nomor PO'), $this->column('branch_name', 'Cabang'),
            $this->column('supplier_name', 'Supplier'), $this->column('invoice_number', 'Nomor Faktur'),
            $this->column('item_count', 'Item', 'number'), $this->column('total_qty', 'Qty', 'number'),
            $this->column('subtotal', 'Subtotal', 'currency'), $this->column('discount_value', 'Diskon', 'currency'),
            $this->column('tax_value', 'PPN', 'currency'), $this->column('purchase_value', 'Nilai Aktual', 'currency'),
            $this->column('posted_by', 'Diposting oleh'),
        ], ['receipts.nomor_penerimaan', 'receipts.nomor_faktur', 'po.no_po', 'branches.name', 'suppliers.nama', 'posters.name'], [
            'receipt_date' => 'receipts.tanggal_penerimaan', 'receipt_number' => 'receipts.nomor_penerimaan', 'no_po' => 'po.no_po',
            'branch_name' => 'branches.name', 'supplier_name' => 'suppliers.nama', 'invoice_number' => 'receipts.nomor_faktur',
            'item_count' => 'receipts.total_barang', 'total_qty' => 'receipts.total_qty', 'subtotal' => 'receipts.subtotal',
            'discount_value' => 'receipts.total_diskon', 'tax_value' => 'receipts.total_ppn', 'purchase_value' => 'purchase_value',
            'posted_by' => 'posters.name',
        ], 'receipt_date', 'desc');
    }

    private function supplierReport(array $branchIds, array $filters): array
    {
        return $this->report('Perbandingan kinerja supplier', $this->supplierAggregate($branchIds, $filters), [
            $this->column('supplier_code', 'Kode'), $this->column('supplier_name', 'Supplier'),
            $this->column('purchase_value', 'Nilai Pembelian', 'currency'), $this->column('receipt_count', 'Penerimaan', 'number'),
            $this->column('po_count', 'PO Dipasok', 'number'), $this->column('product_count', 'Jenis Obat', 'number'),
            $this->column('ordered_qty', 'Qty Dipesan', 'number'), $this->column('received_qty', 'Qty Diterima', 'number'),
            $this->column('fulfillment_rate', 'Pemenuhan', 'percent'), $this->column('avg_lead_days', 'Rata-rata Hari Kirim', 'number'),
            $this->column('return_value', 'Nilai Retur', 'currency'), $this->column('return_rate', 'Rasio Retur', 'percent'),
            $this->column('supplier_score', 'Skor Supplier', 'number'),
        ], ['suppliers.kode', 'suppliers.nama'], [
            'supplier_code' => 'suppliers.kode', 'supplier_name' => 'suppliers.nama', 'purchase_value' => 'purchase_value',
            'receipt_count' => 'receipt_count', 'po_count' => 'po_count', 'product_count' => 'product_count',
            'ordered_qty' => 'ordered_qty', 'received_qty' => 'received_qty', 'fulfillment_rate' => 'fulfillment_rate',
            'avg_lead_days' => 'avg_lead_days', 'return_value' => 'return_value', 'return_rate' => 'return_rate',
            'supplier_score' => 'supplier_score',
        ], 'supplier_score', 'desc');
    }

    private function medicineReport(array $branchIds, array $filters): array
    {
        $unitPrice = $this->unitPurchasePriceExpression('details');
        $query = $this->receiptDetailBase($branchIds, $filters)
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('satuans as units', 'units.id', '=', 'medicines.satuan_id')
            ->groupBy('medicines.id', 'medicines.kode_obat', 'medicines.nama_obat', 'units.nama')
            ->select([
                'medicines.kode_obat as product_code', 'medicines.nama_obat as product_name', 'units.nama as stock_unit',
            ])
            ->selectRaw('COUNT(DISTINCT receipts.id) as receipt_count')
            ->selectRaw('COUNT(DISTINCT receipts.distributor_id) as supplier_count')
            ->selectRaw('COALESCE(SUM(details.qty_diterima_stok), 0) as received_qty')
            ->selectRaw('COALESCE(SUM(details.total), 0) as purchase_value')
            ->selectRaw('CASE WHEN SUM(details.qty_diterima_stok) > 0 THEN (SUM(details.total) * 1.0) / SUM(details.qty_diterima_stok) ELSE 0 END as average_net_price')
            ->selectRaw('MIN('.$unitPrice.') as minimum_price')
            ->selectRaw('MAX('.$unitPrice.') as maximum_price')
            ->selectRaw('MAX(receipts.tanggal_penerimaan) as last_purchase_date');

        return $this->report('Histori pembelian per obat', $query, [
            $this->column('product_code', 'Kode Obat'), $this->column('product_name', 'Nama Obat'),
            $this->column('stock_unit', 'Satuan Stok', 'text', 'Satuan belum tercatat'),
            $this->column('supplier_count', 'Supplier', 'number'), $this->column('receipt_count', 'Penerimaan', 'number'),
            $this->column('received_qty', 'Qty Stok Diterima', 'number'), $this->column('purchase_value', 'Nilai Pembelian', 'currency'),
            $this->column('average_net_price', 'Harga Neto Rata-rata', 'currency'),
            $this->column('minimum_price', 'Harga Terendah', 'currency'), $this->column('maximum_price', 'Harga Tertinggi', 'currency'),
            $this->column('last_purchase_date', 'Pembelian Terakhir', 'date'),
        ], ['medicines.kode_obat', 'medicines.nama_obat'], [
            'product_code' => 'medicines.kode_obat', 'product_name' => 'medicines.nama_obat', 'stock_unit' => 'units.nama',
            'supplier_count' => 'supplier_count', 'receipt_count' => 'receipt_count', 'received_qty' => 'received_qty',
            'purchase_value' => 'purchase_value', 'average_net_price' => 'average_net_price', 'minimum_price' => 'minimum_price',
            'maximum_price' => 'maximum_price', 'last_purchase_date' => 'last_purchase_date',
        ], 'purchase_value', 'desc');
    }

    private function returnReport(array $branchIds, array $filters): array
    {
        $query = $this->returnBase($branchIds, $filters)
            ->join('distributors as suppliers', 'suppliers.id', '=', 'returns.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->join('penerimaan_barang as receipts', 'receipts.id', '=', 'returns.penerimaan_barang_id')
            ->leftJoin('users as posters', 'posters.id', '=', 'returns.posted_by')
            ->leftJoinSub($this->compensationTotals(), 'comp', 'comp.retur_pembelian_id', '=', 'returns.id')
            ->select([
                'returns.nomor_retur as return_number', 'returns.tanggal_retur as return_date', 'branches.name as branch_name',
                'suppliers.nama as supplier_name', 'po.no_po', 'receipts.nomor_penerimaan as receipt_number',
                'returns.nomor_referensi_supplier as supplier_reference', 'returns.total_barang as item_count',
                'returns.total_qty as return_qty', 'returns.grand_total as return_value', 'returns.alasan',
                'returns.expects_compensation', 'returns.compensation_due_date', 'posters.name as posted_by',
            ])
            ->selectRaw('COALESCE(comp.received_value, 0) as compensation_received')
            ->selectRaw('CASE WHEN returns.expects_compensation = 1 THEN CASE WHEN returns.grand_total - COALESCE(comp.received_value, 0) > 0 THEN returns.grand_total - COALESCE(comp.received_value, 0) ELSE 0 END ELSE 0 END as compensation_outstanding');

        return $this->report('Retur pembelian ke supplier', $query, [
            $this->column('return_date', 'Tanggal Retur', 'date'), $this->column('return_number', 'Nomor Retur'),
            $this->column('branch_name', 'Cabang'), $this->column('supplier_name', 'Supplier'),
            $this->column('no_po', 'Nomor PO'), $this->column('receipt_number', 'No. Penerimaan'),
            $this->column('supplier_reference', 'Referensi Supplier', 'text', 'Belum ada referensi'),
            $this->column('item_count', 'Item', 'number'), $this->column('return_qty', 'Qty Retur', 'number'),
            $this->column('return_value', 'Nilai Retur', 'currency'), $this->column('alasan', 'Alasan', 'text', 'Alasan belum dicatat'),
            $this->column('compensation_received', 'Ganti Rugi Diterima', 'currency'),
            $this->column('compensation_outstanding', 'Sisa Ganti Rugi', 'currency'),
            $this->column('compensation_due_date', 'Batas Ganti Rugi', 'date', 'Tidak ditentukan'),
            $this->column('posted_by', 'Diposting oleh'),
        ], ['returns.nomor_retur', 'returns.nomor_referensi_supplier', 'branches.name', 'suppliers.nama', 'po.no_po', 'receipts.nomor_penerimaan', 'returns.alasan'], [
            'return_date' => 'returns.tanggal_retur', 'return_number' => 'returns.nomor_retur', 'branch_name' => 'branches.name',
            'supplier_name' => 'suppliers.nama', 'no_po' => 'po.no_po', 'receipt_number' => 'receipts.nomor_penerimaan',
            'supplier_reference' => 'returns.nomor_referensi_supplier', 'item_count' => 'returns.total_barang',
            'return_qty' => 'returns.total_qty', 'return_value' => 'returns.grand_total', 'alasan' => 'returns.alasan',
            'compensation_received' => 'compensation_received', 'compensation_outstanding' => 'compensation_outstanding',
            'compensation_due_date' => 'returns.compensation_due_date', 'posted_by' => 'posters.name',
        ], 'return_date', 'desc');
    }

    private function invoiceReport(string $type, array $branchIds, array $filters): array
    {
        $query = $this->invoiceBase($type, $branchIds, $filters)
            ->join('distributors as suppliers', 'suppliers.id', '=', 'receipts.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->select([
                'receipts.nomor_faktur as invoice_number',
                'receipts.tanggal_jatuh_tempo as due_date', 'receipts.nomor_penerimaan as receipt_number',
                'po.no_po', 'branches.name as branch_name', 'suppliers.nama as supplier_name',
                'receipts.status_pembayaran as payment_status', 'receipts.jumlah_dibayar as paid_value',
                'receipts.supplier_compensation_discount as compensation_discount',
            ])
            ->selectRaw('COALESCE(receipts.tanggal_faktur, receipts.tanggal_penerimaan) as invoice_date')
            ->selectRaw($this->invoiceExpression('receipts').' as invoice_value')
            ->selectRaw($this->payableExpression('receipts').' as payable_value')
            ->selectRaw($this->debtExpression('receipts').' as outstanding_value')
            ->selectRaw($this->daysFromTodayExpression('receipts.tanggal_jatuh_tempo').' as days_to_due')
            ->selectRaw($this->dueStatusExpression('receipts.tanggal_jatuh_tempo').' as due_status');

        $title = match ($type) {
            'belum-lunas' => 'Outstanding invoice supplier',
            'jatuh-tempo' => 'Prioritas jatuh tempo faktur',
            default => 'Daftar invoice supplier',
        };

        return $this->report($title, $query, [
            $this->column('invoice_date', 'Tanggal Faktur', 'date', 'Mengikuti tanggal terima'),
            $this->column('invoice_number', 'Nomor Faktur'), $this->column('due_date', 'Jatuh Tempo', 'date', 'Belum ditentukan'),
            $this->column('days_to_due', 'Hari ke Jatuh Tempo', 'number', 'Tidak ada tempo'),
            $this->column('due_status', 'Status Tempo', 'status'), $this->column('branch_name', 'Cabang'),
            $this->column('supplier_name', 'Supplier'), $this->column('receipt_number', 'No. Penerimaan'),
            $this->column('no_po', 'Nomor PO'), $this->column('invoice_value', 'Nilai Faktur', 'currency'),
            $this->column('compensation_discount', 'Potongan Ganti Rugi', 'currency'),
            $this->column('payable_value', 'Tagihan Neto', 'currency'), $this->column('paid_value', 'Dibayar', 'currency'),
            $this->column('outstanding_value', 'Sisa Hutang', 'currency'), $this->column('payment_status', 'Status Bayar', 'status'),
        ], ['receipts.nomor_faktur', 'receipts.nomor_penerimaan', 'po.no_po', 'branches.name', 'suppliers.nama'], [
            'invoice_date' => 'receipts.tanggal_faktur', 'invoice_number' => 'receipts.nomor_faktur', 'due_date' => 'receipts.tanggal_jatuh_tempo',
            'days_to_due' => 'days_to_due', 'due_status' => 'due_status', 'branch_name' => 'branches.name',
            'supplier_name' => 'suppliers.nama', 'receipt_number' => 'receipts.nomor_penerimaan', 'no_po' => 'po.no_po',
            'invoice_value' => 'invoice_value', 'compensation_discount' => 'receipts.supplier_compensation_discount',
            'payable_value' => 'payable_value', 'paid_value' => 'receipts.jumlah_dibayar', 'outstanding_value' => 'outstanding_value',
            'payment_status' => 'receipts.status_pembayaran',
        ], $type === 'jatuh-tempo' ? 'due_date' : 'invoice_date', $type === 'jatuh-tempo' ? 'asc' : 'desc');
    }

    private function priceHistoryReport(array $branchIds, array $filters): array
    {
        $unitPrice = $this->unitPurchasePriceExpression('details');
        $previousUnitPrice = $this->unitPurchasePriceExpression('previous_details');
        $previousPrice = '(SELECT '.$previousUnitPrice.' FROM penerimaan_barang_detail as previous_details '
            .'INNER JOIN penerimaan_barang as previous_receipts ON previous_receipts.id = previous_details.penerimaan_barang_id '
            .'INNER JOIN purchase_orders as previous_po ON previous_po.id = previous_receipts.purchase_order_id '
            .'WHERE previous_receipts.status = \'posted\' '
            .'AND previous_details.obat_id = details.obat_id '
            .'AND previous_receipts.distributor_id = receipts.distributor_id '
            .'AND previous_po.branch_id = po.branch_id '
            .'AND (previous_receipts.tanggal_penerimaan < receipts.tanggal_penerimaan '
            .'OR (previous_receipts.tanggal_penerimaan = receipts.tanggal_penerimaan AND previous_details.id < details.id)) '
            .'ORDER BY previous_receipts.tanggal_penerimaan DESC, previous_details.id DESC LIMIT 1)';
        $history = $this->receiptDetailBase($branchIds, $filters)
            ->join('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->join('distributors as suppliers', 'suppliers.id', '=', 'receipts.distributor_id')
            ->join('branches', 'branches.id', '=', 'po.branch_id')
            ->select([
                'details.id', 'receipts.tanggal_penerimaan as receipt_date', 'receipts.nomor_penerimaan as receipt_number',
                'branches.name as branch_name', 'suppliers.nama as supplier_name', 'medicines.kode_obat as product_code',
                'medicines.nama_obat as product_name', 'details.satuan_stok as stock_unit', 'details.qty_diterima_stok as received_qty',
            ])
            ->selectRaw($unitPrice.' as current_price')
            ->selectRaw($previousPrice.' as previous_price');

        $query = DB::query()->fromSub($history, 'history')
            ->select('history.*')
            ->selectRaw('CASE WHEN history.previous_price IS NULL THEN NULL ELSE history.current_price - history.previous_price END as price_change')
            ->selectRaw('CASE WHEN history.previous_price > 0 THEN ((history.current_price - history.previous_price) * 100.0) / history.previous_price ELSE NULL END as change_percent');

        return $this->report('Perubahan harga beli per obat dan supplier', $query, [
            $this->column('receipt_date', 'Tanggal Terima', 'date'), $this->column('receipt_number', 'No. Penerimaan'),
            $this->column('branch_name', 'Cabang'), $this->column('supplier_name', 'Supplier'),
            $this->column('product_code', 'Kode Obat'), $this->column('product_name', 'Nama Obat'),
            $this->column('stock_unit', 'Satuan Stok', 'text', 'Satuan belum tercatat'),
            $this->column('received_qty', 'Qty Diterima', 'number'), $this->column('previous_price', 'Harga Sebelumnya', 'currency', 'Pembelian pertama'),
            $this->column('current_price', 'Harga Beli', 'currency'), $this->column('price_change', 'Perubahan', 'signed_currency', 'Pembelian pertama'),
            $this->column('change_percent', 'Perubahan %', 'percent', 'Pembelian pertama'),
        ], ['history.receipt_number', 'history.branch_name', 'history.supplier_name', 'history.product_code', 'history.product_name'], [
            'receipt_date' => 'history.receipt_date', 'receipt_number' => 'history.receipt_number', 'branch_name' => 'history.branch_name',
            'supplier_name' => 'history.supplier_name', 'product_code' => 'history.product_code', 'product_name' => 'history.product_name',
            'stock_unit' => 'history.stock_unit', 'received_qty' => 'history.received_qty', 'previous_price' => 'history.previous_price',
            'current_price' => 'history.current_price', 'price_change' => 'price_change', 'change_percent' => 'change_percent',
        ], 'receipt_date', 'desc');
    }

    private function supplierAggregate(array $branchIds, array $filters): Builder
    {
        $receiptStats = $this->receiptBase($branchIds, $filters)
            ->groupBy('receipts.distributor_id')
            ->select('receipts.distributor_id')
            ->selectRaw('COUNT(receipts.id) as receipt_count')
            ->selectRaw('COUNT(DISTINCT receipts.purchase_order_id) as po_count')
            ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipts').'), 0) as purchase_value')
            ->selectRaw($this->averageLeadDaysExpression().' as avg_lead_days');
        $detailStats = $this->receiptDetailBase($branchIds, $filters)
            ->groupBy('receipts.distributor_id')
            ->select('receipts.distributor_id')
            ->selectRaw('COUNT(DISTINCT details.obat_id) as product_count')
            ->selectRaw('COALESCE(SUM(details.qty_diterima), 0) as received_qty');
        $orderStats = $this->poBase($branchIds, $filters)
            ->join('purchase_order_details as ordered_details', 'ordered_details.purchase_order_id', '=', 'po.id')
            ->groupBy('po.distributor_id')
            ->select('po.distributor_id')
            ->selectRaw('COALESCE(SUM(ordered_details.qty), 0) as ordered_qty');
        $returnStats = $this->returnBase($branchIds, $filters)
            ->groupBy('returns.distributor_id')
            ->select('returns.distributor_id')
            ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as return_value');

        $fulfillment = 'CASE WHEN COALESCE(os.ordered_qty, 0) > 0 THEN (COALESCE(ds.received_qty, 0) * 100.0) / os.ordered_qty ELSE 0 END';
        $returnRate = 'CASE WHEN rs.purchase_value > 0 THEN (COALESCE(rts.return_value, 0) * 100.0) / rs.purchase_value ELSE 0 END';
        $qualityScore = 'CASE WHEN rs.purchase_value <= 0 THEN 0 WHEN COALESCE(rts.return_value, 0) >= rs.purchase_value THEN 0 ELSE 50 * (1 - ((COALESCE(rts.return_value, 0) * 1.0) / rs.purchase_value)) END';
        $deliveryScore = 'CASE WHEN COALESCE(rs.avg_lead_days, 0) <= 0 THEN 25 WHEN rs.avg_lead_days >= 30 THEN 0 ELSE 25 * (1 - (rs.avg_lead_days / 30)) END';
        $fulfillmentScore = 'CASE WHEN COALESCE(os.ordered_qty, 0) <= 0 THEN 0 WHEN COALESCE(ds.received_qty, 0) >= os.ordered_qty THEN 25 ELSE 25 * ((COALESCE(ds.received_qty, 0) * 1.0) / os.ordered_qty) END';

        return DB::table('distributors as suppliers')
            ->joinSub($receiptStats, 'rs', 'rs.distributor_id', '=', 'suppliers.id')
            ->leftJoinSub($detailStats, 'ds', 'ds.distributor_id', '=', 'suppliers.id')
            ->leftJoinSub($orderStats, 'os', 'os.distributor_id', '=', 'suppliers.id')
            ->leftJoinSub($returnStats, 'rts', 'rts.distributor_id', '=', 'suppliers.id')
            ->select(['suppliers.kode as supplier_code', 'suppliers.nama as supplier_name'])
            ->selectRaw('rs.purchase_value, rs.receipt_count, rs.po_count, COALESCE(ds.product_count, 0) as product_count')
            ->selectRaw('COALESCE(os.ordered_qty, 0) as ordered_qty, COALESCE(ds.received_qty, 0) as received_qty')
            ->selectRaw('ROUND('.$fulfillment.', 2) as fulfillment_rate')
            ->selectRaw('ROUND(COALESCE(rs.avg_lead_days, 0), 2) as avg_lead_days')
            ->selectRaw('COALESCE(rts.return_value, 0) as return_value')
            ->selectRaw('ROUND('.$returnRate.', 2) as return_rate')
            ->selectRaw('ROUND('.$qualityScore.' + '.$deliveryScore.' + '.$fulfillmentScore.', 2) as supplier_score');
    }

    private function report(string $title, Builder $query, array $columns, array $searchable, array $sorts, string $defaultSort, string $defaultDirection): array
    {
        return compact('title', 'query', 'columns', 'searchable', 'sorts') + [
            'default_sort' => $defaultSort,
            'default_direction' => $defaultDirection,
        ];
    }

    private function column(string $key, string $label, string $type = 'text', ?string $emptyLabel = null): array
    {
        return array_filter([
            'key' => $key,
            'label' => $label,
            'type' => $type,
            'sortable' => true,
            'empty_label' => $emptyLabel,
        ], fn ($value) => $value !== null);
    }

    private function paginate(array $report, array $filters): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = $report['query'];
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '' && $report['searchable'] !== []) {
            $query->where(function (Builder $query) use ($report, $search) {
                foreach ($report['searchable'] as $index => $field) {
                    $query->{$index === 0 ? 'where' : 'orWhere'}($field, 'like', '%'.$search.'%');
                }
            });
        }

        $sortKey = array_key_exists((string) ($filters['sort'] ?? ''), $report['sorts'])
            ? (string) $filters['sort']
            : $report['default_sort'];
        $direction = ($filters['direction'] ?? $report['default_direction']) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($report['sorts'][$sortKey], $direction);

        return $query->paginate(
            (int) ($filters['per_page'] ?? 25),
            ['*'],
            'page',
            (int) ($filters['page'] ?? 1),
        );
    }

    private function trend(string $type, array $branchIds, array $filters): array
    {
        if (in_array($type, ['pembelian', 'detail'], true)) {
            $date = $this->dateExpression('po.tanggal_po');
            $raw = $this->poBase($branchIds, $filters)
                ->selectRaw($date.' as bucket')
                ->selectRaw('COUNT(po.id) as count_value')
                ->selectRaw('COALESCE(SUM(po.total_estimasi), 0) as amount_value')
                ->groupByRaw($date)->get();
            [$title, $subtitle, $amountLabel, $countLabel] = ['Tren purchase order', 'Nilai estimasi dan jumlah PO per hari.', 'Estimasi PO', 'Purchase order'];
        } elseif ($type === 'retur') {
            $date = $this->dateExpression('returns.tanggal_retur');
            $raw = $this->returnBase($branchIds, $filters)
                ->selectRaw($date.' as bucket')
                ->selectRaw('COUNT(returns.id) as count_value')
                ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as amount_value')
                ->groupByRaw($date)->get();
            [$title, $subtitle, $amountLabel, $countLabel] = ['Tren retur pembelian', 'Nilai dan dokumen retur supplier per hari.', 'Nilai retur', 'Dokumen retur'];
        } elseif (in_array($type, ['faktur', 'belum-lunas', 'jatuh-tempo'], true)) {
            $dateColumn = $type === 'jatuh-tempo' ? 'receipts.tanggal_jatuh_tempo' : 'COALESCE(receipts.tanggal_faktur, receipts.tanggal_penerimaan)';
            $date = $this->dateExpression($dateColumn);
            $amount = $type === 'faktur' ? $this->payableExpression('receipts') : $this->debtExpression('receipts');
            $raw = $this->invoiceBase($type, $branchIds, $filters)
                ->selectRaw($date.' as bucket')
                ->selectRaw('COUNT(receipts.id) as count_value')
                ->selectRaw('COALESCE(SUM('.$amount.'), 0) as amount_value')
                ->groupByRaw($date)->get();
            [$title, $subtitle, $amountLabel, $countLabel] = $type === 'faktur'
                ? ['Tren faktur pembelian', 'Tagihan neto dan jumlah invoice per hari.', 'Tagihan neto', 'Faktur']
                : ['Jadwal hutang supplier', 'Sisa hutang berdasarkan tanggal laporan.', 'Sisa hutang', 'Faktur'];
        } else {
            $date = $this->dateExpression('receipts.tanggal_penerimaan');
            $raw = $this->receiptBase($branchIds, $filters)
                ->selectRaw($date.' as bucket')
                ->selectRaw('COUNT(receipts.id) as count_value')
                ->selectRaw('COALESCE(SUM('.$this->payableExpression('receipts').'), 0) as amount_value')
                ->groupByRaw($date)->get();
            [$title, $subtitle, $amountLabel, $countLabel] = ['Tren pembelian aktual', 'Nilai penerimaan posted dan jumlah dokumen per hari.', 'Pembelian aktual', 'Penerimaan'];
        }

        $byDate = $raw->keyBy('bucket');
        $points = collect();
        $cursor = $filters['start']->copy();
        while ($cursor->lte($filters['end'])) {
            $key = $cursor->toDateString();
            $points->push([
                'label' => $key,
                'amount' => (float) ($byDate->get($key)?->amount_value ?? 0),
                'count' => (int) ($byDate->get($key)?->count_value ?? 0),
            ]);
            $cursor->addDay();
        }

        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'labels' => $points->pluck('label')->all(),
            'series' => [
                ['name' => $amountLabel, 'format' => 'currency', 'data' => $points->pluck('amount')->all()],
                ['name' => $countLabel, 'format' => 'number', 'data' => $points->pluck('count')->all()],
            ],
        ];
    }

    private function insight(string $type, array $values, array $trend): array
    {
        $amounts = collect($trend['series'][0]['data'] ?? []);
        $labels = collect($trend['labels'] ?? []);
        $peakValue = (float) ($amounts->max() ?? 0);
        $peakIndex = $amounts->search($peakValue);
        $peakLabel = $peakIndex === false ? '-' : (string) $labels->get($peakIndex, '-');

        if ($type === 'supplier' && ($values['best_supplier'] ?? null)) {
            $title = 'Supplier teratas: '.$values['best_supplier'];
            $copy = 'Skor '.number_format((float) $values['best_supplier_score'], 1, ',', '.').' / 100 menggabungkan rasio retur, kecepatan kirim, dan pemenuhan kuantitas.';
        } elseif ($type === 'retur') {
            $title = ($values['return_value'] ?? 0) > 0 ? 'Retur supplier perlu ditindaklanjuti' : 'Tidak ada retur posted';
            $copy = ($values['return_value'] ?? 0) > 0
                ? 'Periksa sisa ganti rugi dan alasan retur untuk memperbaiki keputusan supplier berikutnya.'
                : 'Belum ada retur supplier pada periode yang dipilih.';
        } elseif (in_array($type, ['belum-lunas', 'jatuh-tempo'], true)) {
            $title = ($values['debt_value'] ?? 0) > 0 ? 'Hutang supplier perlu dijadwalkan' : 'Tidak ada saldo hutang';
            $copy = ($values['debt_value'] ?? 0) > 0
                ? 'Prioritaskan faktur dengan tanggal jatuh tempo terdekat dan saldo terbesar.'
                : 'Seluruh faktur dalam cakupan laporan sudah terselesaikan.';
        } else {
            $title = $peakValue > 0 ? 'Aktivitas tertinggi: '.$peakLabel : 'Belum ada aktivitas pembelian';
            $copy = $peakValue > 0
                ? 'Gunakan tabel detail dan filter supplier untuk menelusuri kontributor nilai terbesar.'
                : 'Ubah periode, cabang, atau supplier untuk menemukan data pembelian.';
        }

        $purchaseValue = (float) ($values['purchase_value'] ?? 0);
        $returnValue = (float) ($values['return_value'] ?? 0);

        return [
            'title' => $title,
            'copy' => $copy,
            'peak_value' => $peakValue,
            'peak_label' => $peakLabel,
            'net_after_returns' => (float) ($values['net_after_returns'] ?? ($purchaseValue - $returnValue)),
            'return_ratio' => $purchaseValue > 0 ? ($returnValue / $purchaseValue) * 100 : 0,
            'average_ticket' => 0,
        ];
    }

    private function normalizeRows(Collection $rows, array $columns): Collection
    {
        $numericKeys = collect($columns)->whereIn('type', ['number', 'currency', 'signed_currency', 'percent'])->pluck('key')->all();

        return $rows->map(function ($row) use ($numericKeys) {
            $normalized = (array) $row;
            foreach ($numericKeys as $key) {
                if (array_key_exists($key, $normalized)) {
                    $normalized[$key] = $normalized[$key] === null ? null : (float) $normalized[$key];
                }
            }

            return $normalized;
        });
    }

    private function poBase(array $branchIds, array $filters): Builder
    {
        $query = DB::table('purchase_orders as po')
            ->whereIn('po.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->whereBetween('po.tanggal_po', [$filters['start']->toDateString(), $filters['end']->toDateString()]);

        if ($filters['supplier_id'] ?? null) {
            $query->where('po.distributor_id', $filters['supplier_id']);
        }

        return $query;
    }

    private function receiptBase(array $branchIds, array $filters): Builder
    {
        $query = DB::table('penerimaan_barang as receipts')
            ->join('purchase_orders as po', 'po.id', '=', 'receipts.purchase_order_id')
            ->whereIn('po.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('receipts.status', 'posted')
            ->whereBetween('receipts.tanggal_penerimaan', [$filters['start']->toDateString(), $filters['end']->toDateString()]);

        if ($filters['supplier_id'] ?? null) {
            $query->where('receipts.distributor_id', $filters['supplier_id']);
        }

        return $query;
    }

    private function receiptDetailBase(array $branchIds, array $filters): Builder
    {
        return $this->receiptBase($branchIds, $filters)
            ->join('penerimaan_barang_detail as details', 'details.penerimaan_barang_id', '=', 'receipts.id');
    }

    private function returnBase(array $branchIds, array $filters): Builder
    {
        $query = DB::table('retur_pembelian as returns')
            ->join('purchase_orders as po', 'po.id', '=', 'returns.purchase_order_id')
            ->whereIn('po.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('returns.status', 'posted')
            ->whereBetween('returns.tanggal_retur', [$filters['start']->toDateString(), $filters['end']->toDateString()]);

        if ($filters['supplier_id'] ?? null) {
            $query->where('returns.distributor_id', $filters['supplier_id']);
        }

        return $query;
    }

    private function invoiceBase(string $type, array $branchIds, array $filters): Builder
    {
        $query = DB::table('penerimaan_barang as receipts')
            ->join('purchase_orders as po', 'po.id', '=', 'receipts.purchase_order_id')
            ->whereIn('po.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('receipts.status', '!=', 'cancelled')
            ->whereNotNull('receipts.nomor_faktur')
            ->where('receipts.nomor_faktur', '!=', '');

        if ($filters['supplier_id'] ?? null) {
            $query->where('receipts.distributor_id', $filters['supplier_id']);
        }

        if ($type === 'jatuh-tempo') {
            $query->whereNotNull('receipts.tanggal_jatuh_tempo')
                ->whereBetween('receipts.tanggal_jatuh_tempo', [$filters['start']->toDateString(), $filters['end']->toDateString()]);
        } else {
            $query->whereBetween(DB::raw('COALESCE(receipts.tanggal_faktur, receipts.tanggal_penerimaan)'), [
                $filters['start']->toDateString(), $filters['end']->toDateString(),
            ]);
        }

        if (in_array($type, ['belum-lunas', 'jatuh-tempo'], true)) {
            $query->whereRaw($this->debtExpression('receipts').' > 0');
        }

        return $query;
    }

    private function compensationTotals(): Builder
    {
        return DB::table('retur_pembelian_compensations as compensations')
            ->whereNull('compensations.cancelled_at')
            ->groupBy('compensations.retur_pembelian_id')
            ->select('compensations.retur_pembelian_id')
            ->selectRaw('COALESCE(SUM(compensations.nominal), 0) as received_value');
    }

    private function invoiceExpression(string $alias): string
    {
        return 'CASE WHEN COALESCE('.$alias.'.total_faktur, 0) > 0 THEN '.$alias.'.total_faktur ELSE COALESCE('.$alias.'.grand_total, 0) END';
    }

    private function payableExpression(string $alias): string
    {
        $invoice = $this->invoiceExpression($alias);

        return 'CASE WHEN ('.$invoice.' - COALESCE('.$alias.'.supplier_compensation_discount, 0)) > 0 THEN ('.$invoice.' - COALESCE('.$alias.'.supplier_compensation_discount, 0)) ELSE 0 END';
    }

    private function debtExpression(string $alias): string
    {
        $payable = $this->payableExpression($alias);

        return 'CASE WHEN ('.$payable.' - COALESCE('.$alias.'.jumlah_dibayar, 0)) > 0 THEN ('.$payable.' - COALESCE('.$alias.'.jumlah_dibayar, 0)) ELSE 0 END';
    }

    private function unitPurchasePriceExpression(string $alias): string
    {
        return 'CASE WHEN COALESCE('.$alias.'.harga_beli_stok, 0) > 0 THEN '.$alias.'.harga_beli_stok WHEN COALESCE('.$alias.'.konversi_satuan, 0) > 0 THEN '.$alias.'.harga_beli / '.$alias.'.konversi_satuan ELSE '.$alias.'.harga_beli END';
    }

    private function dateExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', {$column})"
            : "DATE({$column})";
    }

    private function averageLeadDaysExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? 'AVG(julianday(receipts.tanggal_penerimaan) - julianday(po.tanggal_po))'
            : 'AVG(DATEDIFF(receipts.tanggal_penerimaan, po.tanggal_po))';
    }

    private function daysFromTodayExpression(string $column): string
    {
        $today = today()->toDateString();

        return DB::connection()->getDriverName() === 'sqlite'
            ? "CASE WHEN {$column} IS NULL THEN NULL ELSE CAST(julianday({$column}) - julianday('{$today}') AS INTEGER) END"
            : "CASE WHEN {$column} IS NULL THEN NULL ELSE DATEDIFF({$column}, '{$today}') END";
    }

    private function dueStatusExpression(string $column): string
    {
        $today = today()->toDateString();
        $soon = today()->addDays(7)->toDateString();

        return "CASE WHEN {$column} IS NULL THEN 'tanpa_tempo' WHEN {$column} < '{$today}' THEN 'terlambat' WHEN {$column} <= '{$soon}' THEN 'segera_jatuh_tempo' ELSE 'terjadwal' END";
    }
}
