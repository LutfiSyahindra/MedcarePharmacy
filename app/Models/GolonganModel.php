<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GolonganModel extends Model
{
    use HasFactory;
    
    protected $table = "golongan_obats";
    protected $fillable = [
        'id',
        'kode',
        'nama',
        'keterangan',
        'is_active',
    ];
}
