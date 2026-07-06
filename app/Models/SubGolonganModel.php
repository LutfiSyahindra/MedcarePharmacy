<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubGolonganModel extends Model
{
    use HasFactory;

    protected $table = 'sub_golongan_obats';
    protected $fillable = [
        'main_golongan_id',
        'kode',
        'nama',
        'keterangan',
    ];

    public function mainGolongan()
    {
        return $this->belongsTo(MainGolonganModel::class, 'main_golongan_id');
    }
}
