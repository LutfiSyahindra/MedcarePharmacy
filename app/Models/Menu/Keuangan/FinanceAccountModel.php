<?php

namespace App\Models\Menu\Keuangan;

use App\Models\BranchModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceAccountModel extends Model
{
    use HasFactory;

    protected $table = 'finance_accounts';

    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
}
