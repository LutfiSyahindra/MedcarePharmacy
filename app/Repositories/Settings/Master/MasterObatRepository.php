<?php

namespace App\Repositories\Settings\Master;

use App\Models\MainGolonganModel;
use App\Models\MasterObatModel;
use App\Models\SubGolonganModel;

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

    public function updateStatus($id, $status)
    {
        $MasterObat = MasterObatModel::find($id);
        $MasterObat->is_active = $status;
        $MasterObat->save();
    }

    public function getMainGolongan($golongan)
    {
        return MainGolonganModel::where('golongan_id', $golongan)->select('id', 'kode', 'nama')->get();
    }

    public function getSubGolongan($mainGolongan)
    {
        return SubGolonganModel::where('main_golongan_id', $mainGolongan)->select('id', 'kode', 'nama')->get();
    }
}
