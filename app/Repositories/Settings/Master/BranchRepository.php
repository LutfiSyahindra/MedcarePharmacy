<?php

namespace App\Repositories\Settings\Master;

use App\Models\BranchModel;

class BranchRepository
{
    /**
     * Create a new class instance.
     */
    public function getBranches()
    {
        $dataBranch = BranchModel::all();
        return $dataBranch;
    }

    public function updateStatus($id, $status)
    {
        $branch = BranchModel::find($id);
        $branch->is_active = $status;
        $branch->save();
    }

    public function findByIdBranch($id)
    {
        $branch = BranchModel::find($id);
        return $branch;
    }

    public function createBranch($data)
    {
        $branch = BranchModel::create($data);
        return $branch;
    }
    
}
