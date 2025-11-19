<?php

namespace App\Repositories\Settings\Master;

use App\Models\SubCategoryModel;

class SubCategoryRepository
{
    /**
     * Create a new class instance.
     */
    public function getSubCategory()
    {
        $dataSubCategory = SubCategoryModel::all();
        return $dataSubCategory;
    }

    public function createSubCategory(array $data)
    {
        $dataSubCategory = SubCategoryModel::create($data);
        return $dataSubCategory;
    }

    public function findByIdSubCategory($id)
    {
        $SubCategory = SubCategoryModel::with('mainCategory')->find($id);
        return $SubCategory;
    }


}
