<?php

namespace App\Repositories\Settings\Master;

use App\Models\MainCategoryModel;
use App\Models\MasterObatModel;
use App\Models\SubCategoryModel;

class MasterObatRepository
{
    public function getMasterObat()
    {
        $dataMasterObat = MasterObatModel::all();
        return $dataMasterObat;
    }
    public function createMasterObat($data)
    {
        $dataMasterObat = MasterObatModel::create($data);
        return $dataMasterObat;
    }

    public function findByIdMasterObat($id)
    {
        $MasterObat = MasterObatModel::find($id);
        return $MasterObat;
    }

    public function updateStatus($id, $status){
        $MasterObat = MasterObatModel::find($id);
        $MasterObat->is_active = $status;
        $MasterObat->save();
    }

    public function getMainKategori($kategoriUtama){
        $mainKategori = MainCategoryModel::where('category_id', $kategoriUtama)->select('id', 'name')->get();
        return $mainKategori;
    }

    public function getSubKategori($kategori){
        $subKategoris = SubCategoryModel::where('main_category_id', $kategori)->select('id', 'name')->get();
        return $subKategoris;
    }



}
