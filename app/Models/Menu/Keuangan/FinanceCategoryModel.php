<?php

namespace App\Models\Menu\Keuangan;

use App\Models\BranchModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceCategoryModel extends Model
{
    use HasFactory;

    protected $table = 'finance_categories';

    protected $guarded = [];

    protected $casts = [
        'is_operational' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function transactions()
    {
        return $this->hasMany(FinanceTransactionModel::class, 'category_id');
    }
}
