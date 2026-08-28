<?php

namespace App\Models\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\PatientModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'penjualan_transactions';

    protected $guarded = [];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'tanggal_resep' => 'date',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal_gross' => 'decimal:2',
        'diskon_item_total' => 'decimal:2',
        'diskon_transaksi_percent' => 'decimal:2',
        'diskon_transaksi_nominal' => 'decimal:2',
        'subtotal_net' => 'decimal:2',
        'embalase' => 'decimal:2',
        'pajak_percent' => 'decimal:2',
        'pajak_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function patient()
    {
        return $this->belongsTo(PatientModel::class, 'patient_id');
    }

    public function cashierShift()
    {
        return $this->belongsTo(CashierShiftModel::class, 'cashier_shift_id');
    }

    public function details()
    {
        return $this->hasMany(PenjualanTransactionDetailModel::class, 'penjualan_transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(PenjualanPaymentModel::class, 'penjualan_transaction_id');
    }

    public function salesReturns()
    {
        return $this->hasMany(ReturPenjualanModel::class, 'penjualan_transaction_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
