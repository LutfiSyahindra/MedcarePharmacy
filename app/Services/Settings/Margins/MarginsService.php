<?php

namespace App\Services\Settings\Margins;

use App\Models\CategoryModel;
use App\Models\MainCategoryModel;
use App\Models\SubCategoryModel;
use App\Repositories\Settings\Margins\MarginsRepository;

class MarginsService
{
    /**
     * Create a new class instance.
     */
     protected $MarginsRepository;

    public function __construct(MarginsRepository $MarginsRepository)
    {
        $this->MarginsRepository = $MarginsRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getMargins()
    {
        $dataMargins = $this->MarginsRepository->getMargins();
        return $dataMargins;
    }

    public function getMarginsTable()
    {
        $Margins = $this->MarginsRepository->getMargins();

        $dataMargins = [];
        foreach ($Margins as $r) {
            $reference = $r->getReference();
            $persentase = ($r->faktor_jual - 1) * 100;
            $dataMargins[] = [
                'id'            => $r->id,
                'reference_id'  => $reference?->name ?? '-', // gunakan safe operator
                'faktor_jual'   => $r->faktor_jual,
                'persentase'    => number_format($persentase) . '%',
                'tingkat'       => $r->tingkat,
                'is_active'     => $r->is_active,
            ];
        }

        return $dataMargins;
    }


    public function createMargins(array $data)
    {
        $dataMargins = $this->MarginsRepository->createMargins($data);
        return $dataMargins;
    }

    public function findByIdMargins($id)
    {
        $Margins = $this->MarginsRepository->findByIdMargins($id);
        return $Margins;
    }

    public function updateMargins($id, array $data)
    {
        $Margins = $this->MarginsRepository->findByIdMargins($id);
        unset($Margins->reference_text);
        $Margins->update($data);
        return $Margins;
    }

    public function deleteMargins($id)
    {
        return $this->MarginsRepository->findByIdMargins($id)->delete();
    }

    public function getReferences($tingkat)
    {
        switch ($tingkat) {
            case 'kategoriUtama':
                $data = CategoryModel::select('id', 'name')->get();
                break;
            case 'kategori':
                $data = MainCategoryModel::select('id', 'name')->get();
                break;
            case 'sub_kategori':
                $data = SubCategoryModel::select('id', 'name')->get();
                break;
            // case 'obat':
            //     $data = \App\Models\Obat::select('id', 'nama')->get();
            //     break;
            default:
                $data = collect();
                break;
        }
        return $data;
    }

    public function updateStatus($id, $status)
    {
        return $this->MarginsRepository->updateStatus($id, $status);
    }

}
