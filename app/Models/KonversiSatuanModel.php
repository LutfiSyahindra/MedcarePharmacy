<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonversiSatuanModel extends Model
{
    use HasFactory;

    protected $table = 'obat_satuan_conversions';
    protected $guarded = [];
    /**
     * Relasi: Branch punya banyak User
     */
    public function obat()
    {
        return $this->belongsTo(
            MasterObatModel::class,
            'obat_id',
        );
    }

    public function satuan()
    {
        return $this->belongsTo(
            SatuansModel::class,
            'satuan_id',
        );
    }

}
