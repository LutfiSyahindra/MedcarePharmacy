<?php

namespace App\Http\Controllers\Medcare\Menu\Keuangan;

use App\Http\Controllers\Controller;
use App\Services\Menu\Keuangan\FinanceService;
use App\Support\SidebarPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function __construct(private readonly FinanceService $finance) {}

    public function index(Request $request): View
    {
        return $this->page($request, 'overview');
    }

    public function monthly(Request $request): View
    {
        return $this->page($request, 'monthly');
    }

    public function cashFlow(Request $request): View
    {
        return $this->page($request, 'cash-flow');
    }

    public function ledger(Request $request): View
    {
        return $this->page($request, 'ledger');
    }

    public function cashier(Request $request): View
    {
        return $this->page($request, 'cashier');
    }

    private function page(Request $request, string $section): View
    {
        $this->authorizeFinance($request);

        $pages = [
            'overview' => [
                'title' => 'Ringkasan Keuangan',
                'eyebrow' => 'Finance Control',
                'heading' => 'Kondisi keuangan apotek dalam sekali lihat.',
                'description' => 'Pantau indikator utama terlebih dahulu, lalu buka subfitur sesuai pekerjaan yang ingin dilakukan.',
                'icon' => 'mdi-view-dashboard-outline',
                'view' => 'medcare.menu.keuangan.sections.overview',
            ],
            'monthly' => [
                'title' => 'Akun Bulanan',
                'eyebrow' => 'Monthly Performance',
                'heading' => 'Omzet dan keuntungan per bulan.',
                'description' => 'Tinjau omzet, HPP, laba kotor, biaya operasional, dan laba bersih tanpa bercampur dengan jurnal harian.',
                'icon' => 'mdi-calendar-month-outline',
                'view' => 'medcare.menu.keuangan.sections.monthly',
            ],
            'cash-flow' => [
                'title' => 'Arus Kas',
                'eyebrow' => 'Cash Flow',
                'heading' => 'Pergerakan uang masuk dan keluar.',
                'description' => 'Analisis tren harian dan kanal pembayaran pada periode serta cabang yang dipilih.',
                'icon' => 'mdi-chart-timeline-variant',
                'view' => 'medcare.menu.keuangan.sections.cash-flow',
            ],
            'ledger' => [
                'title' => 'Buku Kas',
                'eyebrow' => 'Auditable Ledger',
                'heading' => 'Jurnal transaksi yang rapi dan mudah diaudit.',
                'description' => 'Telusuri transaksi dari POS, retur, mutasi kasir, dan jurnal manual dalam satu daftar terfokus.',
                'icon' => 'mdi-book-open-page-variant-outline',
                'view' => 'medcare.menu.keuangan.sections.ledger',
            ],
            'cashier' => [
                'title' => 'Kas Kasir',
                'eyebrow' => 'Live Cashier',
                'heading' => 'Pantau laci kasir yang sedang aktif.',
                'description' => 'Lihat saldo seharusnya dan penjualan tunai setiap shift tanpa distraksi laporan lainnya.',
                'icon' => 'mdi-cash-register',
                'view' => 'medcare.menu.keuangan.sections.cashier',
            ],
        ];

        return view('medcare.menu.keuangan.index', [
            'section' => $section,
            'page' => $pages[$section],
            'categories' => FinanceService::CATEGORIES,
            'paymentMethods' => FinanceService::PAYMENT_METHODS,
            'sources' => FinanceService::SOURCES,
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorizeFinance($request);

        return response()->json([
            'status' => 'success',
            'finance' => $this->finance->dashboard($request->user(), $this->filters($request)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeFinance($request);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer'],
            'type' => ['required', Rule::in(array_keys(FinanceService::TYPES))],
            'category' => ['required', 'string', Rule::in(
                collect(FinanceService::CATEGORIES)->flatMap(fn (array $categories) => array_keys($categories))->all()
            )],
            'payment_method' => ['required', Rule::in(array_keys(FinanceService::PAYMENT_METHODS))],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'occurred_at' => ['required', 'date'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $transaction = $this->finance->store($request->user(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => $transaction->payment_method === 'tunai'
                ? 'Transaksi tersimpan dan kas shift aktif telah diperbarui.'
                : 'Transaksi keuangan berhasil disimpan.',
            'transaction_number' => $transaction->number,
        ], 201);
    }

    public function void(Request $request, int $transaction): JsonResponse
    {
        $this->authorizeFinance($request);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $record = $this->finance->void($request->user(), $transaction, $validated['reason']);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi '.$record->number.' berhasil dibatalkan dengan jejak audit.',
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
            'monthly_period' => ['nullable', 'date_format:Y-m'],
            'source' => ['nullable', Rule::in(array_keys(FinanceService::SOURCES))],
            'type' => ['nullable', Rule::in(array_keys(FinanceService::TYPES))],
            'payment_method' => ['nullable', Rule::in(array_keys(FinanceService::PAYMENT_METHODS))],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $end = isset($validated['date_end'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_end'])->endOfDay()
            : today()->endOfDay();
        $start = isset($validated['date_start'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_start'])->startOfDay()
            : $end->copy()->subDays(29)->startOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages(['date_end' => 'Tanggal akhir harus setelah tanggal mulai.']);
        }
        if ($start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['date_end' => 'Rentang buku kas maksimal 366 hari.']);
        }

        $monthlyPeriod = isset($validated['monthly_period'])
            ? Carbon::createFromFormat('Y-m-d', $validated['monthly_period'].'-01')->startOfMonth()
            : null;

        if ($monthlyPeriod?->gt(today()->startOfMonth())) {
            throw ValidationException::withMessages([
                'monthly_period' => 'Bulan laporan tidak boleh melewati bulan berjalan.',
            ]);
        }

        if ($monthlyPeriod !== null) {
            $monthlyPeriod = $monthlyPeriod->isSameMonth(today())
                ? today()->endOfDay()
                : $monthlyPeriod->endOfMonth();
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'start' => $start,
            'end' => $end,
            'monthly_period' => $monthlyPeriod,
            'source' => (string) ($validated['source'] ?? ''),
            'type' => (string) ($validated['type'] ?? ''),
            'payment_method' => (string) ($validated['payment_method'] ?? ''),
            'search' => trim((string) ($validated['search'] ?? '')),
        ];
    }

    private function authorizeFinance(Request $request): void
    {
        abort_unless($request->user()?->can(SidebarPermissions::KEUANGAN), 403);
    }
}
