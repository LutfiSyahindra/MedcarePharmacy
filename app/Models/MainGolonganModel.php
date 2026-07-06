<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainGolonganModel extends Model
{
    use HasFactory;

    protected $table = 'main_golongan_obats';
    protected $fillable = [
        'golongan_id',
        'kode',
        'nama',
        'keterangan',
    ];

    public function golongan()
    {
        return $this->belongsTo(GolonganModel::class, 'golongan_id');
    }

    public function subGolongan()
    {
        return $this->hasMany(SubGolonganModel::class, 'main_golongan_id');
    }
}
