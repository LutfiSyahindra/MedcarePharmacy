<?php

namespace App\Models;

use App\Models\Menu\Penjualan\CashierShiftModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BranchModel extends Model
{
    use HasFactory;

    protected $table = 'branches';

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'is_active',
        'operational_timezone',
    ];

    /**
     * Relasi: Branch punya banyak User
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_branches',
            'branch_id',
            'user_id'
        );
    }

    public function apotekProfile(): HasOne
    {
        return $this->hasOne(ApotekProfile::class, 'branch_id');
    }

    public function cashierShifts(): HasMany
    {
        return $this->hasMany(CashierShiftModel::class, 'branch_id');
    }
}
