<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RakPenyimpananModel extends Model
{
    use HasFactory;
    
    protected $table = "rak_penyimpanans";
    protected $fillable = [
        'kode',
        'nama',
        'lokasi',
        'keterangan',
        'is_active',
    ];
}
