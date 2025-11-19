<?php

namespace App\Repositories\Settings\Master;

use App\Models\MainCategoryModel;

class MainCategoryRepository
{
    public function getMainCategory()
    {
        $dataMainCategory = MainCategoryModel::all();
        return $dataMainCategory;
    }
    public function createMainCategory(array $data)
    {
        $dataMainCategory = MainCategoryModel::create($data);
        return $dataMainCategory;
    }

    public function findByIdMainCategory($id)
    {
        $MainCategory = MainCategoryModel::with('category')->find($id);
        return $MainCategory;
    }
}
