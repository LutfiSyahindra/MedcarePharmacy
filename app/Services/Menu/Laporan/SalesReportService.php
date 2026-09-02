<?php

namespace App\Services\Menu\Laporan;

use App\Models\BranchModel;
use App\Models\User;
use App\Support\BranchAccess;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesReportService
{
    public const TYPES = [
        'ringkasan' => [
            'title' => 'Rekap Harian Penjualan',
            'short_title' => 'Rekap Harian',
            'description' => 'Rekap transaksi completed, kuantitas, diskon, dan omzet sebelum retur per hari.',
            'icon' => 'mdi-view-dashboard-outline',
            'tone' => 'navy',
        ],
        'detail' => [
            'title' => 'Detail Penjualan',
            'short_title' => 'Detail',
            'description' => 'Telusuri seluruh item pada transaksi penjualan yang berhasil diselesaikan.',
            'icon' => 'mdi-receipt-text-outline',
            'tone' => 'blue',
        ],
        'obat' => [
            'title' => 'Penjualan per Obat',
            'short_title' => 'Per Obat',
            'description' => 'Bandingkan kuantitas, frekuensi transaksi, dan omzet completed sebelum retur setiap produk obat.',
            'icon' => 'mdi-pill-multiple',
            'tone' => 'teal',
        ],
        'keuntungan' => [
            'title' => 'Keuntungan Penjualan',
            'short_title' => 'Keuntungan',
            'description' => 'Pantau penjualan neto, HPP neto, laba kotor, dan margin setelah memperhitungkan retur.',
            'icon' => 'mdi-finance',
            'tone' => 'green',
        ],
        'kategori' => [
            'title' => 'Penjualan per Kategori',
            'short_title' => 'Per Kategori',
            'description' => 'Lihat komposisi penjualan berdasarkan kategori produk.',
            'icon' => 'mdi-shape-outline',
            'tone' => 'violet',
        ],
        'kasir' => [
            'title' => 'Penjualan per Kasir',
            'short_title' => 'Per Kasir',
            'description' => 'Evaluasi produktivitas, nilai transaksi, dan rata-rata keranjang setiap kasir.',
            'icon' => 'mdi-account-tie-outline',
            'tone' => 'indigo',
        ],
        'shift' => [
            'title' => 'Penjualan per Shift',
            'short_title' => 'Per Shift',
            'description' => 'Rekonsiliasi aktivitas penjualan pada setiap shift kasir.',
            'icon' => 'mdi-clock-outline',
            'tone' => 'cyan',
        ],
        'metode-bayar' => [
            'title' => 'Penjualan per Metode Bayar',
            'short_title' => 'Metode Bayar',
            'description' => 'Analisis penerimaan tunai, QRIS, transfer, kartu, dan metode pembayaran lainnya.',
            'icon' => 'mdi-credit-card-multiple-outline',
            'tone' => 'green',
        ],
        'jenis' => [
            'title' => 'Penjualan per Jenis',
            'short_title' => 'Per Jenis',
            'description' => 'Bandingkan penjualan bebas, resep non racikan, racikan, kredit, dan instansi.',
            'icon' => 'mdi-format-list-bulleted-type',
            'tone' => 'purple',
        ],
        'retur' => [
            'title' => 'Retur Penjualan',
            'short_title' => 'Retur',
            'description' => 'Audit barang yang dikembalikan, alasan, metode refund, dan nilai retur.',
            'icon' => 'mdi-keyboard-return',
            'tone' => 'orange',
        ],
        'diskon' => [
            'title' => 'Diskon Penjualan',
            'short_title' => 'Diskon',
            'description' => 'Tinjau seluruh diskon item dan diskon transaksi serta dampaknya pada omzet.',
            'icon' => 'mdi-sale-outline',
            'tone' => 'amber',
        ],
        'pembatalan' => [
            'title' => 'Pembatalan Transaksi',
            'short_title' => 'Pembatalan',
            'description' => 'Pantau transaksi void atau cancel, nominal, petugas, dan alasan pembatalannya.',
            'icon' => 'mdi-cancel',
            'tone' => 'red',
        ],
        'jam' => [
            'title' => 'Penjualan per Jam',
            'short_title' => 'Per Jam',
            'description' => 'Temukan jam ramai apotek untuk menyusun layanan dan jadwal tim yang lebih efektif.',
            'icon' => 'mdi-chart-timeline-variant-shimmer',
            'tone' => 'rose',
        ],
    ];

    public function definition(string $type): array
    {
        return self::TYPES[$type] ?? throw ValidationException::withMessages([
            'report' => 'Jenis laporan penjualan tidak tersedia.',
        ]);
    }

    public function build(User $user, string $type, array $filters): array
    {
        $definition = $this->definition($type);
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $metrics = $this->metrics($context['branch_ids'], $filters, $type);
        $report = $this->reportQuery($type, $context['branch_ids'], $filters);
        $paginator = $this->paginate($report, $filters);
        $rows = $this->normalizeRows(collect($paginator->items()), $report['columns']);
        $trend = $this->trend($type, $context['branch_ids'], $filters);

        return [
            'meta' => [
                'type' => $type,
                ...$definition,
                'branch_label' => $context['branch_label'],
                'selected_branch_id' => $context['selected_branch_id'],
                'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'period_days' => $filters['start']->diffInDays($filters['end']) + 1,
                'amount_basis' => 'Omzet laporan berasal dari transaksi completed sebelum retur. Nilai setelah retur tersedia pada laporan keuntungan dan Analisis Omzet.',
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'metrics' => $metrics['cards'],
            'insight' => $this->insight($type, $metrics, $trend),
            'chart' => $trend,
            'table' => [
                'title' => $report['title'],
                'columns' => $report['columns'],
                'rows' => $rows->values()->all(),
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

    private function context(User $user, ?int $selectedBranchId): array
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

        $branchIds = $selectedBranchId ? [$selectedBranchId] : $allowedIds;

        return [
            'branches' => $branches,
            'branch_ids' => $branchIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function metrics(array $branchIds, array $filters, string $type): array
    {
        $completed = $this->sales($branchIds, $filters, 'completed')
            ->selectRaw('COUNT(sales.id) as transaction_count')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(sales.subtotal_gross), 0) as gross')
            ->selectRaw('COALESCE(SUM(sales.diskon_item_total + sales.diskon_transaksi_nominal), 0) as discount')
            ->first();

        $qty = $this->sales($branchIds, $filters, 'completed')
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->value('qty');

        $returns = $this->returns($branchIds, $filters)
            ->selectRaw('COUNT(DISTINCT returns.id) as return_count')
            ->selectRaw('COUNT(DISTINCT returns.penjualan_transaction_id) as return_transaction_count')
            ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as return_value')
            ->selectRaw('COALESCE(SUM(returns.total_qty), 0) as return_qty')
            ->first();

        $cancelled = $this->cancelledSales($branchIds, $filters)
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw('COUNT(sales.id) as cancelled_count')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as cancelled_value')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as cancelled_qty')
            ->first();

        $transactionCount = (int) ($completed->transaction_count ?? 0);
        $revenue = (float) ($completed->revenue ?? 0);
        $gross = (float) ($completed->gross ?? 0);
        $discount = (float) ($completed->discount ?? 0);
        $returnCount = (int) ($returns->return_count ?? 0);
        $returnValue = (float) ($returns->return_value ?? 0);
        $cancelCount = (int) ($cancelled->cancelled_count ?? 0);
        $cancelValue = (float) ($cancelled->cancelled_value ?? 0);
        $cancelQty = (float) ($cancelled->cancelled_qty ?? 0);
        $discountedCount = (int) $this->sales($branchIds, $filters, 'completed')
            ->where(function (Builder $query) {
                $query->where('sales.diskon_item_total', '>', 0)
                    ->orWhere('sales.diskon_transaksi_nominal', '>', 0);
            })->count('sales.id');

        $values = [
            'revenue' => $revenue,
            'transaction_count' => $transactionCount,
            'qty' => (float) $qty,
            'discount' => $discount,
            'gross' => $gross,
            'average_ticket' => $transactionCount > 0 ? $revenue / $transactionCount : 0,
            'return_count' => $returnCount,
            'return_transaction_count' => (int) ($returns->return_transaction_count ?? 0),
            'return_value' => $returnValue,
            'return_qty' => (float) ($returns->return_qty ?? 0),
            'cancelled_count' => $cancelCount,
            'cancelled_value' => $cancelValue,
            'cancelled_qty' => $cancelQty,
            'discounted_count' => $discountedCount,
            'net_after_returns' => $revenue - $returnValue,
        ];

        if ($type === 'keuntungan') {
            $profitability = $this->profitabilityDailyQuery($branchIds, $filters)->get();
            $netSales = (float) $profitability->sum('net_sales');
            $netHpp = (float) $profitability->sum('net_hpp');
            $grossProfit = (float) $profitability->sum('gross_profit');
            $grossMargin = $netSales != 0.0 ? ($grossProfit / $netSales) * 100 : 0;
            $values = [
                ...$values,
                'net_sales' => $netSales,
                'net_hpp' => $netHpp,
                'gross_profit' => $grossProfit,
                'gross_margin' => $grossMargin,
            ];
            $cards = [
                $this->card('Penjualan neto', $netSales, 'currency', 'mdi-cash-check', 'Omzet selesai setelah nilai retur', 'navy'),
                $this->card('HPP neto', $netHpp, 'currency', 'mdi-package-variant-closed', 'HPP terjual setelah HPP barang retur', 'blue'),
                $this->card('Laba kotor', $grossProfit, 'signed_currency', 'mdi-finance', 'Penjualan neto dikurangi HPP neto', $grossProfit < 0 ? 'red' : 'green'),
                $this->card('Margin kotor', $grossMargin, 'percent', 'mdi-percent-outline', 'Persentase laba terhadap penjualan neto', 'teal'),
            ];
        } elseif ($type === 'retur') {
            $cards = [
                $this->card('Nilai retur', $returnValue, 'currency', 'mdi-cash-refund', 'Nilai retur berstatus posted', 'orange'),
                $this->card('Dokumen retur', $returnCount, 'number', 'mdi-keyboard-return', 'Retur berhasil diposting', 'blue'),
                $this->card('Qty dikembalikan', $values['return_qty'], 'number', 'mdi-package-variant-closed-minus', 'Akumulasi unit dikembalikan', 'violet'),
                $this->card('Rata-rata retur', $returnCount > 0 ? $returnValue / $returnCount : 0, 'currency', 'mdi-chart-line', 'Nilai rata-rata per dokumen', 'teal'),
            ];
        } elseif ($type === 'pembatalan') {
            $totalDecisions = $transactionCount + $cancelCount;
            $cards = [
                $this->card('Nilai dibatalkan', $cancelValue, 'currency', 'mdi-cash-remove', 'Potensi omzet yang dibatalkan', 'red'),
                $this->card('Transaksi batal', $cancelCount, 'number', 'mdi-cancel', 'Void atau cancel pada periode ini', 'orange'),
                $this->card('Qty dibatalkan', $cancelQty, 'number', 'mdi-package-variant-remove', 'Unit pada transaksi dibatalkan', 'violet'),
                $this->card('Rasio pembatalan', $totalDecisions > 0 ? ($cancelCount / $totalDecisions) * 100 : 0, 'percent', 'mdi-chart-donut', 'Dari transaksi selesai + batal', 'blue'),
            ];
        } elseif ($type === 'diskon') {
            $cards = [
                $this->card('Total diskon', $discount, 'currency', 'mdi-sale-outline', 'Diskon item + transaksi', 'amber'),
                $this->card('Transaksi berdiskon', $discountedCount, 'number', 'mdi-receipt-text-check-outline', 'Transaksi selesai dengan diskon', 'blue'),
                $this->card('Rata-rata diskon', $discountedCount > 0 ? $discount / $discountedCount : 0, 'currency', 'mdi-calculator-variant-outline', 'Per transaksi berdiskon', 'violet'),
                $this->card('Rasio diskon', $gross > 0 ? ($discount / $gross) * 100 : 0, 'percent', 'mdi-percent-outline', 'Terhadap subtotal bruto', 'teal'),
            ];
        } else {
            $cards = [
                $this->card('Omzet sebelum retur', $revenue, 'currency', 'mdi-cash-multiple', 'Transaksi completed; retur ditampilkan terpisah', 'navy'),
                $this->card('Total transaksi', $transactionCount, 'number', 'mdi-receipt-text-check-outline', 'Rata-rata '.number_format($values['average_ticket'], 0, ',', '.').' / transaksi', 'blue'),
                $this->card('Qty terjual', $values['qty'], 'number', 'mdi-package-variant-closed-check', 'Akumulasi seluruh item', 'teal'),
                $this->card('Total diskon', $discount, 'currency', 'mdi-sale-outline', 'Diskon item + transaksi', 'amber'),
            ];
        }

        return ['cards' => $cards, 'values' => $values];
    }

    private function card(string $label, float|int $value, string $format, string $icon, string $note, string $tone): array
    {
        return compact('label', 'value', 'format', 'icon', 'note', 'tone');
    }

    private function reportQuery(string $type, array $branchIds, array $filters): array
    {
        return match ($type) {
            'ringkasan' => $this->summaryReport($branchIds, $filters),
            'detail' => $this->detailReport($branchIds, $filters),
            'obat' => $this->medicineReport($branchIds, $filters),
            'keuntungan' => $this->profitabilityReport($branchIds, $filters),
            'kategori' => $this->categoryReport($branchIds, $filters),
            'kasir' => $this->cashierReport($branchIds, $filters),
            'shift' => $this->shiftReport($branchIds, $filters),
            'metode-bayar' => $this->paymentReport($branchIds, $filters),
            'jenis' => $this->typeReport($branchIds, $filters),
            'retur' => $this->returnReport($branchIds, $filters),
            'diskon' => $this->discountReport($branchIds, $filters),
            'pembatalan' => $this->cancellationReport($branchIds, $filters),
            'jam' => $this->hourlyReport($branchIds, $filters),
        };
    }

    private function summaryReport(array $branchIds, array $filters): array
    {
        $date = $this->dateExpression('sales.tanggal_transaksi');
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw("{$date} as sale_date")
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(item_totals.item_count), 0) as items')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as qty')
            ->selectRaw('COALESCE(SUM(sales.subtotal_gross), 0) as gross')
            ->selectRaw('COALESCE(SUM(sales.diskon_item_total + sales.diskon_transaksi_nominal), 0) as discount')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('COALESCE(AVG(sales.grand_total), 0) as average_ticket')
            ->groupByRaw($date);

        return $this->report('Rekap transaksi completed per hari', $query, [
            $this->column('sale_date', 'Tanggal', 'date'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('items', 'Item', 'number'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('gross', 'Bruto', 'currency'),
            $this->column('discount', 'Diskon', 'currency'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('average_ticket', 'Rata-rata', 'currency'),
        ], [], [
            'sale_date' => 'sale_date', 'transactions' => 'transactions', 'items' => 'items', 'qty' => 'qty',
            'gross' => 'gross', 'discount' => 'discount', 'revenue' => 'revenue', 'average_ticket' => 'average_ticket',
        ], 'sale_date', 'desc');
    }

    private function detailReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'sales.completed_by')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->select('details.id as row_id', 'sales.tanggal_transaksi as sale_date', 'sales.nomor_transaksi as transaction_number')
            ->selectRaw("COALESCE(NULLIF(details.kode_obat, ''), 'Kode tidak tersedia') as product_code")
            ->addSelect('details.nama_obat as product_name')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->addSelect('sales.jenis_transaksi as transaction_type', 'details.qty_jual as qty')
            ->selectRaw("COALESCE(NULLIF(details.satuan_jual, ''), 'Satuan tidak tercatat') as unit")
            ->addSelect('details.harga_jual as unit_price', 'details.diskon_nominal as discount', 'details.total_line as line_total')
            ->selectRaw("COALESCE(cashiers.name, 'Tanpa kasir') as cashier")
            ->selectRaw("COALESCE(branches.name, 'Cabang tidak tersedia') as branch");

        return $this->report('Seluruh item transaksi', $query, [
            $this->column('sale_date', 'Waktu transaksi', 'datetime'),
            $this->column('transaction_number', 'No. transaksi'),
            $this->column('product_code', 'Kode obat'),
            $this->column('product_name', 'Nama obat'),
            $this->column('category', 'Kategori'),
            $this->column('transaction_type', 'Jenis', 'transaction_type'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('unit', 'Satuan'),
            $this->column('unit_price', 'Harga', 'currency'),
            $this->column('discount', 'Diskon item', 'currency'),
            $this->column('line_total', 'Total', 'currency'),
            $this->column('cashier', 'Kasir'),
            $this->column('branch', 'Cabang'),
        ], ['sales.nomor_transaksi', 'details.kode_obat', 'details.nama_obat', 'categories.name', 'cashiers.name', 'branches.name'], [
            'sale_date' => 'sales.tanggal_transaksi', 'transaction_number' => 'sales.nomor_transaksi',
            'product_code' => 'details.kode_obat', 'product_name' => 'details.nama_obat', 'category' => 'categories.name',
            'transaction_type' => 'sales.jenis_transaksi', 'qty' => 'details.qty_jual', 'unit_price' => 'details.harga_jual',
            'discount' => 'details.diskon_nominal', 'line_total' => 'details.total_line', 'cashier' => 'cashiers.name', 'branch' => 'branches.name',
        ], 'sale_date', 'desc');
    }

    private function medicineReport(array $branchIds, array $filters): array
    {
        $allocatedTransactionDiscount = $this->allocatedTransactionDiscountExpression();
        $netRevenue = "(details.total_line - {$allocatedTransactionDiscount})";

        $query = $this->sales($branchIds, $filters, 'completed')
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->detailTotals(), 'detail_totals', 'detail_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->selectRaw("COALESCE(NULLIF(details.kode_obat, ''), 'Kode tidak tersedia') as product_code")
            ->addSelect('details.nama_obat as product_name')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->selectRaw("COALESCE(NULLIF(details.satuan_jual, ''), 'Satuan tidak tercatat') as unit")
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.subtotal_gross), 0) as gross')
            ->selectRaw("COALESCE(ROUND(SUM(details.diskon_nominal + {$allocatedTransactionDiscount}), 2), 0) as discount")
            ->selectRaw("COALESCE(ROUND(SUM({$netRevenue}), 2), 0) as revenue")
            ->groupBy('details.obat_id', 'details.kode_obat', 'details.nama_obat', 'categories.name', 'details.satuan_jual');

        return $this->report('Kontribusi transaksi completed setiap obat', $query, [
            $this->column('product_code', 'Kode obat'),
            $this->column('product_name', 'Nama obat'),
            $this->column('category', 'Kategori'),
            $this->column('unit', 'Satuan'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('qty', 'Qty terjual', 'number'),
            $this->column('gross', 'Bruto', 'currency'),
            $this->column('discount', 'Diskon', 'currency'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
        ], ['details.kode_obat', 'details.nama_obat', 'categories.name'], [
            'product_code' => 'product_code', 'product_name' => 'product_name', 'category' => 'category',
            'transactions' => 'transactions', 'qty' => 'qty', 'gross' => 'gross', 'discount' => 'discount', 'revenue' => 'revenue',
        ], 'revenue', 'desc');
    }

    private function profitabilityReport(array $branchIds, array $filters): array
    {
        return $this->report('Realisasi keuntungan penjualan harian', $this->profitabilityDailyQuery($branchIds, $filters), [
            $this->column('sale_date', 'Tanggal', 'date'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('gross_sales', 'Penjualan bruto', 'currency'),
            $this->column('returns_value', 'Nilai retur', 'currency'),
            $this->column('net_sales', 'Penjualan neto', 'currency'),
            $this->column('sales_hpp', 'HPP penjualan', 'currency'),
            $this->column('return_hpp', 'HPP retur', 'currency'),
            $this->column('net_hpp', 'HPP neto', 'currency'),
            $this->column('gross_profit', 'Laba kotor', 'signed_currency'),
            $this->column('gross_margin', 'Margin kotor', 'percent'),
        ], [], [
            'sale_date' => 'sale_date', 'transactions' => 'transactions', 'gross_sales' => 'gross_sales',
            'returns_value' => 'returns_value', 'net_sales' => 'net_sales', 'sales_hpp' => 'sales_hpp',
            'return_hpp' => 'return_hpp', 'net_hpp' => 'net_hpp', 'gross_profit' => 'gross_profit',
            'gross_margin' => 'gross_margin',
        ], 'sale_date', 'desc');
    }

    private function profitabilityDailyQuery(array $branchIds, array $filters): Builder
    {
        $saleDate = $this->dateExpression('sales.tanggal_transaksi');
        $returnDate = $this->dateExpression('returns.tanggal_retur');

        $saleEvents = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->transactionCosts(), 'transaction_costs', 'transaction_costs.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw("{$saleDate} as event_date")
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as gross_sales')
            ->selectRaw('0 as returns_value')
            ->selectRaw('COALESCE(SUM(transaction_costs.hpp), 0) as sales_hpp')
            ->selectRaw('0 as return_hpp')
            ->groupByRaw($saleDate);

        $returnEvents = $this->returns($branchIds, $filters)
            ->leftJoinSub($this->returnDocumentCosts(), 'return_costs', 'return_costs.retur_penjualan_id', '=', 'returns.id')
            ->selectRaw("{$returnDate} as event_date")
            ->selectRaw('0 as transactions')
            ->selectRaw('0 as gross_sales')
            ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as returns_value')
            ->selectRaw('0 as sales_hpp')
            ->selectRaw('COALESCE(SUM(return_costs.hpp), 0) as return_hpp')
            ->groupByRaw($returnDate);

        $events = $saleEvents->unionAll($returnEvents);
        $netSales = '(SUM(gross_sales) - SUM(returns_value))';
        $netHpp = '(SUM(sales_hpp) - SUM(return_hpp))';
        $grossProfit = "({$netSales} - {$netHpp})";

        return DB::query()
            ->fromSub($events, 'profit_events')
            ->selectRaw('event_date as sale_date')
            ->selectRaw('SUM(transactions) as transactions')
            ->selectRaw('ROUND(SUM(gross_sales), 2) as gross_sales')
            ->selectRaw('ROUND(SUM(returns_value), 2) as returns_value')
            ->selectRaw("ROUND({$netSales}, 2) as net_sales")
            ->selectRaw('ROUND(SUM(sales_hpp), 2) as sales_hpp')
            ->selectRaw('ROUND(SUM(return_hpp), 2) as return_hpp')
            ->selectRaw("ROUND({$netHpp}, 2) as net_hpp")
            ->selectRaw("ROUND({$grossProfit}, 2) as gross_profit")
            ->selectRaw("CASE WHEN {$netSales} != 0 THEN ROUND(((1.0 * {$grossProfit}) / {$netSales}) * 100, 2) ELSE 0 END as gross_margin")
            ->groupBy('event_date');
    }

    private function categoryReport(array $branchIds, array $filters): array
    {
        $allocatedTransactionDiscount = $this->allocatedTransactionDiscountExpression();
        $netRevenue = "(details.total_line - {$allocatedTransactionDiscount})";
        $query = $this->sales($branchIds, $filters, 'completed')
            ->join('penjualan_transaction_details as details', 'details.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoinSub($this->detailTotals(), 'detail_totals', 'detail_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('master_obats as medicines', 'medicines.id', '=', 'details.obat_id')
            ->leftJoin('categories', 'categories.id', '=', 'medicines.category_id')
            ->selectRaw("COALESCE(categories.name, 'Tanpa kategori') as category")
            ->selectRaw('COUNT(DISTINCT details.obat_id) as products')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(details.qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM(details.subtotal_gross), 0) as gross')
            ->selectRaw("COALESCE(ROUND(SUM(details.diskon_nominal + {$allocatedTransactionDiscount}), 2), 0) as discount")
            ->selectRaw("COALESCE(ROUND(SUM({$netRevenue}), 2), 0) as revenue")
            ->groupBy('categories.id', 'categories.name');

        return $this->report('Performa kategori produk', $query, [
            $this->column('category', 'Kategori produk'),
            $this->column('products', 'Produk aktif', 'number'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('qty', 'Qty terjual', 'number'),
            $this->column('gross', 'Bruto', 'currency'),
            $this->column('discount', 'Diskon', 'currency'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
        ], ['categories.name'], [
            'category' => 'category', 'products' => 'products', 'transactions' => 'transactions',
            'qty' => 'qty', 'gross' => 'gross', 'discount' => 'discount', 'revenue' => 'revenue',
        ], 'revenue', 'desc');
    }

    private function cashierReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'sales.completed_by')
            ->selectRaw("COALESCE(cashiers.name, 'Tanpa kasir') as cashier")
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as qty')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(sales.diskon_item_total + sales.diskon_transaksi_nominal), 0) as discount')
            ->selectRaw('COALESCE(AVG(sales.grand_total), 0) as average_ticket')
            ->selectRaw('MAX(sales.tanggal_transaksi) as last_transaction')
            ->groupBy('sales.completed_by', 'cashiers.name');

        return $this->report('Performa kasir', $query, [
            $this->column('cashier', 'Kasir'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('qty', 'Qty terjual', 'number'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('discount', 'Diskon', 'currency'),
            $this->column('average_ticket', 'Rata-rata transaksi', 'currency'),
            $this->column('last_transaction', 'Transaksi terakhir', 'datetime'),
        ], ['cashiers.name'], [
            'cashier' => 'cashier', 'transactions' => 'transactions', 'qty' => 'qty', 'revenue' => 'revenue',
            'discount' => 'discount', 'average_ticket' => 'average_ticket', 'last_transaction' => 'last_transaction',
        ], 'revenue', 'desc');
    }

    private function shiftReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('cashier_shifts as shifts', 'shifts.id', '=', 'sales.cashier_shift_id')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'shifts.user_id')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->selectRaw("COALESCE(shifts.shift_number, 'Tanpa shift') as shift_number")
            ->selectRaw("COALESCE(cashiers.name, 'Tanpa kasir') as cashier")
            ->selectRaw("COALESCE(branches.name, 'Cabang tidak tersedia') as branch")
            ->selectRaw("COALESCE(shifts.status, 'unassigned') as shift_status")
            ->selectRaw('shifts.opened_at as opened_at, shifts.closed_at as closed_at')
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as qty')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('MAX(shifts.expected_cash) as expected_cash')
            ->selectRaw('MAX(shifts.actual_cash) as actual_cash')
            ->selectRaw('MAX(shifts.cash_difference) as cash_difference')
            ->groupBy('sales.cashier_shift_id', 'shifts.shift_number', 'cashiers.name', 'branches.name', 'shifts.status', 'shifts.opened_at', 'shifts.closed_at');

        return $this->report('Aktivitas setiap shift', $query, [
            $this->column('shift_number', 'Nomor shift'),
            $this->column('cashier', 'Kasir'),
            $this->column('branch', 'Cabang'),
            $this->column('shift_status', 'Status', 'shift_status'),
            $this->column('opened_at', 'Dibuka', 'datetime', 'Waktu buka tidak tercatat'),
            $this->column('closed_at', 'Ditutup', 'datetime', 'Shift masih aktif'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('expected_cash', 'Kas seharusnya', 'currency', 'Belum dihitung'),
            $this->column('actual_cash', 'Kas aktual', 'currency', 'Menunggu tutup shift'),
            $this->column('cash_difference', 'Selisih kas', 'signed_currency', 'Menunggu tutup shift'),
        ], ['shifts.shift_number', 'cashiers.name', 'branches.name'], [
            'shift_number' => 'shift_number', 'cashier' => 'cashier', 'branch' => 'branch', 'shift_status' => 'shift_status',
            'opened_at' => 'opened_at', 'closed_at' => 'closed_at', 'transactions' => 'transactions', 'qty' => 'qty',
            'revenue' => 'revenue', 'expected_cash' => 'expected_cash', 'actual_cash' => 'actual_cash', 'cash_difference' => 'cash_difference',
        ], 'opened_at', 'desc');
    }

    private function paymentReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->join('penjualan_payments as payments', 'payments.penjualan_transaction_id', '=', 'sales.id')
            ->select('payments.metode as payment_method')
            ->selectRaw('COUNT(DISTINCT sales.id) as transactions')
            ->selectRaw('COUNT(payments.id) as payment_entries')
            ->selectRaw('COUNT(DISTINCT payments.reference_no) as reference_count')
            ->selectRaw('COALESCE(SUM(payments.amount), 0) as amount')
            ->selectRaw('COALESCE(AVG(payments.amount), 0) as average_payment')
            ->groupBy('payments.metode');

        return $this->report('Komposisi metode pembayaran', $query, [
            $this->column('payment_method', 'Metode bayar', 'payment_method'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('payment_entries', 'Entri pembayaran', 'number'),
            $this->column('reference_count', 'Referensi', 'number'),
            $this->column('amount', 'Nilai diterima', 'currency'),
            $this->column('average_payment', 'Rata-rata', 'currency'),
        ], ['payments.metode', 'payments.reference_no'], [
            'payment_method' => 'payment_method', 'transactions' => 'transactions', 'payment_entries' => 'payment_entries',
            'reference_count' => 'reference_count', 'amount' => 'amount', 'average_payment' => 'average_payment',
        ], 'amount', 'desc');
    }

    private function typeReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->select('sales.jenis_transaksi as transaction_type')
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(item_totals.item_count), 0) as items')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as qty')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(sales.diskon_item_total + sales.diskon_transaksi_nominal), 0) as discount')
            ->selectRaw('COALESCE(AVG(sales.grand_total), 0) as average_ticket')
            ->groupBy('sales.jenis_transaksi');

        return $this->report('Performa jenis transaksi', $query, [
            $this->column('transaction_type', 'Jenis transaksi', 'transaction_type'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('items', 'Item', 'number'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('discount', 'Diskon', 'currency'),
            $this->column('average_ticket', 'Rata-rata', 'currency'),
        ], ['sales.jenis_transaksi'], [
            'transaction_type' => 'transaction_type', 'transactions' => 'transactions', 'items' => 'items',
            'qty' => 'qty', 'revenue' => 'revenue', 'discount' => 'discount', 'average_ticket' => 'average_ticket',
        ], 'revenue', 'desc');
    }

    private function returnReport(array $branchIds, array $filters): array
    {
        $query = $this->returns($branchIds, $filters)
            ->join('retur_penjualan_details as details', 'details.retur_penjualan_id', '=', 'returns.id')
            ->join('penjualan_transactions as sales', 'sales.id', '=', 'returns.penjualan_transaction_id')
            ->leftJoin('branches', 'branches.id', '=', 'returns.branch_id')
            ->leftJoin('users as officers', 'officers.id', '=', 'returns.posted_by')
            ->select('returns.tanggal_retur as return_date', 'returns.nomor_retur as return_number')
            ->addSelect('sales.nomor_transaksi as transaction_number', 'details.kode_obat as product_code', 'details.nama_obat as product_name')
            ->addSelect('details.qty_jual as qty')
            ->selectRaw("COALESCE(NULLIF(details.satuan_jual, ''), 'Satuan tidak tercatat') as unit")
            ->addSelect('details.total as return_value', 'returns.refund_method', 'details.alasan_item as item_reason')
            ->selectRaw("COALESCE(officers.name, 'Petugas tidak tercatat') as officer")
            ->selectRaw("COALESCE(branches.name, 'Cabang tidak tersedia') as branch");

        return $this->report('Barang dan nilai retur', $query, [
            $this->column('return_date', 'Tanggal retur', 'date'),
            $this->column('return_number', 'No. retur'),
            $this->column('transaction_number', 'Transaksi asal'),
            $this->column('product_code', 'Kode obat'),
            $this->column('product_name', 'Nama obat'),
            $this->column('qty', 'Qty retur', 'number'),
            $this->column('unit', 'Satuan'),
            $this->column('return_value', 'Nilai retur', 'currency'),
            $this->column('refund_method', 'Metode refund', 'refund_method'),
            $this->column('item_reason', 'Alasan'),
            $this->column('officer', 'Petugas'),
            $this->column('branch', 'Cabang'),
        ], ['returns.nomor_retur', 'sales.nomor_transaksi', 'details.kode_obat', 'details.nama_obat', 'details.alasan_item', 'officers.name'], [
            'return_date' => 'returns.tanggal_retur', 'return_number' => 'returns.nomor_retur',
            'transaction_number' => 'sales.nomor_transaksi', 'product_code' => 'details.kode_obat', 'product_name' => 'details.nama_obat',
            'qty' => 'details.qty_jual', 'return_value' => 'details.total', 'refund_method' => 'returns.refund_method',
            'officer' => 'officers.name', 'branch' => 'branches.name',
        ], 'return_date', 'desc');
    }

    private function discountReport(array $branchIds, array $filters): array
    {
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoin('users as cashiers', 'cashiers.id', '=', 'sales.completed_by')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->where(function (Builder $query) {
                $query->where('sales.diskon_item_total', '>', 0)
                    ->orWhere('sales.diskon_transaksi_nominal', '>', 0);
            })
            ->select('sales.tanggal_transaksi as sale_date', 'sales.nomor_transaksi as transaction_number')
            ->selectRaw("COALESCE(sales.customer_name, 'Umum') as customer")
            ->addSelect('sales.subtotal_gross as gross', 'sales.diskon_item_total as item_discount')
            ->addSelect('sales.diskon_transaksi_nominal as transaction_discount')
            ->selectRaw('(sales.diskon_item_total + sales.diskon_transaksi_nominal) as total_discount')
            ->selectRaw('CASE WHEN sales.subtotal_gross > 0 THEN ((sales.diskon_item_total + sales.diskon_transaksi_nominal) / sales.subtotal_gross) * 100 ELSE 0 END as discount_ratio')
            ->addSelect('sales.grand_total as revenue')
            ->selectRaw("COALESCE(cashiers.name, 'Tanpa kasir') as cashier")
            ->selectRaw("COALESCE(branches.name, 'Cabang tidak tersedia') as branch");

        return $this->report('Seluruh diskon transaksi', $query, [
            $this->column('sale_date', 'Waktu transaksi', 'datetime'),
            $this->column('transaction_number', 'No. transaksi'),
            $this->column('customer', 'Pelanggan'),
            $this->column('gross', 'Bruto', 'currency'),
            $this->column('item_discount', 'Diskon item', 'currency'),
            $this->column('transaction_discount', 'Diskon transaksi', 'currency'),
            $this->column('total_discount', 'Total diskon', 'currency'),
            $this->column('discount_ratio', 'Rasio', 'percent'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('cashier', 'Kasir'),
            $this->column('branch', 'Cabang'),
        ], ['sales.nomor_transaksi', 'sales.customer_name', 'cashiers.name', 'branches.name'], [
            'sale_date' => 'sales.tanggal_transaksi', 'transaction_number' => 'sales.nomor_transaksi', 'customer' => 'sales.customer_name',
            'gross' => 'sales.subtotal_gross', 'item_discount' => 'sales.diskon_item_total',
            'transaction_discount' => 'sales.diskon_transaksi_nominal', 'total_discount' => 'total_discount',
            'discount_ratio' => 'discount_ratio', 'revenue' => 'sales.grand_total', 'cashier' => 'cashiers.name', 'branch' => 'branches.name',
        ], 'total_discount', 'desc');
    }

    private function cancellationReport(array $branchIds, array $filters): array
    {
        $cancelledAt = 'COALESCE(sales.cancelled_at, sales.tanggal_transaksi)';
        $query = $this->cancelledSales($branchIds, $filters)
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->leftJoin('users as officers', 'officers.id', '=', 'sales.cancelled_by')
            ->leftJoin('branches', 'branches.id', '=', 'sales.branch_id')
            ->selectRaw("{$cancelledAt} as cancelled_at")
            ->addSelect('sales.nomor_transaksi as transaction_number')
            ->selectRaw("COALESCE(sales.customer_name, 'Umum') as customer")
            ->addSelect('sales.jenis_transaksi as transaction_type')
            ->selectRaw('COALESCE(item_totals.item_count, 0) as items')
            ->selectRaw('COALESCE(item_totals.qty, 0) as qty')
            ->addSelect('sales.grand_total as cancelled_value')
            ->selectRaw("COALESCE(NULLIF(sales.cancellation_reason, ''), 'Alasan tidak dicatat') as reason")
            ->selectRaw("COALESCE(officers.name, 'Petugas tidak tercatat') as officer")
            ->selectRaw("COALESCE(branches.name, 'Cabang tidak tersedia') as branch");

        return $this->report('Transaksi void / cancel', $query, [
            $this->column('cancelled_at', 'Waktu pembatalan', 'datetime'),
            $this->column('transaction_number', 'No. transaksi'),
            $this->column('customer', 'Pelanggan'),
            $this->column('transaction_type', 'Jenis', 'transaction_type'),
            $this->column('items', 'Item', 'number'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('cancelled_value', 'Nilai dibatalkan', 'currency'),
            $this->column('reason', 'Alasan pembatalan'),
            $this->column('officer', 'Dibatalkan oleh'),
            $this->column('branch', 'Cabang'),
        ], ['sales.nomor_transaksi', 'sales.customer_name', 'sales.cancellation_reason', 'officers.name', 'branches.name'], [
            'cancelled_at' => 'cancelled_at', 'transaction_number' => 'sales.nomor_transaksi', 'customer' => 'sales.customer_name',
            'transaction_type' => 'sales.jenis_transaksi', 'items' => 'items', 'qty' => 'qty',
            'cancelled_value' => 'sales.grand_total', 'officer' => 'officers.name', 'branch' => 'branches.name',
        ], 'cancelled_at', 'desc');
    }

    private function hourlyReport(array $branchIds, array $filters): array
    {
        $hour = $this->hourExpression('sales.tanggal_transaksi');
        $query = $this->sales($branchIds, $filters, 'completed')
            ->leftJoinSub($this->detailTotals(), 'item_totals', 'item_totals.penjualan_transaction_id', '=', 'sales.id')
            ->selectRaw("{$hour} as sale_hour")
            ->selectRaw('COUNT(sales.id) as transactions')
            ->selectRaw('COALESCE(SUM(item_totals.item_count), 0) as items')
            ->selectRaw('COALESCE(SUM(item_totals.qty), 0) as qty')
            ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as revenue')
            ->selectRaw('COALESCE(AVG(sales.grand_total), 0) as average_ticket')
            ->groupByRaw($hour);

        return $this->report('Pola kunjungan per jam', $query, [
            $this->column('sale_hour', 'Rentang waktu', 'hour'),
            $this->column('transactions', 'Transaksi', 'number'),
            $this->column('items', 'Item', 'number'),
            $this->column('qty', 'Qty', 'number'),
            $this->column('revenue', 'Omzet sebelum retur', 'currency'),
            $this->column('average_ticket', 'Rata-rata', 'currency'),
        ], [], [
            'sale_hour' => 'sale_hour', 'transactions' => 'transactions', 'items' => 'items',
            'qty' => 'qty', 'revenue' => 'revenue', 'average_ticket' => 'average_ticket',
        ], 'sale_hour', 'asc');
    }

    private function report(string $title, Builder $query, array $columns, array $searchable, array $sorts, string $defaultSort, string $defaultDirection): array
    {
        return [
            'title' => $title,
            'query' => $query,
            'columns' => $columns,
            'searchable' => $searchable,
            'sorts' => $sorts,
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
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($field, 'like', '%'.$search.'%');
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
        if ($type === 'jam') {
            $hour = $this->hourExpression('sales.tanggal_transaksi');
            $raw = $this->sales($branchIds, $filters, 'completed')
                ->selectRaw("{$hour} as bucket")
                ->selectRaw('COUNT(sales.id) as count_value')
                ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as amount_value')
                ->groupByRaw($hour)
                ->get()->keyBy(fn ($row) => (int) $row->bucket);
            $points = collect(range(0, 23))->map(fn (int $hourValue) => [
                'label' => str_pad((string) $hourValue, 2, '0', STR_PAD_LEFT).':00',
                'amount' => (float) ($raw->get($hourValue)?->amount_value ?? 0),
                'count' => (int) ($raw->get($hourValue)?->count_value ?? 0),
            ]);

            return $this->chart('Pola omzet per jam', 'Identifikasi jam layanan dengan aktivitas tertinggi.', $points, 'Omzet', 'Transaksi');
        }

        if ($type === 'keuntungan') {
            $raw = $this->profitabilityDailyQuery($branchIds, $filters)
                ->get()
                ->keyBy('sale_date');
            $cursor = $filters['start']->copy()->startOfDay();
            $end = $filters['end']->copy()->startOfDay();
            $points = collect();
            while ($cursor->lte($end)) {
                $key = $cursor->toDateString();
                $points->push([
                    'label' => $key,
                    'amount' => (float) ($raw->get($key)?->gross_profit ?? 0),
                    'count' => (int) ($raw->get($key)?->transactions ?? 0),
                ]);
                $cursor->addDay();
            }

            return $this->chart(
                'Tren keuntungan penjualan',
                'Pergerakan laba kotor setelah penjualan dan retur setiap hari.',
                $points,
                'Laba kotor',
                'Transaksi',
            );
        }

        $dateColumn = match ($type) {
            'retur' => 'returns.tanggal_retur',
            'pembatalan' => 'COALESCE(sales.cancelled_at, sales.tanggal_transaksi)',
            default => 'sales.tanggal_transaksi',
        };
        $date = $this->dateExpression($dateColumn);

        if ($type === 'retur') {
            $raw = $this->returns($branchIds, $filters)
                ->selectRaw("{$date} as bucket")
                ->selectRaw('COUNT(returns.id) as count_value')
                ->selectRaw('COALESCE(SUM(returns.grand_total), 0) as amount_value')
                ->groupByRaw($date)->get();
            $title = 'Tren nilai retur';
            $subtitle = 'Nilai dan jumlah dokumen retur posted per hari.';
            $amountLabel = 'Nilai retur';
            $countLabel = 'Dokumen retur';
        } elseif ($type === 'pembatalan') {
            $raw = $this->cancelledSales($branchIds, $filters)
                ->selectRaw("{$date} as bucket")
                ->selectRaw('COUNT(sales.id) as count_value')
                ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as amount_value')
                ->groupByRaw($date)->get();
            $title = 'Tren pembatalan';
            $subtitle = 'Nilai dan jumlah transaksi yang dibatalkan per hari.';
            $amountLabel = 'Nilai batal';
            $countLabel = 'Transaksi batal';
        } elseif ($type === 'diskon') {
            $raw = $this->sales($branchIds, $filters, 'completed')
                ->selectRaw("{$date} as bucket")
                ->selectRaw('COUNT(CASE WHEN sales.diskon_item_total > 0 OR sales.diskon_transaksi_nominal > 0 THEN 1 END) as count_value')
                ->selectRaw('COALESCE(SUM(sales.diskon_item_total + sales.diskon_transaksi_nominal), 0) as amount_value')
                ->groupByRaw($date)->get();
            $title = 'Tren pemberian diskon';
            $subtitle = 'Nilai diskon item dan transaksi per hari.';
            $amountLabel = 'Nilai diskon';
            $countLabel = 'Transaksi diskon';
        } else {
            $raw = $this->sales($branchIds, $filters, 'completed')
                ->selectRaw("{$date} as bucket")
                ->selectRaw('COUNT(sales.id) as count_value')
                ->selectRaw('COALESCE(SUM(sales.grand_total), 0) as amount_value')
                ->groupByRaw($date)->get();
            $title = 'Tren omzet sebelum retur';
            $subtitle = 'Pergerakan omzet transaksi completed sebelum retur dan jumlah transaksi per hari.';
            $amountLabel = 'Omzet sebelum retur';
            $countLabel = 'Transaksi';
        }

        $byDate = $raw->keyBy('bucket');
        $cursor = $filters['start']->copy()->startOfDay();
        $end = $filters['end']->copy()->startOfDay();
        $points = collect();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $points->push([
                'label' => $key,
                'amount' => (float) ($byDate->get($key)?->amount_value ?? 0),
                'count' => (int) ($byDate->get($key)?->count_value ?? 0),
            ]);
            $cursor->addDay();
        }

        return $this->chart($title, $subtitle, $points, $amountLabel, $countLabel);
    }

    private function chart(string $title, string $subtitle, Collection $points, string $amountLabel, string $countLabel): array
    {
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

    private function insight(string $type, array $metrics, array $trend): array
    {
        $values = $metrics['values'];
        $amounts = collect($trend['series'][0]['data'] ?? []);
        $labels = collect($trend['labels'] ?? []);
        $peakValue = (float) ($amounts->max() ?? 0);
        $peakIndex = $amounts->search($peakValue);
        $peakLabel = $peakIndex === false ? '-' : (string) $labels->get($peakIndex, '-');

        if ($type === 'keuntungan') {
            $counts = collect($trend['series'][1]['data'] ?? []);
            $activeIndexes = $amounts->keys()->filter(
                fn ($index) => (float) $amounts->get($index, 0) != 0.0 || (float) $counts->get($index, 0) != 0.0,
            );
            $peakIndex = $activeIndexes->sortByDesc(fn ($index) => (float) $amounts->get($index, 0))->first();
            $peakValue = $peakIndex === null ? 0 : (float) $amounts->get($peakIndex, 0);
            $peakLabel = $peakIndex === null ? '-' : (string) $labels->get($peakIndex, '-');
        }

        if ($type === 'retur') {
            $title = $values['return_count'] > 0 ? 'Retur perlu dipantau' : 'Tidak ada retur posted';
            $copy = $values['return_count'] > 0
                ? 'Puncak nilai retur berada pada '.$peakLabel.'. Tinjau alasan item untuk menemukan pola kualitas atau layanan.'
                : 'Belum ada nilai retur yang mengurangi hasil penjualan pada periode aktif.';
        } elseif ($type === 'pembatalan') {
            $title = $values['cancelled_count'] > 0 ? 'Ada transaksi yang dibatalkan' : 'Tidak ada pembatalan';
            $copy = $values['cancelled_count'] > 0
                ? 'Puncak nilai pembatalan berada pada '.$peakLabel.'. Gunakan detail alasan untuk menentukan tindak lanjut.'
                : 'Seluruh keputusan transaksi pada periode ini berakhir tanpa void atau cancel.';
        } elseif ($type === 'jam') {
            $title = $peakValue > 0 ? 'Jam ramai: '.$peakLabel : 'Belum ada jam ramai';
            $copy = $peakValue > 0
                ? 'Omzet tertinggi terkonsentrasi di sekitar jam tersebut. Pertimbangkan kesiapan kasir dan stok fast-moving.'
                : 'Belum ada transaksi selesai pada periode yang dipilih.';
        } elseif ($type === 'keuntungan') {
            $hasActivity = $peakLabel !== '-';
            $title = ! $hasActivity
                ? 'Belum ada aktivitas penjualan'
                : ($values['gross_profit'] >= 0 ? 'Penjualan menghasilkan laba kotor' : 'HPP melampaui penjualan neto');
            $copy = $hasActivity
                ? 'Laba kotor harian tertinggi tercatat pada '.$peakLabel.'. Gunakan rincian harian untuk menilai penjualan, retur, dan HPP pembentuknya.'
                : 'Belum ada aktivitas penjualan atau retur pada periode yang dipilih.';
        } else {
            $title = $peakValue > 0 ? 'Momentum terbaik: '.$peakLabel : 'Belum ada penjualan';
            $copy = $peakValue > 0
                ? 'Periode tersebut menghasilkan nilai tertinggi pada grafik. Gunakan tabel untuk menelusuri kontributor utamanya.'
                : 'Ubah periode atau cabang untuk melihat aktivitas penjualan yang tersedia.';
        }

        return [
            'title' => $title,
            'copy' => $copy,
            'peak_value' => $peakValue,
            'peak_label' => $peakLabel,
            'net_after_returns' => $values['net_after_returns'],
            'return_ratio' => $values['revenue'] > 0 ? ($values['return_value'] / $values['revenue']) * 100 : 0,
            'average_ticket' => $values['average_ticket'],
        ];
    }

    private function normalizeRows(Collection $rows, array $columns): Collection
    {
        $numericKeys = collect($columns)
            ->whereIn('type', ['number', 'currency', 'signed_currency', 'percent', 'hour'])
            ->pluck('key')
            ->all();

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

    private function sales(array $branchIds, array $filters, string $status): Builder
    {
        return DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('sales.status', $status)
            ->whereBetween('sales.tanggal_transaksi', [
                $filters['start']->copy()->startOfDay(),
                $filters['end']->copy()->endOfDay(),
            ]);
    }

    private function cancelledSales(array $branchIds, array $filters): Builder
    {
        return DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('sales.status', 'cancelled')
            ->whereBetween(DB::raw('COALESCE(sales.cancelled_at, sales.tanggal_transaksi)'), [
                $filters['start']->copy()->startOfDay(),
                $filters['end']->copy()->endOfDay(),
            ]);
    }

    private function returns(array $branchIds, array $filters): Builder
    {
        return DB::table('retur_penjualan as returns')
            ->whereIn('returns.branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->where('returns.status', 'posted')
            ->whereBetween('returns.tanggal_retur', [
                $filters['start']->copy()->startOfDay(),
                $filters['end']->copy()->endOfDay(),
            ]);
    }

    private function detailTotals(): Builder
    {
        return DB::table('penjualan_transaction_details')
            ->select('penjualan_transaction_id')
            ->selectRaw('COUNT(id) as item_count')
            ->selectRaw('COALESCE(SUM(qty_jual), 0) as qty')
            ->selectRaw('COALESCE(SUM(subtotal_net), 0) as subtotal_net')
            ->groupBy('penjualan_transaction_id');
    }

    private function allocatedTransactionDiscountExpression(): string
    {
        return 'CASE WHEN COALESCE(detail_totals.subtotal_net, 0) > 0 '
            .'THEN COALESCE(sales.diskon_transaksi_nominal, 0) * ((1.0 * details.subtotal_net) / detail_totals.subtotal_net) '
            .'WHEN COALESCE(detail_totals.item_count, 0) > 0 '
            .'THEN COALESCE(sales.diskon_transaksi_nominal, 0) * (1.0 / detail_totals.item_count) ELSE 0 END';
    }

    private function transactionCosts(): Builder
    {
        return DB::table('penjualan_transaction_batches as sale_batches')
            ->join('penjualan_transaction_details as details', 'details.id', '=', 'sale_batches.penjualan_transaction_detail_id')
            ->select('details.penjualan_transaction_id')
            ->selectRaw('COALESCE(SUM(sale_batches.qty_stok * sale_batches.harga_beli), 0) as hpp')
            ->groupBy('details.penjualan_transaction_id');
    }

    private function returnDocumentCosts(): Builder
    {
        return DB::table('retur_penjualan_batches as return_batches')
            ->join('retur_penjualan_details as return_details', 'return_details.id', '=', 'return_batches.retur_penjualan_detail_id')
            ->join('penjualan_transaction_batches as sale_batches', 'sale_batches.id', '=', 'return_batches.penjualan_transaction_batch_id')
            ->select('return_details.retur_penjualan_id')
            ->selectRaw('COALESCE(SUM(return_batches.qty_stok * sale_batches.harga_beli), 0) as hpp')
            ->groupBy('return_details.retur_penjualan_id');
    }

    private function dateExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-%d', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m-%d')";
    }

    private function hourExpression(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }
}
