<?php

namespace App\Repositories\Settings\Margins;

use App\Models\MarginsModel;

class MarginsRepository
{
    /**
     * Create a new class instance.
     */
    public function getMargins()
    {
        $margins = MarginsModel::all();
        return $margins;
    }
    public function createMargins(array $data)
    {
        $margins = MarginsModel::create($data);
        return $margins;
    }

    public function findByIdMargins($id)
    {
        $margins = MarginsModel::find($id);
        if ($margins) {
            $reference = $margins->getReference();
            $margins->reference_text = $reference?->nama ?? $reference?->name ?? $reference?->nama_obat ?? 'Tanpa Nama';
        }
        return $margins;
    }

    public function updateStatus($id, $status){
        $margin = MarginsModel::find($id);
        $margin->is_active = $status;
        $margin->save();
    }
}
