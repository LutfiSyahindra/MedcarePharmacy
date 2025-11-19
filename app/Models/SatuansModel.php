<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatuansModel extends Model
{
    use HasFactory;
    
    protected $table = "satuans";
    protected $fillable = [
        'id',
        'kode',
        'nama',
        'is_active',
    ];

    public function konversi()
    {
        return $this->hasMany(KonversiSatuanModel::class, 'satuan_id');
    }
}
