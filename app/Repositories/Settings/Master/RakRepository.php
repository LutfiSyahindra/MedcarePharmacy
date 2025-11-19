<?php

namespace App\Repositories\Settings\Master;

use App\Models\RakPenyimpananModel;

class RakRepository
{
    public function getRak()
    {
        $dataRak = RakPenyimpananModel::all();
        return $dataRak;
    }
    public function createRak(array $data)
    {
        $dataRak = RakPenyimpananModel::create($data);
        return $dataRak;
    }

    public function findByIdRak($id)
    {
        $Rak = RakPenyimpananModel::find($id);
        return $Rak;
    }

    public function updateStatus($id, $status){
        $Rak = RakPenyimpananModel::find($id);
        $Rak->is_active = $status;
        $Rak->save();
    }

}
