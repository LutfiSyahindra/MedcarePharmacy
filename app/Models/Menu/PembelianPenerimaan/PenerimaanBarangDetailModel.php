<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StokBatchModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBarangDetailModel extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_barang_detail';

    protected $guarded = [];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarangModel::class, 'penerimaan_barang_id');
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
}
