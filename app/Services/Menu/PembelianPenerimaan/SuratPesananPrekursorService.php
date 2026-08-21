<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\ControlledDrugClassification;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananPrekursorService
{
    public const COPY_COUNT = 4;

    /**
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function precursorDetails(PembelianModel $purchaseOrder): Collection
    {
        $purchaseOrder->loadMissing([
            'details.obat.golongan',
            'details.obat.mainGolongan.golongan',
            'details.obat.subGolongan.mainGolongan.golongan',
            'details.obat.sediaan',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ]);

        foreach ($purchaseOrder->details as $detail) {
            $classification = $this->matchedClassification($detail->obat);
            $isPrecursor = $classification !== null;

            $detail->setAttribute('is_precursor', $isPrecursor);
            $detail->setAttribute('precursor_classification', $classification);

            if ($isPrecursor) {
                $detail->setAttribute('quantity_in_words', IndonesianNumber::spell((int) $detail->qty));
            }
        }

        $purchaseOrder->setAttribute(
            'precursor_document_number',
            trim((string) $purchaseOrder->no_po).'/PRE'
        );

        return $purchaseOrder->details
            ->filter(fn ($detail) => (bool) $detail->getAttribute('is_precursor'))
            ->values();
    }

    public function isPrecursorDrug(?MasterObatModel $medicine): bool
    {
        return $this->matchedClassification($medicine) !== null;
    }

    public function matchedClassification(?MasterObatModel $medicine): ?string
    {
        return ControlledDrugClassification::matchedClassification(
            $medicine,
            ['prekur'],
            ['pr', 'prk', 'pre', 'prekursor']
        );
    }
}
