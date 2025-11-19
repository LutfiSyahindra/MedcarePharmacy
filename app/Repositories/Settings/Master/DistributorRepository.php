<?php

namespace App\Repositories\Settings\Master;

use App\Models\DistributorModel;

class DistributorRepository
{
    public function getDistributor()
    {
        $dataDistributor = DistributorModel::all();
        return $dataDistributor;
    }
    public function createDistributor(array $data)
    {
        $dataDistributor = DistributorModel::create($data);
        return $dataDistributor;
    }

    public function findByIdDistributor($id)
    {
        $Distributor = DistributorModel::find($id);
        return $Distributor;
    }

    public function updateStatus($id, $status){
        $Distributor = DistributorModel::find($id);
        $Distributor->is_active = $status;
        $Distributor->save();
    }
}
