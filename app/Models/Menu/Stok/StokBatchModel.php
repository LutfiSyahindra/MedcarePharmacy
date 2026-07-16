<?php

namespace App\Models\Menu\Stok;

use App\Models\BranchModel;
use App\Models\MasterObatModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokBatchModel extends Model
{
    use HasFactory;

    protected $table = 'stok_batches';

    protected $guarded = [];

    protected $casts = [
        'expired_date' => 'date',
        'qty' => 'decimal:2',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ppn' => 'decimal:2',
        'last_movement_at' => 'datetime',
    ];

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class, 'obat_id');
    }

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function kartuStok()
    {
        return $this->hasMany(KartuStokModel::class, 'stok_batch_id');
    }

    public function riwayatHarga()
    {
        return $this->hasMany(RiwayatHargaModel::class, 'stok_batch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
