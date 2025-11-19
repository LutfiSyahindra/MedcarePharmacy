<?php

namespace App\Repositories\Settings\Master;

use App\Models\GolonganModel;

class GolonganRepository
{
    /**
     * Create a new class instance.
     */
    public function getGolongan()
    {
        $dataGolongan = GolonganModel::all();
        return $dataGolongan;
    }
    public function createGolongan(array $data)
    {
        $dataGolongan = GolonganModel::create($data);
        return $dataGolongan;
    }

    public function findByIdGolongan($id)
    {
        $Golongan = GolonganModel::find($id);
        return $Golongan;
    }

    public function updateStatus($id, $status){
        $Golongan = GolonganModel::find($id);
        $Golongan->is_active = $status;
        $Golongan->save();
    }
}
