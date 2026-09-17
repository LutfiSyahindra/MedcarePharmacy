<?php

namespace App\Models\Menu\Penjualan;

use App\Models\Menu\Keuangan\FinanceTransactionModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierCashMovementModel extends Model
{
    use HasFactory;

    protected $table = 'cashier_cash_movements';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(CashierShiftModel::class, 'cashier_shift_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function financeTransaction()
    {
        return $this->belongsTo(FinanceTransactionModel::class, 'finance_transaction_id');
    }
}
