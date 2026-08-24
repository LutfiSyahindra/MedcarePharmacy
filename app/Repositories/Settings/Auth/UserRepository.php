<?php

namespace App\Repositories\Settings\Auth;

use App\Models\Menu\Stok\StockOpnameModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UserRepository
{
    public function getData()
    {
        $dataUser = User::query()
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('stock_opnames as user_opnames')
                    ->selectRaw('COUNT(*)')
                    ->where(function ($opnameQuery) {
                        foreach ($this->stockOpnameActorColumns() as $column) {
                            $opnameQuery->orWhereColumn('user_opnames.'.$column, 'users.id');
                        }

                        $opnameQuery
                            ->orWhereExists(function ($detailQuery) {
                                $detailQuery->from('stock_opname_details as user_opname_details')
                                    ->selectRaw('1')
                                    ->whereColumn('user_opname_details.stock_opname_id', 'user_opnames.id')
                                    ->whereColumn('user_opname_details.counted_by', 'users.id');
                            })
                            ->orWhereExists(function ($logQuery) {
                                $logQuery->from('stock_opname_logs as user_opname_logs')
                                    ->selectRaw('1')
                                    ->whereColumn('user_opname_logs.stock_opname_id', 'user_opnames.id')
                                    ->whereColumn('user_opname_logs.performed_by', 'users.id');
                            });
                    });
            }, 'stock_opname_transactions_count')
            ->get();

        return $dataUser;
    }

    public function getStockOpnameTransactions(int $userId)
    {
        return StockOpnameModel::query()
            ->with(['branch', 'rack'])
            ->withCount([
                'details as user_counted_items_count' => fn ($query) => $query->where('counted_by', $userId),
            ])
            ->with([
                'logs' => fn ($query) => $query
                    ->where('performed_by', $userId)
                    ->orderByDesc('performed_at'),
            ])
            ->where(fn (Builder $query) => $this->scopeStockOpnameParticipation($query, $userId))
            ->latest('tanggal_opname')
            ->latest('id')
            ->get();
    }

    public function updateStatus($id, $status)
    {
        $user = User::find($id);
        $user->status = $status;
        $user->save();
    }

    public function createUser($data)
    {
        return User::create($data);
    }

    public function findById($id)
    {
        $data = User::find($id);

        return $data;
    }

    public function getRolesByIds(array $ids)
    {
        return Role::whereIn('id', $ids)->get();
    }

    public function syncRoles($user, array $roles)
    {
        return $user->syncRoles($roles);
    }

    public function getUsersRoles($userId)
    {
        $user = User::with('roles')->findOrFail($userId);

        return $user->roles;
    }

    private function scopeStockOpnameParticipation(Builder $query, int $userId): void
    {
        foreach ($this->stockOpnameActorColumns() as $column) {
            $query->orWhere($column, $userId);
        }

        $query
            ->orWhereHas('details', fn ($detailQuery) => $detailQuery->where('counted_by', $userId))
            ->orWhereHas('logs', fn ($logQuery) => $logQuery->where('performed_by', $userId));
    }

    private function stockOpnameActorColumns(): array
    {
        return [
            'created_by',
            'started_by',
            'submitted_by',
            'verified_by',
            'approved_by',
            'adjusted_by',
        ];
    }
}
