<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\ControlledDrugClassification;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananOotService
{
    public const COPY_COUNT = 4;

    /**
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function ootDetails(PembelianModel $purchaseOrder): Collection
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
            $isOot = $classification !== null;

            $detail->setAttribute('is_oot', $isOot);
            $detail->setAttribute('oot_classification', $classification);

            if ($isOot) {
                $detail->setAttribute('quantity_in_words', IndonesianNumber::spell((int) $detail->qty));
            }
        }

        $purchaseOrder->setAttribute(
            'oot_document_number',
            trim((string) $purchaseOrder->no_po).'/OOT'
        );

        return $purchaseOrder->details
            ->filter(fn ($detail) => (bool) $detail->getAttribute('is_oot'))
            ->values();
    }

    public function isOotDrug(?MasterObatModel $medicine): bool
    {
        return $this->matchedClassification($medicine) !== null;
    }

    public function matchedClassification(?MasterObatModel $medicine): ?string
    {
        return ControlledDrugClassification::matchedClassification(
            $medicine,
            ['oot', 'obat-obat tertentu', 'obat tertentu'],
            ['ot', 'otk', 'oot']
        );
    }
}
