<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorModel extends Model
{
    use HasFactory;

    protected $table = 'distributors';

    protected $fillable = [
        'kode',
        'nama',
        'alamat',
        'telepon',
        'email',
        'is_active',
        'uses_manual_po_number',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uses_manual_po_number' => 'boolean',
    ];
}
