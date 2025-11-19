<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategoryModel extends Model
{
    use HasFactory;
    
    protected $table = "sub_categories";
    protected $fillable = [
        'main_category_id',
        'code',
        'name',
    ];

    public function mainCategory()
    {
        return $this->belongsTo(MainCategoryModel::class, 'main_category_id');
    }
}
