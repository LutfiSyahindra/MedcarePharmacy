<?php

namespace App\Models\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\RakPenyimpananModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetailModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_details';

    protected $guarded = [];

    protected $casts = [
        'expired_date' => 'date',
        'stok_sistem_awal' => 'decimal:2',
        'stok_sistem_hitung' => 'decimal:2',
        'stok_fisik' => 'decimal:2',
        'selisih' => 'decimal:2',
        'hpp' => 'decimal:2',
        'mutasi_masuk' => 'decimal:2',
        'mutasi_keluar' => 'decimal:2',
        'stok_sistem_validasi' => 'decimal:2',
        'stok_target_validasi' => 'decimal:2',
        'selisih_validasi' => 'decimal:2',
        'counted_at' => 'datetime',
    ];

    public function opname()
    {
        return $this->belongsTo(StockOpnameModel::class, 'stock_opname_id');
    }

    public function batch()
    {
        return $this->belongsTo(StokBatchModel::class, 'stok_batch_id');
    }

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function rack()
    {
        return $this->belongsTo(RakPenyimpananModel::class, 'rak_id');
    }

    public function counter()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function kartuStok()
    {
        return $this->belongsTo(KartuStokModel::class, 'kartu_stok_id');
    }
}
