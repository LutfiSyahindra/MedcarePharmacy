<?php

namespace App\Repositories\Settings\Master;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;

class KonversiSatuanObatRepository
{
    public function getKonversi()
    {
        $dataKonversi = KonversiSatuanModel::with(['obat', 'satuan'])->get();
        return $dataKonversi;
    }

    public function getObatWithKonversi()
    {
        return MasterObatModel::with([
            'satuan',
            'konversiSatuan' => function ($query) {
                $query->with('satuan')
                    ->orderByDesc('is_default')
                    ->orderBy('id');
            },
        ])
            ->orderBy('nama_obat')
            ->get();
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
