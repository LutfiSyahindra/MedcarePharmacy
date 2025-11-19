<?php

namespace App\Services\Settings\Master;

use App\Repositories\Settings\Master\BranchRepository;

class BranchService
{
    protected $BranchRepository;

    public function __construct(BranchRepository $BranchRepository)
    {
        $this->BranchRepository = $BranchRepository;
    }
    
    public function getBranches()
    {
        $branches = $this->BranchRepository->getBranches();

        $dataBranch = [];
        foreach ($branches as $r) {
            $dataBranch[] = [
                'id'        => $r->id,
                'name'      => $r->name,
                'code'      => $r->code,
                'address'   => $r->address,
                'phone'     => $r->phone,
                'email'     => $r->email,
                'is_active' => $r->is_active,
            ];
        }

        return $dataBranch;
    }

    public function updateStatus($id, $status){
        return $this->BranchRepository->updateStatus($id, $status);
    }

    public function editBranch($id, array $data)
    {
        $branch = $this->BranchRepository->findByIdBranch($id);

        if (!$branch) {
            throw new \Exception('Branch not found');
        }

        $branch->update($data);

        return $branch;
    }

    public function findBranch($id)
    {
        return $this->BranchRepository->findByIdBranch($id);
    }

    public function createBranch($data)
    {
        return $this->BranchRepository->createBranch($data);
    }

    public function deleteBranch($id)
    {
        return $this->BranchRepository->findByIdBranch($id)->delete();
    }

}
