<?php

namespace App\Repositories\Settings\Master;

use App\Models\SediaanModel;

class SediaanRepository
{
    /**
     * Create a new class instance.
     */
    public function getSediaan()
    {
        $dataSediaan = SediaanModel::all();
        return $dataSediaan;
    }
    public function createSediaan(array $data)
    {
        $dataSediaan = SediaanModel::create($data);
        return $dataSediaan;
    }

    public function findByIdSediaan($id)
    {
        $Sediaan = SediaanModel::find($id);
        return $Sediaan;
    }

    public function updateStatus($id, $status){
        $Sediaan = SediaanModel::find($id);
        $Sediaan->is_active = $status;
        $Sediaan->save();
    }
}
