<?php

namespace App\Repositories\Settings\Margins;

use App\Models\MarginsModel;
use App\Models\Menu\Stok\StokBatchModel;

class MarginsRepository
{
    /**
     * Create a new class instance.
     */
    public function getMargins()
    {
        $margins = $this->queryWithUsage()->get();
        return $margins;
    }
    public function createMargins(array $data)
    {
        $margins = MarginsModel::create($data);
        return $margins;
    }

    public function findByIdMargins($id)
    {
        $margins = $this->queryWithUsage()->findOrFail($id);
        if ($margins) {
            $reference = $margins->getReference();
            $margins->reference_text = $reference?->nama ?? $reference?->name ?? $reference?->nama_obat ?? 'Tanpa Nama';
        }
        return $margins;
    }

    private function queryWithUsage()
    {
        return MarginsModel::query()
            ->withCount('stokBatches as used_batch_count')
            ->selectSub(
                StokBatchModel::query()->selectRaw('COUNT(DISTINCT obat_id)')
                    ->whereColumn('margin_id', 'margins.id'),
                'used_product_count'
            );
    }

    public function updateStatus($id, $status){
        $margin = MarginsModel::find($id);
        $margin->is_active = $status;
        $margin->save();
    }
}
