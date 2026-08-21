<?php

namespace App\Models\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualanModel extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan';

    protected $guarded = [];

    protected $casts = [
        'tanggal_retur' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_qty' => 'decimal:2',
        'subtotal_gross' => 'decimal:2',
        'diskon_item_total' => 'decimal:2',
        'diskon_transaksi_total' => 'decimal:2',
        'pajak_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(PenjualanTransactionModel::class, 'penjualan_transaction_id');
    }

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function details()
    {
        return $this->hasMany(ReturPenjualanDetailModel::class, 'retur_penjualan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
