<?php

namespace App\Models\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShiftModel extends Model
{
    use HasFactory;

    protected $table = 'cashier_shifts';

    protected $guarded = [];

    protected $casts = [
        'opening_amount' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'cash_in_total' => 'decimal:2',
        'cash_out_total' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movements()
    {
        return $this->hasMany(CashierCashMovementModel::class, 'cashier_shift_id');
    }

    public function transactions()
    {
        return $this->hasMany(PenjualanTransactionModel::class, 'cashier_shift_id');
    }
}
