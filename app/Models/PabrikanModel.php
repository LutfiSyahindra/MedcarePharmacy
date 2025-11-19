<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PabrikanModel extends Model
{
    use HasFactory;
    
    protected $table = "pabrikan";
    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'telepon',
        'email',
        'is_active',
    ];
}
