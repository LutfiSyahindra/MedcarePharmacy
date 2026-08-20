<?php

namespace App\Http\Controllers\Medcare\Menu\Dokumen;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Services\Menu\Dokumen\DocumentArchiveService;
use App\Support\BranchAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentArchiveService $documents) {}

    public function index(Request $request): View
    {
        $branches = BranchModel::query()
            ->with('apotekProfile')
            ->whereIn('id', BranchAccess::userBranchIds())
            ->orderBy('name')
            ->get();
        $requestedBranchId = $request->integer('branch_id');
        $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
        $selectedBranch = $branches->firstWhere('id', $requestedBranchId)
            ?: $branches->firstWhere('id', $activeBranchId)
            ?: $branches->first();

        return view('medcare.menu.dokumen.index', [
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
        ]);
    }

    public function template(Request $request, string $type): View
    {
        abort_unless(in_array($type, DocumentArchiveService::types(), true), 404);

        $branches = BranchModel::query()
            ->with('apotekProfile')
            ->whereIn('id', BranchAccess::userBranchIds())
            ->orderBy('name')
            ->get();
        $requestedBranchId = $request->integer('branch_id');

        if ($requestedBranchId > 0) {
            $branch = $branches->firstWhere('id', $requestedBranchId);
            abort_if(! $branch, 404, 'Cabang tidak ditemukan atau tidak dapat diakses.');
        } else {
            $activeBranchId = (int) $request->session()->get('active_branch_id', 0);
            $branch = $branches->firstWhere('id', $activeBranchId) ?: $branches->first();
        }

        abort_if(! $branch, 422, 'User belum memiliki akses cabang untuk membuat template dokumen.');

        return view('medcare.menu.dokumen.template-surat-pesanan', [
            'branch' => $branch,
            'type' => $type,
            'meta' => $this->documents->typeMetadata($type),
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'type' => ['nullable', Rule::in(DocumentArchiveService::types())],
            'status' => ['nullable', 'string', 'max:40'],
            'date_start' => ['nullable', 'date_format:Y-m-d'],
            'date_end' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_start'],
        ]);

        $archive = $this->documents->archive(BranchAccess::userBranchIds());
        $summaryDocuments = $this->documents->filter($archive, [
            'status' => $filters['status'] ?? null,
            'date_start' => $filters['date_start'] ?? null,
            'date_end' => $filters['date_end'] ?? null,
        ]);
        $documents = $this->documents->filter($summaryDocuments, [
            'type' => $filters['type'] ?? null,
        ]);
        $summary = $this->documents->summary($summaryDocuments);
        $tableDocuments = $documents->map(fn (array $document) => Arr::except($document, [
            'document_numbers',
            'items',
            'notes',
        ]));

        return DataTables::of($tableDocuments)
            ->addIndexColumn()
            ->with(['summary' => $summary])
            ->make(true);
    }

    public function show(string $type, int $purchaseOrder): JsonResponse
    {
        abort_unless(in_array($type, DocumentArchiveService::types(), true), 404);

        $document = $this->documents->findDocument(
            $purchaseOrder,
            $type,
            BranchAccess::userBranchIds(),
        );

        abort_if(! $document, 404, 'Dokumen tidak ditemukan atau tidak dapat diakses.');

        return response()->json(['data' => $document]);
    }
}
