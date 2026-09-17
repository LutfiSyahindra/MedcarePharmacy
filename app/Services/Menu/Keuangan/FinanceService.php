<?php

namespace App\Services\Menu\Keuangan;

use App\Models\BranchModel;
use App\Models\Menu\Keuangan\FinanceAccountModel;
use App\Models\Menu\Keuangan\FinanceCategoryModel;
use App\Models\Menu\Keuangan\FinanceTransactionModel;
use App\Models\Menu\Penjualan\CashierCashMovementModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Penjualan\ReturPenjualanModel;
use App\Models\User;
use App\Services\Menu\AnalisisProfitabilitas\ProfitabilityMetricsService;
use App\Services\Menu\Penjualan\CashierShiftService;
use App\Services\Menu\Penjualan\PenjualanPosService;
use App\Support\BranchAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FinanceService
{
    private const SYSTEM_MONTHLY_ACCOUNTS = [
        'revenue' => [
            'code' => 'SYS-OMZET-BULANAN',
            'name' => 'Akun Omzet Bulanan',
        ],
        'profit' => [
            'code' => 'SYS-KEUNTUNGAN-BULANAN',
            'name' => 'Akun Keuntungan Bulanan',
        ],
        'net_profit' => [
            'code' => 'SYS-LABA-BERSIH-BULANAN',
            'name' => 'Akun Laba Bersih Bulanan',
        ],
    ];

    public const TYPES = [
        'income' => 'Pendapatan',
        'expense' => 'Pengeluaran',
    ];

    public const CATEGORIES = [
        'income' => [
            'pendapatan_lain' => 'Pendapatan Lain',
            'pelunasan_piutang' => 'Pelunasan Piutang',
            'refund_supplier' => 'Pengembalian Dana Supplier',
            'modal_tambahan' => 'Tambahan Modal',
        ],
        'expense' => [
            'operasional' => 'Biaya Operasional',
            'pembelian_stok' => 'Pembelian Stok',
            'gaji' => 'Gaji & Honor',
            'utilitas' => 'Utilitas',
            'pajak' => 'Pajak & Administrasi',
            'pemeliharaan' => 'Pemeliharaan',
            'pengeluaran_lain' => 'Pengeluaran Lain',
        ],
    ];

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'debit' => 'Kartu Debit',
        'credit_card' => 'Kartu Kredit',
        'ewallet' => 'E-Wallet',
        'piutang' => 'Piutang',
        'instansi' => 'Instansi',
        'potong_piutang' => 'Potong Piutang',
        'lainnya' => 'Lainnya',
    ];

    public const SOURCES = [
        'pos' => 'Penjualan POS',
        'return' => 'Retur Penjualan',
        'cashier' => 'Mutasi Kasir',
        'manual' => 'Jurnal Manual',
        'system' => 'Jurnal Sistem',
    ];

    public function __construct(
        private readonly CashierShiftService $cashierShiftService,
        private readonly ProfitabilityMetricsService $profitabilityMetrics,
    ) {}

    public function dashboard(User $user, array $filters): array
    {
        $context = $this->context($user, $filters['branch_id'] ?? null);
        $rows = collect()
            ->concat($this->saleRows($context['branch_ids'], $filters['start'], $filters['end']))
            ->concat($this->returnRows($context['branch_ids'], $filters['start'], $filters['end']))
            ->concat($this->cashierRows($context['branch_ids'], $filters['start'], $filters['end']))
            ->concat($this->manualRows($context['branch_ids'], $filters['start'], $filters['end']));

        $rows = $this->applyFilters($rows, $filters);
        $effectiveRows = $rows->where('status', 'posted');
        $summary = $this->summary($effectiveRows);
        $totalRows = $rows->count();
        $rows = $rows
            ->sortByDesc(fn (array $row) => $row['occurred_at']->getTimestamp().str_pad((string) $row['sort_id'], 12, '0', STR_PAD_LEFT))
            ->take(500)
            ->map(fn (array $row) => $this->serializeRow($row))
            ->values();

        return [
            'meta' => [
                'branches' => $context['branches']->map(fn (BranchModel $branch) => [
                    'id' => (int) $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                ])->values()->all(),
                'selected_branch_id' => $context['selected_branch_id'],
                'branch_label' => $context['branch_label'],
                'date_start' => $filters['start']->toDateString(),
                'date_end' => $filters['end']->toDateString(),
                'total_rows' => $totalRows,
                'displayed_rows' => $rows->count(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ],
            'summary' => $summary,
            'trend' => $this->trend($effectiveRows, $filters['start'], $filters['end']),
            'payment_methods' => $this->paymentSummary($effectiveRows),
            'source_summary' => $this->sourceSummary($effectiveRows),
            'monthly_accounts' => $this->monthlyAccounts(
                $context['branch_ids'],
                $filters['monthly_period'] ?? $filters['end'],
                ($filters['monthly_period'] ?? null) !== null,
            ),
            'open_drawers' => $this->openDrawers($context['branch_ids']),
            'rows' => $rows->all(),
        ];
    }

    private function monthlyAccounts(array $branchIds, Carbon $end, bool $filterApplied = false): array
    {
        $accounts = $this->ensureMonthlyAccounts($branchIds);
        $historyStart = $end->copy()->startOfMonth()->subMonths(11);
        $historyEnd = $end->copy()->endOfDay();
        $monthlyRows = $this->profitabilityMetrics
            ->dailyQuery($branchIds, $historyStart, $historyEnd)
            ->orderBy('sale_date')
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->sale_date)->format('Y-m'));
        $monthlyOperatingExpenses = $this->monthlyOperatingExpenses($branchIds, $historyStart, $historyEnd);
        $records = collect(range(0, 11))->map(function (int $offset) use ($historyStart, $end, $monthlyRows, $monthlyOperatingExpenses) {
            $month = $historyStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $rows = $monthlyRows->get($key, collect());
            $netRevenue = round((float) $rows->sum('net_sales'), 2);
            $grossProfit = round((float) $rows->sum('gross_profit'), 2);
            $operatingExpenses = round((float) $monthlyOperatingExpenses->get($key, 0), 2);
            $netProfit = round($grossProfit - $operatingExpenses, 2);

            return [
                'period' => $key,
                'period_label' => $this->indonesianMonthLabel($month),
                'gross_revenue' => round((float) $rows->sum('gross_sales'), 2),
                'returns' => round((float) $rows->sum('returns_value'), 2),
                'net_revenue' => $netRevenue,
                'net_hpp' => round((float) $rows->sum('net_hpp'), 2),
                'gross_profit' => $grossProfit,
                'gross_margin' => $netRevenue != 0.0 ? round(($grossProfit / $netRevenue) * 100, 2) : null,
                'operating_expenses' => $operatingExpenses,
                'net_profit' => $netProfit,
                'net_margin' => $netRevenue != 0.0 ? round(($netProfit / $netRevenue) * 100, 2) : null,
                'is_current' => $key === $end->format('Y-m'),
                'is_partial' => $key === $end->format('Y-m') && $end->day < $end->daysInMonth,
            ];
        })->values();
        $current = $records->last();
        $previous = $records->slice(-2, 1)->first();

        return [
            'period' => $current['period'],
            'period_label' => $current['period_label'],
            'is_partial' => $current['is_partial'],
            'filter_applied' => $filterApplied,
            'accounts' => [
                $this->monthlyAccountPayload(
                    'revenue',
                    self::SYSTEM_MONTHLY_ACCOUNTS['revenue'],
                    $accounts,
                    (float) $current['net_revenue'],
                    (float) $previous['net_revenue'],
                    'Omzet bersih setelah retur',
                ),
                $this->monthlyAccountPayload(
                    'profit',
                    self::SYSTEM_MONTHLY_ACCOUNTS['profit'],
                    $accounts,
                    (float) $current['gross_profit'],
                    (float) $previous['gross_profit'],
                    'Omzet bersih dikurangi HPP neto',
                ),
                $this->monthlyAccountPayload(
                    'net_profit',
                    self::SYSTEM_MONTHLY_ACCOUNTS['net_profit'],
                    $accounts,
                    (float) $current['net_profit'],
                    (float) $previous['net_profit'],
                    'Laba kotor dikurangi biaya operasional',
                ),
            ],
            'records' => $filterApplied
                ? [$current]
                : $records->reverse()->values()->all(),
            'basis' => 'Omzet = penjualan selesai - retur terposting. Laba kotor = omzet bersih - HPP neto. Laba bersih = laba kotor - biaya operasional; pembelian stok tidak dikurangkan lagi karena sudah diperhitungkan melalui HPP.',
        ];
    }

    private function monthlyOperatingExpenses(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        return FinanceTransactionModel::query()
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->whereBetween('transaction_date', [$start, $end])
            ->where('status', 'posted')
            ->whereIn('type', ['expense', 'supplier_payment', 'cashier_cash_out'])
            ->whereHas('category', fn ($query) => $query
                ->where('is_operational', true)
                ->where('code', '!=', 'MANUAL-PEMBELIAN-STOK'))
            ->with('branch:id,operational_timezone')
            ->get()
            ->groupBy(fn (FinanceTransactionModel $transaction) => $transaction->transaction_date
                ->copy()
                ->setTimezone($transaction->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE)
                ->format('Y-m'))
            ->map(fn (Collection $rows) => round((float) $rows->sum('amount'), 2));
    }

    private function ensureMonthlyAccounts(array $branchIds): Collection
    {
        foreach ($branchIds as $branchId) {
            foreach (self::SYSTEM_MONTHLY_ACCOUNTS as $definition) {
                FinanceAccountModel::firstOrCreate([
                    'branch_id' => $branchId,
                    'code' => $definition['code'],
                ], [
                    'name' => $definition['name'],
                    'type' => 'reporting',
                    'opening_balance' => 0,
                    'is_default' => false,
                    'is_active' => true,
                ]);
            }
        }

        return FinanceAccountModel::query()
            ->whereIn('branch_id', $branchIds === [] ? [-1] : $branchIds)
            ->whereIn('code', collect(self::SYSTEM_MONTHLY_ACCOUNTS)->pluck('code')->all())
            ->where('is_active', true)
            ->get();
    }

    private function monthlyAccountPayload(
        string $key,
        array $definition,
        Collection $accounts,
        float $amount,
        float $previousAmount,
        string $description,
    ): array {
        $change = $previousAmount != 0.0
            ? round((($amount - $previousAmount) / abs($previousAmount)) * 100, 2)
            : null;

        return [
            'key' => $key,
            'account_ids' => $accounts->where('code', $definition['code'])->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'code' => $definition['code'],
            'name' => $definition['name'],
            'description' => $description,
            'amount' => round($amount, 2),
            'previous_amount' => round($previousAmount, 2),
            'change_percent' => $change,
            'trend' => $amount > $previousAmount ? 'up' : ($amount < $previousAmount ? 'down' : 'flat'),
        ];
    }

    private function indonesianMonthLabel(Carbon $month): string
    {
        $names = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $names[$month->month].' '.$month->year;
    }

    public function store(User $user, array $payload): FinanceTransactionModel
    {
        $branch = $this->accessibleBranch($user, (int) $payload['branch_id'], true);
        $type = (string) $payload['type'];
        $category = (string) $payload['category'];
        $paymentMethod = (string) $payload['payment_method'];

        if (! isset(self::CATEGORIES[$type][$category])) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak sesuai dengan jenis transaksi.',
            ]);
        }

        return DB::transaction(function () use ($user, $payload, $branch, $type, $category, $paymentMethod) {
            $occurredAt = $paymentMethod === 'tunai'
                ? now()
                : Carbon::parse($payload['occurred_at'], $branch->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE)->utc();
            $categoryModel = $this->manualCategory($branch, $type, $category);

            $transaction = FinanceTransactionModel::create([
                'branch_id' => $branch->id,
                'category_id' => $categoryModel->id,
                'number' => $this->nextNumber($branch, $occurredAt),
                'type' => $type,
                'payment_method' => $paymentMethod,
                'amount' => round((float) $payload['amount'], 2),
                'reference_no' => $this->nullableText($payload['reference_no'] ?? null),
                'description' => trim((string) $payload['description']),
                'status' => 'posted',
                'transaction_date' => $occurredAt,
                'source_type' => 'manual',
                'source_key' => 'main',
                'metadata' => ['category_key' => $category],
                'created_by' => $user->id,
                'posted_at' => now(),
            ]);

            if ($paymentMethod === 'tunai') {
                $this->cashierShiftService->addMovement(
                    (int) $branch->id,
                    $type === 'income' ? 'cash_in' : 'cash_out',
                    (float) $transaction->amount,
                    $transaction->number.' · '.$transaction->description,
                    $user,
                    (int) $transaction->id,
                );
            }

            return $transaction->fresh(['branch', 'category', 'createdBy', 'cashierMovements.shift']);
        });
    }

    public function void(User $user, int $transactionId, string $reason): FinanceTransactionModel
    {
        return DB::transaction(function () use ($user, $transactionId, $reason) {
            $transaction = FinanceTransactionModel::query()
                ->whereKey($transactionId)
                ->whereIn('branch_id', BranchAccess::userBranchIds($user))
                ->where('source_type', 'manual')
                ->with('cashierMovements.shift')
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->status !== 'posted') {
                throw ValidationException::withMessages(['status' => 'Transaksi ini sudah dibatalkan.']);
            }

            if ($transaction->payment_method === 'tunai') {
                $originalMovement = $transaction->cashierMovements->sortBy('id')->first();
                $shift = $originalMovement?->shift;

                if (! $shift || $shift->status !== 'open' || (int) $shift->user_id !== (int) $user->id) {
                    throw ValidationException::withMessages([
                        'status' => 'Transaksi tunai hanya dapat dibatalkan saat shift asal masih aktif oleh kasir yang sama. Gunakan jurnal koreksi untuk shift yang sudah ditutup.',
                    ]);
                }

                $this->cashierShiftService->addMovement(
                    (int) $transaction->branch_id,
                    $transaction->type === 'income' ? 'cash_out' : 'cash_in',
                    (float) $transaction->amount,
                    'Pembalik '.$transaction->number.' · '.trim($reason),
                    $user,
                    (int) $transaction->id,
                );
            }

            $transaction->forceFill([
                'status' => 'voided',
                'voided_by' => $user->id,
                'voided_at' => now(),
                'void_reason' => trim($reason),
            ])->save();

            return $transaction->fresh(['branch', 'category', 'createdBy', 'voidedBy']);
        });
    }

    private function saleRows(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        return PenjualanTransactionModel::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$start, $end])
            ->with(['branch:id,code,name,operational_timezone', 'completedBy:id,name', 'payments'])
            ->get()
            ->flatMap(function (PenjualanTransactionModel $transaction) {
                $remainingChange = (float) $transaction->kembalian;

                return $transaction->payments->map(function ($payment) use ($transaction, &$remainingChange) {
                    $amount = (float) $payment->amount;
                    if ($payment->metode === 'tunai' && $remainingChange > 0) {
                        $deduction = min($amount, $remainingChange);
                        $amount -= $deduction;
                        $remainingChange -= $deduction;
                    }

                    return $this->row([
                        'id' => 'pos-'.$payment->id,
                        'sort_id' => $payment->id,
                        'source' => 'pos',
                        'source_label' => self::SOURCES['pos'],
                        'reference' => $transaction->nomor_transaksi,
                        'branch_id' => (int) $transaction->branch_id,
                        'branch_name' => $transaction->branch?->name ?? '-',
                        'branch_timezone' => $transaction->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE,
                        'occurred_at' => $payment->paid_at ?: $transaction->completed_at ?: $transaction->tanggal_transaksi,
                        'type' => 'income',
                        'category' => 'penjualan_pos',
                        'category_label' => 'Penjualan POS',
                        'payment_method' => $payment->metode,
                        'amount' => max(0, $amount),
                        'description' => 'Penjualan'.($transaction->customer_name ? ' · '.$transaction->customer_name : ''),
                        'created_by' => $transaction->completedBy?->name ?? '-',
                    ]);
                })->filter(fn (array $row) => $row['amount'] > 0);
            })->values();
    }

    private function returnRows(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        return ReturPenjualanModel::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'posted')
            ->whereBetween('posted_at', [$start, $end])
            ->with(['branch:id,code,name,operational_timezone', 'createdBy:id,name', 'transaction:id,nomor_transaksi'])
            ->get()
            ->map(fn (ReturPenjualanModel $return) => $this->row([
                'id' => 'return-'.$return->id,
                'sort_id' => $return->id,
                'source' => 'return',
                'source_label' => self::SOURCES['return'],
                'reference' => $return->nomor_retur,
                'branch_id' => (int) $return->branch_id,
                'branch_name' => $return->branch?->name ?? '-',
                'branch_timezone' => $return->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE,
                'occurred_at' => $return->posted_at,
                'type' => 'expense',
                'category' => 'retur_penjualan',
                'category_label' => 'Refund Retur Penjualan',
                'payment_method' => $return->refund_method ?: 'lainnya',
                'amount' => (float) $return->grand_total,
                'description' => 'Retur dari '.($return->transaction?->nomor_transaksi ?? 'transaksi penjualan'),
                'created_by' => $return->createdBy?->name ?? '-',
            ]))->values();
    }

    private function cashierRows(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        return CashierCashMovementModel::query()
            ->whereNull('finance_transaction_id')
            ->whereBetween('occurred_at', [$start, $end])
            ->where('description', 'not like', 'Pengembalian pembatalan %')
            ->whereHas('shift', fn ($query) => $query->whereIn('branch_id', $branchIds))
            ->with(['shift.branch:id,code,name,operational_timezone', 'shift.user:id,name', 'createdBy:id,name'])
            ->get()
            ->map(fn (CashierCashMovementModel $movement) => $this->row([
                'id' => 'cashier-'.$movement->id,
                'sort_id' => $movement->id,
                'source' => 'cashier',
                'source_label' => self::SOURCES['cashier'],
                'reference' => $movement->shift?->shift_number ?? '-',
                'branch_id' => (int) ($movement->shift?->branch_id ?? 0),
                'branch_name' => $movement->shift?->branch?->name ?? '-',
                'branch_timezone' => $movement->shift?->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE,
                'occurred_at' => $movement->occurred_at,
                'type' => $movement->type === 'cash_in' ? 'income' : 'expense',
                'category' => $movement->type,
                'category_label' => $movement->type === 'cash_in' ? 'Kas Masuk Kasir' : 'Kas Keluar Kasir',
                'payment_method' => 'tunai',
                'amount' => (float) $movement->amount,
                'description' => $movement->description,
                'created_by' => $movement->createdBy?->name ?? ($movement->shift?->user?->name ?? '-'),
            ]))->values();
    }

    private function manualRows(array $branchIds, Carbon $start, Carbon $end): Collection
    {
        return FinanceTransactionModel::query()
            ->whereIn('branch_id', $branchIds)
            ->whereBetween('transaction_date', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('source_type')
                    ->orWhereNotIn('source_type', ['sale', 'cashier_movement', 'sale_return', 'sales_return']);
            })
            ->with(['branch:id,code,name,operational_timezone', 'category:id,code,name,type', 'createdBy:id,name'])
            ->get()
            ->map(function (FinanceTransactionModel $transaction) {
                $source = $transaction->source_type === 'manual' ? 'manual' : 'system';
                $type = $this->directionForFinanceType((string) $transaction->type, $transaction->category?->type);

                return $this->row([
                    'id' => 'manual-'.$transaction->id,
                    'sort_id' => $transaction->id,
                    'record_id' => (int) $transaction->id,
                    'source' => $source,
                    'source_label' => self::SOURCES[$source],
                    'reference' => $transaction->number,
                    'branch_id' => (int) $transaction->branch_id,
                    'branch_name' => $transaction->branch?->name ?? '-',
                    'branch_timezone' => $transaction->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE,
                    'occurred_at' => $transaction->transaction_date,
                    'type' => $type,
                    'category' => $transaction->category?->code ?: (string) $transaction->type,
                    'category_label' => $transaction->category?->name ?: Str::headline($transaction->type),
                    'payment_method' => $transaction->payment_method ?: 'lainnya',
                    'amount' => (float) $transaction->amount,
                    'description' => $transaction->description,
                    'external_reference' => $transaction->reference_no,
                    'created_by' => $transaction->createdBy?->name ?? '-',
                    'status' => $transaction->status,
                    'void_reason' => $transaction->void_reason,
                ]);
            })->values();
    }

    private function row(array $row): array
    {
        return [
            'record_id' => null,
            'external_reference' => null,
            'status' => 'posted',
            'void_reason' => null,
            ...$row,
        ];
    }

    private function serializeRow(array $row): array
    {
        $date = ($row['occurred_at'] instanceof Carbon ? $row['occurred_at']->copy() : Carbon::parse($row['occurred_at']))
            ->setTimezone($row['branch_timezone'] ?? CashierShiftService::DEFAULT_TIMEZONE);

        return [
            ...collect($row)->except('branch_timezone')->all(),
            'occurred_at' => $date->format('Y-m-d H:i:s'),
            'occurred_at_label' => $date->format('d/m/Y · H:i'),
            'type_label' => self::TYPES[$row['type']] ?? Str::headline($row['type']),
            'payment_method_label' => self::PAYMENT_METHODS[$row['payment_method']]
                ?? PenjualanPosService::PAYMENT_METHODS[$row['payment_method']]
                ?? Str::headline($row['payment_method']),
            'can_void' => $row['source'] === 'manual' && $row['status'] === 'posted',
        ];
    }

    private function applyFilters(Collection $rows, array $filters): Collection
    {
        if (! empty($filters['source'])) {
            $rows = $rows->where('source', $filters['source']);
        }
        if (! empty($filters['type'])) {
            $rows = $rows->where('type', $filters['type']);
        }
        if (! empty($filters['payment_method'])) {
            $rows = $rows->where('payment_method', $filters['payment_method']);
        }
        if (($filters['search'] ?? '') !== '') {
            $search = mb_strtolower($filters['search']);
            $rows = $rows->filter(fn (array $row) => str_contains(mb_strtolower(implode(' ', [
                $row['reference'], $row['external_reference'], $row['branch_name'], $row['category_label'],
                $row['description'], $row['created_by'],
            ])), $search));
        }

        return $rows->values();
    }

    private function summary(Collection $rows): array
    {
        $income = round((float) $rows->where('type', 'income')->sum('amount'), 2);
        $expense = round((float) $rows->where('type', 'expense')->sum('amount'), 2);
        $cashRows = $rows->where('payment_method', 'tunai');
        $cashIncome = round((float) $cashRows->where('type', 'income')->sum('amount'), 2);
        $cashExpense = round((float) $cashRows->where('type', 'expense')->sum('amount'), 2);

        return [
            'income' => $income,
            'expense' => $expense,
            'net_cash_flow' => round($income - $expense, 2),
            'cash_income' => $cashIncome,
            'cash_expense' => $cashExpense,
            'net_cash' => round($cashIncome - $cashExpense, 2),
            'pos_income' => round((float) $rows->where('source', 'pos')->sum('amount'), 2),
            'transaction_count' => $rows->count(),
        ];
    }

    private function trend(Collection $rows, Carbon $start, Carbon $end): array
    {
        $daily = $rows->groupBy(fn (array $row) => $row['occurred_at']->copy()
            ->setTimezone($row['branch_timezone'] ?? CashierShiftService::DEFAULT_TIMEZONE)
            ->toDateString());
        $labels = [];
        $income = [];
        $expense = [];

        for ($date = $start->copy()->startOfDay(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $day = $daily->get($key, collect());
            $labels[] = $date->format('d M');
            $income[] = round((float) $day->where('type', 'income')->sum('amount'), 2);
            $expense[] = round((float) $day->where('type', 'expense')->sum('amount'), 2);
        }

        return compact('labels', 'income', 'expense');
    }

    private function paymentSummary(Collection $rows): array
    {
        return $rows->groupBy('payment_method')
            ->map(function (Collection $group, string $method) {
                $income = round((float) $group->where('type', 'income')->sum('amount'), 2);
                $expense = round((float) $group->where('type', 'expense')->sum('amount'), 2);

                return [
                    'key' => $method,
                    'label' => self::PAYMENT_METHODS[$method] ?? PenjualanPosService::PAYMENT_METHODS[$method] ?? Str::headline($method),
                    'income' => $income,
                    'expense' => $expense,
                    'net' => round($income - $expense, 2),
                    'transactions' => $group->count(),
                ];
            })->sortByDesc('income')->values()->all();
    }

    private function sourceSummary(Collection $rows): array
    {
        return collect(self::SOURCES)->map(function (string $label, string $source) use ($rows) {
            $group = $rows->where('source', $source);

            return [
                'key' => $source,
                'label' => $label,
                'income' => round((float) $group->where('type', 'income')->sum('amount'), 2),
                'expense' => round((float) $group->where('type', 'expense')->sum('amount'), 2),
                'transactions' => $group->count(),
            ];
        })->values()->all();
    }

    private function openDrawers(array $branchIds): array
    {
        $shifts = CashierShiftModel::query()
            ->whereIn('branch_id', $branchIds)
            ->where('status', 'open')
            ->with(['branch:id,code,name,operational_timezone', 'user:id,name'])
            ->orderBy('opened_at')
            ->get();

        return [
            'count' => $shifts->count(),
            'expected_cash' => round((float) $shifts->sum(fn (CashierShiftModel $shift) => $this->cashierShiftService->summary($shift)['expected_cash']), 2),
            'rows' => $shifts->map(function (CashierShiftModel $shift) {
                $summary = $this->cashierShiftService->summary($shift);

                return [
                    'id' => (int) $shift->id,
                    'shift_number' => $shift->shift_number,
                    'branch_name' => $shift->branch?->name ?? '-',
                    'cashier_name' => $shift->user?->name ?? '-',
                    'opened_at' => $shift->opened_at?->copy()
                        ->setTimezone($shift->branch?->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE)
                        ->format('d/m/Y · H:i'),
                    'expected_cash' => $summary['expected_cash'],
                    'cash_sales' => $summary['cash_sales'],
                ];
            })->values()->all(),
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
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak tersedia untuk akun ini.']);
        }

        return [
            'branches' => $branches,
            'branch_ids' => $selectedBranchId ? [$selectedBranchId] : $allowedIds,
            'selected_branch_id' => $selectedBranchId,
            'branch_label' => $selectedBranchId
                ? ($branches->firstWhere('id', $selectedBranchId)?->name ?? 'Cabang')
                : ($branches->count() > 1 ? 'Semua cabang' : ($branches->first()?->name ?? 'Belum ada cabang')),
        ];
    }

    private function accessibleBranch(User $user, int $branchId, bool $activeOnly = false): BranchModel
    {
        $branch = BranchModel::query()
            ->whereKey($branchId)
            ->whereIn('id', BranchAccess::userBranchIds($user))
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages(['branch_id' => 'Cabang tidak aktif atau tidak dapat diakses.']);
        }

        return $branch;
    }

    private function nextNumber(BranchModel $branch, Carbon $occurredAt): string
    {
        $date = $occurredAt->copy()->setTimezone($branch->operational_timezone ?: CashierShiftService::DEFAULT_TIMEZONE)->format('Ymd');
        $branchCode = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $branch->code ?: 'BR'.$branch->id));
        $prefix = 'KU-'.$date.'-'.$branchCode.'-';

        do {
            $number = $prefix.Str::upper(Str::random(6));
        } while (FinanceTransactionModel::query()->where('number', $number)->exists());

        return $number;
    }

    private function manualCategory(BranchModel $branch, string $type, string $category): FinanceCategoryModel
    {
        $code = 'MANUAL-'.Str::upper(Str::slug($category));

        $isOperational = $type === 'expense' && $category !== 'pembelian_stok';

        return FinanceCategoryModel::updateOrCreate([
            'branch_id' => $branch->id,
            'code' => $code,
        ], [
            'name' => self::CATEGORIES[$type][$category],
            'type' => $type,
            'group' => $type === 'income' ? 'Pendapatan manual' : ($isOperational ? 'Operasional' : 'Persediaan'),
            'is_operational' => $isOperational,
            'is_system' => false,
            'is_active' => true,
        ]);
    }

    private function directionForFinanceType(string $type, ?string $categoryType): string
    {
        if (in_array($type, ['income', 'sale', 'purchase_return', 'cashier_cash_in'], true)) {
            return 'income';
        }
        if (in_array($type, ['expense', 'supplier_payment', 'sale_return', 'cashier_cash_out'], true)) {
            return 'expense';
        }

        return $categoryType === 'income' ? 'income' : 'expense';
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
