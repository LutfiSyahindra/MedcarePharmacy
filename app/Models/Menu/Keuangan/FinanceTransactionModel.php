<?php

namespace App\Models\Menu\Keuangan;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\CashierCashMovementModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'finance_transactions';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'posted_at' => 'datetime',
        'voided_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function category()
    {
        return $this->belongsTo(FinanceCategoryModel::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function cashierMovements()
    {
        return $this->hasMany(CashierCashMovementModel::class, 'finance_transaction_id');
    }
}
