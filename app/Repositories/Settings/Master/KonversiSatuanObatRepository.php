<?php

namespace App\Repositories\Settings\Master;

use App\Models\KonversiSatuanModel;

class KonversiSatuanObatRepository
{
    public function getKonversi()
    {
        $dataKonversi = KonversiSatuanModel::all();
        return $dataKonversi;
    }

    public function updateStatus($id, $status)
    {
        $Konversi = KonversiSatuanModel::find($id);
        $Konversi->is_active = $status;
        $Konversi->save();
    }

    public function findByIdKonversi($id)
    {
        $Konversi = KonversiSatuanModel::find($id);
        return $Konversi;
    }

    public function createKonversi($data)
    {
        $Konversi = KonversiSatuanModel::create($data);
        return $Konversi;
    }
}
