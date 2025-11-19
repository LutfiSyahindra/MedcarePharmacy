<?php

namespace App\Repositories\Settings\Master;

use App\Models\SatuansModel;

class SatuanRepository
{
    /**
     * Create a new class instance.
     */
    public function getSatuan()
    {
        $dataSatuan = SatuansModel::all();
        return $dataSatuan;
    }
    public function createSatuan(array $data)
    {
        $dataSatuan = SatuansModel::create($data);
        return $dataSatuan;
    }

    public function findByIdSatuan($id)
    {
        $Satuan = SatuansModel::find($id);
        return $Satuan;
    }

    public function updateStatus($id, $status){
        $satuan = SatuansModel::find($id);
        $satuan->is_active = $status;
        $satuan->save();
    }
}
