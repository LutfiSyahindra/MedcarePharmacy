<?php

namespace App\Repositories\Settings\Master;

use App\Models\PabrikanModel;

class PabrikanRepository
{
    public function getPabrikan()
    {
        $dataPabrikan = PabrikanModel::all();
        return $dataPabrikan;
    }
    public function createPabrikan(array $data)
    {
        $dataPabrikan = PabrikanModel::create($data);
        return $dataPabrikan;
    }

    public function findByIdPabrikan($id)
    {
        $Pabrikan = PabrikanModel::find($id);
        return $Pabrikan;
    }

    public function updateStatus($id, $status){
        $Pabrikan = PabrikanModel::find($id);
        $Pabrikan->is_active = $status;
        $Pabrikan->save();
    }
}
