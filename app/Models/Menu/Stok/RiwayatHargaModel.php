<?php

namespace App\Models\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatHargaModel extends Model
{
    use HasFactory;

    protected $table = 'riwayat_harga';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'harga_jual_lama' => 'decimal:2',
        'harga_jual_baru' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function batch()
    {
        return $this->belongsTo(StokBatchModel::class, 'stok_batch_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
