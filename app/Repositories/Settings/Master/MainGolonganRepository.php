<?php

namespace App\Repositories\Settings\Master;

use App\Models\MainGolonganModel;

class MainGolonganRepository
{
    public function getMainGolongan()
    {
        return MainGolonganModel::all();
    }

    public function createMainGolongan(array $data)
    {
        return MainGolonganModel::create($data);
    }

    public function findByIdMainGolongan($id)
    {
        return MainGolonganModel::with('golongan')->find($id);
    }
}
