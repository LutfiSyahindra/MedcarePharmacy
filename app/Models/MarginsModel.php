<?php

namespace App\Models;

use App\Models\Menu\Stok\StokBatchModel;
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

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function stokBatches()
    {
        return $this->hasMany(StokBatchModel::class, 'margin_id');
    }

    public function getReference()
    {
        return match ($this->tingkat) {
            'kategori' => CategoryModel::find($this->reference_id),
            'golongan' => GolonganModel::find($this->reference_id),
            'main_golongan' => MainGolonganModel::find($this->reference_id),
            'sub_golongan' => SubGolonganModel::find($this->reference_id),
            'obat' => MasterObatModel::find($this->reference_id),
            default => null,
        };
    }

}
