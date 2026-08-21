<?php

namespace App\Models\Menu\Penjualan;

use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualanDetailModel extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan_details';

    protected $guarded = [];

    protected $casts = [
        'konversi' => 'decimal:4',
        'qty_jual' => 'decimal:2',
        'qty_stok' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'subtotal_gross' => 'decimal:2',
        'diskon_item_nominal' => 'decimal:2',
        'diskon_transaksi_nominal' => 'decimal:2',
        'pajak_nominal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(ReturPenjualanModel::class, 'retur_penjualan_id');
    }

    public function saleDetail()
    {
        return $this->belongsTo(PenjualanTransactionDetailModel::class, 'penjualan_transaction_detail_id');
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
        return $this->hasMany(ReturPenjualanBatchModel::class, 'retur_penjualan_detail_id');
    }
}
