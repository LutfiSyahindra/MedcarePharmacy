<?php

namespace App\Http\Controllers\Medcare\Menu\Pasien;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Models\PatientModel;
use App\Services\Settings\Auth\RoleSettingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{
    public function __construct(private readonly RoleSettingService $roleSettings) {}

    public function index(Request $request)
    {
        $branches = BranchModel::query()
            ->whereIn('id', $this->accessibleBranchIds($request))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $sessionBranchId = (int) $request->session()->get('active_branch_id', 0);
        $selectedBranchId = $branches->contains('id', $sessionBranchId)
            ? $sessionBranchId
            : ($branches->count() === 1 ? (int) $branches->first()->id : null);

        return view('medcare.menu.pasien.index', [
            'branches' => $branches,
            'selectedBranchId' => $selectedBranchId,
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer'],
        ]);

        $query = $this->accessiblePatients($request)
            ->with('branch:id,code,name')
            ->when(isset($validated['branch_id']), fn (Builder $query) => $query->where('branch_id', $validated['branch_id']))
            ->select('patients.*');

        return DataTables::eloquent($query)
            ->addColumn('branch_name', fn (PatientModel $patient) => $patient->branch?->name ?: '-')
            ->addColumn('updated_label', fn (PatientModel $patient) => $patient->updated_at?->format('d M Y H:i'))
            ->addColumn('actions', function (PatientModel $patient) {
                return '<div class="patient-actions">'
                    .'<button type="button" class="btn patient-action is-edit" data-patient-action="edit" data-id="'.$patient->id.'" title="Edit pasien"><i class="mdi mdi-pencil-outline"></i></button>'
                    .'<button type="button" class="btn patient-action is-delete" data-patient-action="delete" data-id="'.$patient->id.'" title="Hapus pasien"><i class="mdi mdi-delete-outline"></i></button>'
                    .'</div>';
            })
            ->filter(function (Builder $query) use ($request) {
                $search = trim((string) data_get($request->input('search'), 'value', ''));

                if ($search === '') {
                    return;
                }

                $like = '%'.$search.'%';
                $query->where(function (Builder $query) use ($like) {
                    $query->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhereHas('branch', fn (Builder $branchQuery) => $branchQuery->where('name', 'like', $like));
                });
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function visits(Request $request): JsonResponse
    {
        $branchIds = $this->accessibleBranchIds($request);
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['week', 'month', 'year'])],
            'branch_id' => ['nullable', 'integer', Rule::in($branchIds)],
        ], [
            'period.in' => 'Periode kunjungan tidak tersedia.',
            'branch_id.in' => 'Cabang tidak dapat diakses oleh akun ini.',
        ]);

        $period = (string) ($validated['period'] ?? 'week');
        $selectedBranchIds = isset($validated['branch_id'])
            ? [(int) $validated['branch_id']]
            : $branchIds;
        $end = today()->endOfDay();
        $start = match ($period) {
            'month' => today()->startOfMonth(),
            'year' => today()->startOfYear(),
            default => today()->startOfWeek(Carbon::MONDAY),
        };

        $visits = DB::table('penjualan_transactions as sales')
            ->whereIn('sales.branch_id', $selectedBranchIds)
            ->whereNotNull('sales.patient_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.tanggal_transaksi', [$start, $end]);

        $patientRows = (clone $visits)
            ->join('patients', 'patients.id', '=', 'sales.patient_id')
            ->groupBy('patients.id', 'patients.name', 'patients.phone')
            ->select(['patients.id', 'patients.name', 'patients.phone'])
            ->selectRaw('COUNT(*) as visit_count')
            ->selectRaw('MAX(sales.tanggal_transaksi) as last_visit_at')
            ->orderByDesc('visit_count')
            ->orderByDesc('last_visit_at')
            ->get();

        $totalVisits = (int) $patientRows->sum(fn ($patient) => (int) $patient->visit_count);
        $uniquePatients = $patientRows->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'meta' => [
                    'period' => $period,
                    'period_label' => match ($period) {
                        'month' => 'Bulan ini',
                        'year' => 'Tahun ini',
                        default => 'Minggu ini',
                    },
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'range_label' => $start->translatedFormat('d M Y').' - '.$end->translatedFormat('d M Y'),
                ],
                'summary' => [
                    'visits' => $totalVisits,
                    'unique_patients' => $uniquePatients,
                    'repeat_visits' => max(0, $totalVisits - $uniquePatients),
                ],
                'trend' => $this->visitTrend($visits, $period, $start, $end),
                'top_patients' => $patientRows->take(5)->values()->map(fn ($patient) => [
                    'id' => (int) $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'visits' => (int) $patient->visit_count,
                    'last_visit' => Carbon::parse($patient->last_visit_at)->translatedFormat('d M Y H:i'),
                ])->all(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge(['phone' => PatientModel::normalizePhone($request->input('phone'))]);
        $validated = $this->validatePatient($request);
        $patient = PatientModel::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data pasien berhasil disimpan.',
            'data' => $this->patientPayload($patient->load('branch:id,code,name')),
        ], 201);
    }

    public function show(Request $request, int $patient): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->patientPayload($this->findAccessiblePatient($request, $patient)),
        ]);
    }

    public function update(Request $request, int $patient): JsonResponse
    {
        $patientModel = $this->findAccessiblePatient($request, $patient);
        $request->merge(['phone' => PatientModel::normalizePhone($request->input('phone'))]);
        $validated = $this->validatePatient($request, $patientModel);

        $patientModel->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data pasien berhasil diperbarui.',
            'data' => $this->patientPayload($patientModel->fresh('branch:id,code,name')),
        ]);
    }

    public function destroy(Request $request, int $patient): JsonResponse
    {
        $patientModel = $this->findAccessiblePatient($request, $patient);
        $patientModel->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Data pasien berhasil dihapus.',
        ]);
    }

    private function accessiblePatients(Request $request): Builder
    {
        return PatientModel::query()
            ->whereIn('branch_id', $this->accessibleBranchIds($request));
    }

    private function visitTrend(QueryBuilder $visits, string $period, Carbon $start, Carbon $end): array
    {
        $isYear = $period === 'year';
        $dateColumn = 'sales.tanggal_transaksi';
        $bucketExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => $isYear
                ? "strftime('%Y-%m', {$dateColumn})"
                : "date({$dateColumn})",
            'pgsql' => $isYear
                ? "to_char({$dateColumn}, 'YYYY-MM')"
                : "to_char({$dateColumn}, 'YYYY-MM-DD')",
            'sqlsrv' => $isYear
                ? "FORMAT({$dateColumn}, 'yyyy-MM')"
                : "CONVERT(varchar(10), {$dateColumn}, 23)",
            default => $isYear
                ? "DATE_FORMAT({$dateColumn}, '%Y-%m')"
                : "DATE({$dateColumn})",
        };

        $rows = (clone $visits)
            ->selectRaw("{$bucketExpression} as period_bucket, COUNT(*) as total")
            ->groupByRaw($bucketExpression)
            ->pluck('total', 'period_bucket');

        $labels = [];
        $values = [];
        $cursor = $start->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $key = $isYear ? $cursor->format('Y-m') : $cursor->toDateString();
            $labels[] = $isYear
                ? $cursor->translatedFormat('M')
                : $cursor->translatedFormat('d M');
            $values[] = (int) ($rows[$key] ?? 0);
            $isYear ? $cursor->addMonth() : $cursor->addDay();
        }

        return compact('labels', 'values');
    }

    private function findAccessiblePatient(Request $request, int $patient): PatientModel
    {
        return $this->accessiblePatients($request)
            ->with('branch:id,code,name')
            ->findOrFail($patient);
    }

    private function validatePatient(Request $request, ?PatientModel $patient = null): array
    {
        $accessibleActiveBranchIds = BranchModel::query()
            ->whereIn('id', $this->accessibleBranchIds($request))
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $request->validate([
            'branch_id' => ['required', 'integer', Rule::in($accessibleActiveBranchIds)],
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'digits_between:8,15',
                Rule::unique('patients', 'phone')
                    ->where(fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
                    ->ignore($patient?->id),
            ],
        ], [
            'branch_id.required' => 'Cabang pasien wajib dipilih.',
            'branch_id.in' => 'Cabang tidak aktif atau tidak dapat diakses.',
            'name.required' => 'Nama lengkap pasien wajib diisi.',
            'phone.required' => 'Nomor telepon pasien wajib diisi.',
            'phone.digits_between' => 'Nomor telepon harus terdiri dari 8 sampai 15 angka.',
            'phone.unique' => 'Nomor telepon sudah terdaftar pada cabang ini.',
        ]);
    }

    private function accessibleBranchIds(Request $request): array
    {
        return $this->roleSettings->posBranchIds($request->user());
    }

    private function patientPayload(PatientModel $patient): array
    {
        return [
            'id' => $patient->id,
            'branch_id' => $patient->branch_id,
            'branch_name' => $patient->branch?->name,
            'name' => $patient->name,
            'phone' => $patient->phone,
            'created_at' => $patient->created_at?->format('d M Y H:i'),
            'updated_at' => $patient->updated_at?->format('d M Y H:i'),
        ];
    }
}
