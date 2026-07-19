<?php

namespace App\Models\Menu\Penjualan;

use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenjualanTransactionDetailModel extends Model
{
    use HasFactory;

    protected $table = 'penjualan_transaction_details';

    protected $guarded = [];

    protected $casts = [
        'konversi' => 'decimal:4',
        'qty_jual' => 'decimal:2',
        'qty_stok' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'subtotal_gross' => 'decimal:2',
        'diskon_percent' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'subtotal_net' => 'decimal:2',
        'pajak_percent' => 'decimal:2',
        'pajak_nominal' => 'decimal:2',
        'total_line' => 'decimal:2',
        'batch_summary' => 'array',
        'durasi_hari' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(PenjualanTransactionModel::class, 'penjualan_transaction_id');
    }

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function satuan()
    {
        return $this->belongsTo(SatuansModel::class, 'satuan_id');
    }

    public function batchAllocations()
    {
        return $this->hasMany(PenjualanTransactionBatchModel::class, 'penjualan_transaction_detail_id');
    }
}
