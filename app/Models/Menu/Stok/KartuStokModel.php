<?php

namespace App\Models\Menu\Stok;

use App\Models\MasterObatModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuStokModel extends Model
{
    use HasFactory;

    protected $table = 'kartu_stok';

    protected $guarded = [];

    protected $casts = [
        'tanggal_mutasi' => 'datetime',
        'expired_date' => 'date',
    ];

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function batch()
    {
        return $this->belongsTo(StokBatchModel::class, 'stok_batch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
