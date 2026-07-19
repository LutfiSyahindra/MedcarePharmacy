<?php

namespace App\Models\Menu\Penjualan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanPaymentModel extends Model
{
    use HasFactory;

    protected $table = 'penjualan_payments';

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(PenjualanTransactionModel::class, 'penjualan_transaction_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
