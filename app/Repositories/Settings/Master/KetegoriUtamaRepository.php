<?php

namespace App\Repositories\Settings\Master;

use App\Models\CategoryModel;

class KetegoriUtamaRepository
{
    public function getKategoriUtama()
    {
        $dataKategoriUtama = CategoryModel::all();
        return $dataKategoriUtama;
    }
    public function createKategoriUtama(array $data)
    {
        $dataKategoriUtama = CategoryModel::create($data);
        return $dataKategoriUtama;
    }

    public function findByIdKategoriUtama($id)
    {
        $KategoriUtama = CategoryModel::find($id);
        return $KategoriUtama;
    }
}
