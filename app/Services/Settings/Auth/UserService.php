<?php

namespace App\Services\Settings\Auth;

use App\Models\Menu\Stok\StockOpnameModel;
use App\Repositories\Settings\Auth\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getData()
    {
        return $this->userRepository->getData();
    }

    public function updateStatus($id, $status)
    {
        return $this->userRepository->updateStatus($id, $status);
    }

    public function create($data)
    {
        return $this->userRepository->createUser($data);
    }

    public function findById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function update($id, $data)
    {
        $user = $this->userRepository->findById($id);

        if (! $user) {
            return null; // atau bisa lempar exception
        }

        $user->update($data);

        return $user;
    }

    public function destroy($id)
    {
        $user = $this->userRepository->findById($id);
        $user->delete();
    }

    public function assignRolesToUsers($userId, array $roleIds)
    {
        $user = $this->userRepository->findById($userId);

        // Ambil role berdasarkan ID, lalu hanya ambil nama
        $roles = $this->userRepository->getRolesByIds($roleIds)->pluck('name')->toArray();

        return $this->userRepository->syncRoles($user, $roles);
    }

    public function getUsersRoles($userId)
    {
        return $this->userRepository->getUsersRoles($userId);
    }

    public function getStockOpnameTransactions(int $userId): array
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            abort(404, 'User tidak ditemukan.');
        }

        $statusLabels = StockOpnameModel::statusLabels();
        $transactions = $this->userRepository->getStockOpnameTransactions($userId)
            ->map(function (StockOpnameModel $opname) use ($userId, $statusLabels) {
                $roles = collect([
                    'created_by' => 'Pembuat',
                    'started_by' => 'Pemulai',
                    'submitted_by' => 'Pengirim',
                    'verified_by' => 'Verifikator',
                    'approved_by' => 'Approver',
                    'adjusted_by' => 'Penyesuai',
                ])->filter(fn ($label, $column) => (int) $opname->{$column} === $userId)
                    ->values();

                if ((int) $opname->user_counted_items_count > 0) {
                    $roles->push('Penghitung');
                }

                if ($roles->isEmpty() && $opname->logs->isNotEmpty()) {
                    $roles->push('Pelaksana');
                }

                return [
                    'id' => (int) $opname->id,
                    'nomor' => $opname->nomor,
                    'tanggal_opname' => optional($opname->tanggal_opname)->format('Y-m-d'),
                    'branch' => $opname->branch?->name ?: '-',
                    'lokasi' => $opname->rack ? $opname->rack->kode.' - '.$opname->rack->nama : 'Semua Rak',
                    'status' => $opname->status,
                    'status_label' => $statusLabels[$opname->status] ?? $opname->status,
                    'roles' => $roles->unique()->values()->all(),
                    'counted_items' => (int) $opname->user_counted_items_count,
                    'activities' => $opname->logs->map(fn ($log) => [
                        'action' => $log->action,
                        'label' => $this->stockOpnameActivityLabel($log->action),
                        'note' => $log->note,
                        'performed_at' => optional($log->performed_at)->format('Y-m-d H:i:s'),
                    ])->values()->all(),
                ];
            })->values();

        return [
            'user' => [
                'id' => (int) $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'summary' => [
                'total' => $transactions->count(),
                'counting' => $transactions->where('status', StockOpnameModel::STATUS_COUNTING)->count(),
                'waiting' => $transactions->whereIn('status', [
                    StockOpnameModel::STATUS_AWAITING_VERIFICATION,
                    StockOpnameModel::STATUS_AWAITING_APPROVAL,
                ])->count(),
                'completed' => $transactions->whereIn('status', [
                    StockOpnameModel::STATUS_APPROVED,
                    StockOpnameModel::STATUS_ADJUSTED,
                ])->count(),
            ],
            'transactions' => $transactions->all(),
        ];
    }

    private function stockOpnameActivityLabel(string $action): string
    {
        return [
            'create' => 'Membuat draft',
            'update' => 'Memperbarui draft',
            'start_counting' => 'Memulai penghitungan',
            'save_counts' => 'Menyimpan hasil hitung',
            'submit_verification' => 'Mengirim hasil penghitungan',
            'save_reasons' => 'Menyimpan alasan selisih',
            'verify' => 'Memvalidasi hasil opname',
            'approve' => 'Menyetujui stock opname',
            'reject' => 'Mengembalikan untuk dihitung ulang',
            'post_adjustment' => 'Memposting penyesuaian stok',
        ][$action] ?? str($action)->replace('_', ' ')->title()->toString();
    }
}
