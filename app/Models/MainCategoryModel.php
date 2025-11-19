<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainCategoryModel extends Model
{
    use HasFactory;

    protected $table = 'main_category';
    protected $fillable = [
        'code',
        'name',
        'category_id',
    ];

    public function subCategories()
    {
        return $this->hasMany(SubCategoryModel::class);
    }

    public function category()
    {
        return $this->belongsTo(CategoryModel::class);
    }
}
