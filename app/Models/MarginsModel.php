<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarginsModel extends Model
{
    use HasFactory;
    
    protected $table = "margins";
    protected $fillable = [
        'reference_id',
        'faktor_jual',
        'tingkat',
        'is_active',
    ];

    public function getReference()
    {
        return match ($this->tingkat) {
            'kategori' => MainCategoryModel::find($this->reference_id),
            'sub_kategori' => SubCategoryModel::find($this->reference_id),
            'kategoriUtama' => CategoryModel::find($this->reference_id),
            // 'obat' => \App\Models\Obat::find($this->reference_id),
            default => null,
        };
    }

}
