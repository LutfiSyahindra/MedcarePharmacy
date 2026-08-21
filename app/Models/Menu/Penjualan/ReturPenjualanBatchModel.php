<?php

namespace App\Models\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPenjualanBatchModel extends Model
{
    use HasFactory;

    protected $table = 'retur_penjualan_batches';

    protected $guarded = [];

    protected $casts = [
        'expired_date' => 'date',
        'qty_stok' => 'decimal:2',
    ];

    public function detail()
    {
        return $this->belongsTo(ReturPenjualanDetailModel::class, 'retur_penjualan_detail_id');
    }

    public function saleBatch()
    {
        return $this->belongsTo(PenjualanTransactionBatchModel::class, 'penjualan_transaction_batch_id');
    }

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function stockBatch()
    {
        return $this->belongsTo(StokBatchModel::class, 'stok_batch_id');
    }

    public function kartuStok()
    {
        return $this->belongsTo(KartuStokModel::class, 'kartu_stok_id');
    }

    public function cancelKartuStok()
    {
        return $this->belongsTo(KartuStokModel::class, 'cancel_kartu_stok_id');
    }
}
