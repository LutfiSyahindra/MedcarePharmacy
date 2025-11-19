<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SediaanModel extends Model
{
    use HasFactory;
    
    protected $table = "sediaan_obats";
    protected $fillable = [
        'id',
        'kode',
        'nama',
        'keterangan',
        'is_active',
    ];
}
