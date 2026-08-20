<?php

namespace App\Services\Menu\Dokumen;

use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Services\Menu\PembelianPenerimaan\SuratPesananNarkotikaService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPrekursorService;
use App\Services\Menu\PembelianPenerimaan\SuratPesananPsikotropikaService;
use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocumentArchiveService
{
    public const TYPE_NARCOTIC = 'narkotika';

    public const TYPE_PSYCHOTROPIC = 'psikotropika';

    public const TYPE_PRECURSOR = 'prekursor';

    private const EAGER_LOADS = [
        'distributor',
        'branch.apotekProfile',
        'createdBy',
        'approvedBy',
        'details.obat.golongan',
        'details.obat.mainGolongan.golongan',
        'details.obat.subGolongan.mainGolongan.golongan',
        'details.obat.sediaan',
        'details.obat.satuan',
        'details.satuanKonversi.satuan',
    ];

    public function __construct(
        private readonly SuratPesananNarkotikaService $narcoticOrders,
        private readonly SuratPesananPsikotropikaService $psychotropicOrders,
        private readonly SuratPesananPrekursorService $precursorOrders,
    ) {}

    /**
     * @return array<int, string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_NARCOTIC,
            self::TYPE_PSYCHOTROPIC,
            self::TYPE_PRECURSOR,
        ];
    }

    /**
     * Build the central archive without copying purchase-order data into a new table.
     *
     * @param  array<int, int>  $branchIds
     * @return Collection<int, array<string, mixed>>
     */
    public function archive(array $branchIds): Collection
    {
        $query = PembelianModel::query()->with(self::EAGER_LOADS);
        BranchAccess::scope($query, 'branch_id', $branchIds);

        $purchaseOrders = $query
            ->orderByDesc('tanggal_po')
            ->orderByDesc('id')
            ->get();

        return $this->documentsFromPurchaseOrders($purchaseOrders);
    }

    /**
     * @param  EloquentCollection<int, PembelianModel>|Collection<int, PembelianModel>  $purchaseOrders
     * @return Collection<int, array<string, mixed>>
     */
    public function documentsFromPurchaseOrders(EloquentCollection|Collection $purchaseOrders): Collection
    {
        return $purchaseOrders
            ->flatMap(fn (PembelianModel $purchaseOrder) => $this->documentsForPurchaseOrder($purchaseOrder))
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function documentsForPurchaseOrder(PembelianModel $purchaseOrder): Collection
    {
        $purchaseOrder->loadMissing(self::EAGER_LOADS);
        $documents = collect();

        $narcoticDetails = $this->narcoticOrders->narcoticDetails($purchaseOrder);
        if ($narcoticDetails->isNotEmpty()) {
            $documents->push($this->makeDocument(
                $purchaseOrder,
                self::TYPE_NARCOTIC,
                $narcoticDetails,
                $narcoticDetails->pluck('narcotic_document_number')->filter()->values()->all(),
                SuratPesananNarkotikaService::COPY_COUNT,
                'pembelian.suratPesananNarkotika',
            ));
        }

        $psychotropicDetails = $this->psychotropicOrders->psychotropicDetails($purchaseOrder);
        if ($psychotropicDetails->isNotEmpty()) {
            $documents->push($this->makeDocument(
                $purchaseOrder,
                self::TYPE_PSYCHOTROPIC,
                $psychotropicDetails,
                [(string) $purchaseOrder->getAttribute('psychotropic_document_number')],
                SuratPesananPsikotropikaService::COPY_COUNT,
                'pembelian.suratPesananPsikotropika',
            ));
        }

        $precursorDetails = $this->precursorOrders->precursorDetails($purchaseOrder);
        if ($precursorDetails->isNotEmpty()) {
            $documents->push($this->makeDocument(
                $purchaseOrder,
                self::TYPE_PRECURSOR,
                $precursorDetails,
                [(string) $purchaseOrder->getAttribute('precursor_document_number')],
                SuratPesananPrekursorService::COPY_COUNT,
                'pembelian.suratPesananPrekursor',
            ));
        }

        return $documents;
    }

    /**
     * @param  array<int, int>  $branchIds
     * @return array<string, mixed>|null
     */
    public function findDocument(int $purchaseOrderId, string $type, array $branchIds): ?array
    {
        if (! in_array($type, self::types(), true)) {
            return null;
        }

        $query = PembelianModel::query()->with(self::EAGER_LOADS);
        BranchAccess::scope($query, 'branch_id', $branchIds);

        $purchaseOrder = $query->find($purchaseOrderId);
        if (! $purchaseOrder) {
            return null;
        }

        return $this->documentsForPurchaseOrder($purchaseOrder)
            ->firstWhere('type', $type);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $documents
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filter(Collection $documents, array $filters): Collection
    {
        $type = $filters['type'] ?? null;
        $status = $filters['status'] ?? null;
        $dateStart = $filters['date_start'] ?? null;
        $dateEnd = $filters['date_end'] ?? null;

        return $documents
            ->when($type, fn (Collection $rows) => $rows->where('type', $type))
            ->when($status, fn (Collection $rows) => $rows->where('status', $status))
            ->when($dateStart, fn (Collection $rows) => $rows->filter(
                fn (array $document) => ($document['date'] ?? '') >= $dateStart
            ))
            ->when($dateEnd, fn (Collection $rows) => $rows->filter(
                fn (array $document) => ($document['date'] ?? '') <= $dateEnd
            ))
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $documents
     * @return array<string, int>
     */
    public function summary(Collection $documents): array
    {
        return [
            'sets' => $documents->count(),
            'documents' => (int) $documents->sum('document_count'),
            'purchase_orders' => $documents->pluck('purchase_order_id')->unique()->count(),
            'narkotika' => (int) $documents->where('type', self::TYPE_NARCOTIC)->sum('document_count'),
            'psikotropika' => (int) $documents->where('type', self::TYPE_PSYCHOTROPIC)->sum('document_count'),
            'prekursor' => (int) $documents->where('type', self::TYPE_PRECURSOR)->sum('document_count'),
            'pages' => (int) $documents->sum('page_count'),
        ];
    }

    /**
     * @param  Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>  $details
     * @param  array<int, string>  $documentNumbers
     * @return array<string, mixed>
     */
    private function makeDocument(
        PembelianModel $purchaseOrder,
        string $type,
        Collection $details,
        array $documentNumbers,
        int $copyCount,
        string $printRoute,
    ): array {
        $meta = $this->typeMetadata($type);
        $status = (string) ($purchaseOrder->status ?: 'draft');
        $items = $details->map(function ($detail) use ($type) {
            $medicine = $detail->obat;
            $unit = $detail->satuanKonversi?->satuan?->nama
                ?: $medicine?->satuan?->nama
                ?: 'unit';

            return [
                'id' => (int) ($detail->id ?? 0),
                'name' => $medicine?->nama_obat ?: 'Obat tidak diketahui',
                'classification' => $detail->getAttribute($this->classificationAttribute($type)) ?: '-',
                'quantity' => (float) ($detail->qty ?? 0),
                'unit' => $unit,
                'document_number' => $type === self::TYPE_NARCOTIC
                    ? $detail->getAttribute('narcotic_document_number')
                    : ($documentNumbers[0] ?? '-'),
            ];
        })->values();

        $documentCount = max(1, count($documentNumbers));
        $firstNumber = $documentNumbers[0] ?? trim((string) $purchaseOrder->no_po);
        $medicineNames = $items->pluck('name')->implode(', ');

        return [
            'id' => $type.'-'.$purchaseOrder->getKey(),
            'purchase_order_id' => (int) $purchaseOrder->getKey(),
            'purchase_order_number' => (string) $purchaseOrder->no_po,
            'document_number' => $firstNumber,
            'document_numbers' => $documentNumbers,
            'document_count' => $documentCount,
            'copy_count' => $copyCount,
            'page_count' => $documentCount * $copyCount,
            'type' => $type,
            'type_label' => $meta['label'],
            'type_short_label' => $meta['short_label'],
            'type_description' => $meta['description'],
            'date' => (string) $purchaseOrder->tanggal_po,
            'branch' => $purchaseOrder->branch?->name ?: '-',
            'distributor' => $purchaseOrder->distributor?->nama ?: '-',
            'status' => $status,
            'status_label' => $this->statusLabel($status),
            'is_ready' => in_array($status, ['approved', 'diterima_sebagian', 'selesai'], true),
            'item_count' => $items->count(),
            'medicine_names' => $medicineNames,
            'created_by' => $purchaseOrder->createdBy?->name ?: '-',
            'approved_by' => $purchaseOrder->approvedBy?->name,
            'notes' => $purchaseOrder->catatan,
            'items' => $items->all(),
            'preview_url' => route($printRoute, ['id' => $purchaseOrder->getKey()]),
            'print_url' => route($printRoute, ['id' => $purchaseOrder->getKey(), 'print' => 1]),
            'search_text' => Str::lower(implode(' ', [
                $firstNumber,
                implode(' ', $documentNumbers),
                $purchaseOrder->no_po,
                $meta['label'],
                $purchaseOrder->branch?->name,
                $purchaseOrder->distributor?->nama,
                $medicineNames,
            ])),
        ];
    }

    /**
     * @return array{label: string, short_label: string, description: string}
     */
    public function typeMetadata(string $type): array
    {
        return match ($type) {
            self::TYPE_NARCOTIC => [
                'label' => 'Surat Pesanan Narkotika',
                'short_label' => 'Narkotika',
                'description' => 'Satu surat untuk setiap jenis obat Narkotika.',
            ],
            self::TYPE_PSYCHOTROPIC => [
                'label' => 'Surat Pesanan Psikotropika',
                'short_label' => 'Psikotropika',
                'description' => 'Surat pesanan khusus obat Psikotropika.',
            ],
            self::TYPE_PRECURSOR => [
                'label' => 'Surat Pesanan Prekursor',
                'short_label' => 'Prekursor',
                'description' => 'Surat pesanan obat atau bahan obat Prekursor Farmasi.',
            ],
        };
    }

    private function classificationAttribute(string $type): string
    {
        return match ($type) {
            self::TYPE_NARCOTIC => 'narcotic_classification',
            self::TYPE_PSYCHOTROPIC => 'psychotropic_classification',
            self::TYPE_PRECURSOR => 'precursor_classification',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'waiting_approval' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'diterima_sebagian' => 'Diterima Sebagian',
            'selesai' => 'Selesai',
            default => Str::headline($status),
        };
    }
}
