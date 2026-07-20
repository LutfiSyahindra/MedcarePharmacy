<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianDetailModel extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_detail';

    protected $guarded = [];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelianModel::class, 'retur_pembelian_id');
    }

    public function penerimaanBarangDetail()
    {
        return $this->belongsTo(PenerimaanBarangDetailModel::class, 'penerimaan_barang_detail_id');
    }

    public function purchaseOrderDetail()
    {
        return $this->belongsTo(PembelianDetailModel::class, 'purchase_order_detail_id');
    }

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function stokBatch()
    {
        return $this->belongsTo(StokBatchModel::class, 'stok_batch_id');
    }

    public function satuanRetur()
    {
        return $this->belongsTo(SatuansModel::class, 'satuan_retur_id');
    }
}
