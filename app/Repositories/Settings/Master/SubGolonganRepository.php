<?php

namespace App\Repositories\Settings\Master;

use App\Models\SubGolonganModel;

class SubGolonganRepository
{
    public function getSubGolongan()
    {
        return SubGolonganModel::all();
    }

    public function createSubGolongan(array $data)
    {
        return SubGolonganModel::create($data);
    }

    public function findByIdSubGolongan($id)
    {
        return SubGolonganModel::with('mainGolongan')->find($id);
    }
}
