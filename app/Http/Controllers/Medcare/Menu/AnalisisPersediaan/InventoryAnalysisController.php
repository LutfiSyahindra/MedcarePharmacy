<?php

namespace App\Http\Controllers\Medcare\Menu\AnalisisPersediaan;

use App\Http\Controllers\Controller;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Services\Menu\AnalisisPersediaan\InventoryAnalysisService;
use App\Services\Menu\PembelianPenerimaan\PembelianService;
use App\Services\Notifikasi\TransactionNotificationService;
use App\Support\BranchAccess;
use App\Support\TieredDiscount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryAnalysisController extends Controller
{
    public function __construct(
        private readonly InventoryAnalysisService $analysis,
        private readonly PembelianService $purchases,
        private readonly TransactionNotificationService $transactionNotifications,
    ) {}

    public function index(string $analysis): View
    {
        return view('medcare.menu.analisisPersediaan.index', [
            'analysisType' => $analysis,
            'analysisDefinition' => $this->analysis->definition($analysis),
            'analysisTypes' => InventoryAnalysisService::TYPES,
            'distributors' => $analysis === 'saran-pembelian'
                ? DistributorModel::query()
                    ->where('is_active', true)
                    ->orderBy('nama')
                    ->get(['id', 'kode', 'nama'])
                : collect(),
        ]);
    }

    public function data(Request $request, string $analysis): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'analysis' => $this->analysis->build($request->user(), $analysis, $this->filters($request)),
        ]);
    }

    public function createPurchaseOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'medicine_ids' => ['required', 'array', 'min:1'],
            'medicine_ids.*' => ['required', 'integer', 'distinct', 'exists:master_obats,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'integer', 'distinct'],
            'items.*.distributor_id' => [
                'required',
                'integer',
                Rule::exists('distributors', 'id')->where('is_active', true),
            ],
            'items.*.harga_estimasi' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'items.*.diskon_1' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.diskon_2' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.diskon_3' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'items.required' => 'Rincian purchase order belum tersedia.',
            'items.*.distributor_id.required' => 'Pilih distributor untuk setiap produk.',
            'items.*.distributor_id.exists' => 'Distributor yang dipilih tidak tersedia atau tidak aktif.',
            'items.*.harga_estimasi.required' => 'Isi harga estimasi untuk setiap produk.',
            'items.*.harga_estimasi.numeric' => 'Harga estimasi harus berupa angka.',
            'items.*.harga_estimasi.min' => 'Harga estimasi tidak boleh negatif.',
        ]);

        $user = $request->user();
        $allowedBranchIds = BranchAccess::userBranchIds($user);
        $branchId = isset($validated['branch_id']) ? (int) $validated['branch_id'] : null;

        if ($branchId !== null && ! in_array($branchId, $allowedBranchIds, true)) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang tidak tersedia untuk akun ini.',
            ]);
        }

        if ($branchId === null) {
            if (count($allowedBranchIds) !== 1) {
                throw ValidationException::withMessages([
                    'branch_id' => empty($allowedBranchIds)
                        ? 'User belum ditugaskan ke cabang.'
                        : 'Pilih satu cabang sebelum membuat purchase order.',
                ]);
            }

            $branchId = $allowedBranchIds[0];
        }

        $filters = $this->filters($request);
        $filters['branch_id'] = $branchId;
        $medicineIds = collect($validated['medicine_ids'])
            ->map(fn ($medicineId) => (int) $medicineId)
            ->unique()
            ->values();
        $purchaseItems = collect($validated['items'])
            ->mapWithKeys(fn (array $item) => [(int) $item['medicine_id'] => $item]);

        if ($purchaseItems->keys()->sort()->values()->all() !== $medicineIds->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'items' => 'Rincian PO harus sesuai dengan seluruh produk yang dipilih.',
            ]);
        }

        $result = DB::transaction(function () use ($user, $branchId, $filters, $medicineIds, $purchaseItems) {
            // Serialisasi pembuatan dari saran yang sama agar klik berulang menghitung PO berjalan terbaru.
            MasterObatModel::query()
                ->whereIn('id', $medicineIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            $freshAnalysis = $this->analysis->build($user, 'saran-pembelian', $filters);
            $suggestions = collect($freshAnalysis['rows'])
                ->whereIn('id', $medicineIds->all())
                ->values();
            $skippedCount = $medicineIds->count() - $suggestions->count();

            if ($suggestions->isEmpty()) {
                throw ValidationException::withMessages([
                    'medicine_ids' => 'Produk terpilih tidak lagi membutuhkan pembelian setelah PO berjalan dihitung ulang.',
                ]);
            }

            $configurationIssues = $suggestions
                ->reject(fn (array $row) => (bool) ($row['can_create_po'] ?? false));

            if ($configurationIssues->isNotEmpty()) {
                $items = $configurationIssues
                    ->take(5)
                    ->map(fn (array $row) => $row['name'].': '.($row['po_issue'] ?? 'Konfigurasi pembelian belum lengkap.'))
                    ->implode(' ');

                throw ValidationException::withMessages([
                    'medicine_ids' => 'PO belum dapat dibuat. '.$items,
                ]);
            }

            $period = Carbon::parse($filters['start'])->format('d-m-Y')
                .' s.d. '.Carbon::parse($filters['end'])->format('d-m-Y');
            $orders = collect();
            $suggestionsByDistributor = $suggestions->groupBy(
                fn (array $row) => (int) $purchaseItems->get((int) $row['id'])['distributor_id']
            );

            foreach ($suggestionsByDistributor as $distributorId => $distributorSuggestions) {
                $details = $distributorSuggestions->map(function (array $row) use ($purchaseItems) {
                    $conversionFactor = max(1, (int) $row['purchase_conversion_factor']);
                    $purchaseQty = (float) ceil((float) $row['suggested_qty'] / $conversionFactor);
                    $item = $purchaseItems->get((int) $row['id'], []);
                    $purchasePrice = round((float) $item['harga_estimasi'], 2);
                    [$discount1, $discount2, $discount3] = TieredDiscount::percentages(
                        $item['diskon_1'] ?? 0,
                        $item['diskon_2'] ?? 0,
                        $item['diskon_3'] ?? 0,
                    );

                    return [
                        'obat_id' => (int) $row['id'],
                        'qty' => $purchaseQty,
                        'harga_estimasi' => $purchasePrice,
                        'diskon_1' => $discount1,
                        'diskon_2' => $discount2,
                        'diskon_3' => $discount3,
                        'subtotal' => TieredDiscount::netAmount(
                            $purchaseQty * $purchasePrice,
                            $discount1,
                            $discount2,
                            $discount3,
                        ),
                        'satuan_konversi' => (int) $row['purchase_conversion_id'],
                        'is_oot' => false,
                    ];
                })->values();
                $total = round($details->sum('subtotal'), 2);
                $purchaseOrder = $this->purchases->createPembelian([
                    'no_po' => $this->purchases->generatePo(),
                    'distributor_id' => (int) $distributorId,
                    'branch_id' => $branchId,
                    'tanggal_po' => today()->toDateString(),
                    'total_estimasi' => $total,
                    'status' => 'waiting_approval',
                    'catatan' => 'Dibuat dari Saran Pembelian periode '.$period
                        .' (target '.$filters['cover_days'].' hari, lead time '.$filters['lead_days'].' hari). '
                        .'Tinjau satuan, jumlah, harga, dan penandaan OOT sebelum approval.',
                    'created_by' => $user->id,
                    'approved_by' => null,
                ]);

                foreach ($details as $detail) {
                    $this->purchases->createPembelianDetail([
                        'purchase_order_id' => $purchaseOrder->id,
                        ...$detail,
                    ]);
                }

                $orders->push($purchaseOrder->setAttribute('suggestion_item_count', $details->count()));
            }

            return [
                'orders' => $orders,
                'skipped_count' => $skippedCount,
            ];
        }, 3);

        foreach ($result['orders'] as $purchaseOrder) {
            try {
                $this->transactionNotifications->notifyApprovalRequest('pembelian', $purchaseOrder, $user);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        $orderCount = $result['orders']->count();
        $message = $orderCount.' purchase order berhasil dibuat dan menunggu approval.';
        if ($result['skipped_count'] > 0) {
            $message .= ' '.$result['skipped_count'].' produk dilewati karena kebutuhannya sudah terpenuhi.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'orders' => $result['orders']->map(fn ($purchaseOrder) => [
                'id' => (int) $purchaseOrder->id,
                'no_po' => $purchaseOrder->no_po,
                'distributor_id' => (int) $purchaseOrder->distributor_id,
                'item_count' => (int) $purchaseOrder->suggestion_item_count,
                'total_estimasi' => (float) $purchaseOrder->total_estimasi,
            ])->values()->all(),
            'skipped_count' => $result['skipped_count'],
            'redirect_url' => route('pembelian.pembelian'),
        ], 201);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('date_start'), ['after_or_equal:date_start'])],
            'search' => ['nullable', 'string', 'max:150'],
            'slow_days' => ['nullable', 'integer', 'min:7', 'max:180'],
            'dead_days' => ['nullable', 'integer', 'min:30', 'max:730'],
            'cover_days' => ['nullable', 'integer', 'min:7', 'max:180'],
            'lead_days' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $end = isset($validated['date_end'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_end'])->endOfDay()
            : today()->endOfDay();
        $start = isset($validated['date_start'])
            ? Carbon::createFromFormat('Y-m-d', $validated['date_start'])->startOfDay()
            : $end->copy()->subDays(89)->startOfDay();

        if ($start->gt($end)) {
            throw ValidationException::withMessages(['date_end' => 'Tanggal akhir harus setelah tanggal mulai.']);
        }

        if ($start->diffInDays($end) > 365) {
            throw ValidationException::withMessages(['date_end' => 'Rentang analisis maksimal 366 hari.']);
        }

        $slowDays = (int) ($validated['slow_days'] ?? 30);
        $deadDays = (int) ($validated['dead_days'] ?? 90);
        if ($deadDays <= $slowDays) {
            throw ValidationException::withMessages(['dead_days' => 'Batas dead stock harus lebih besar dari batas slow moving.']);
        }

        $coverDays = (int) ($validated['cover_days'] ?? 30);
        $leadDays = (int) ($validated['lead_days'] ?? 7);
        if ($leadDays > $coverDays) {
            throw ValidationException::withMessages(['lead_days' => 'Lead time pemasok tidak boleh melebihi target ketersediaan.']);
        }

        return [
            'branch_id' => isset($validated['branch_id']) ? (int) $validated['branch_id'] : null,
            'start' => $start,
            'end' => $end,
            'period_days' => $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1,
            'search' => trim((string) ($validated['search'] ?? '')),
            'slow_days' => $slowDays,
            'dead_days' => $deadDays,
            'cover_days' => $coverDays,
            'lead_days' => $leadDays,
        ];
    }
}
