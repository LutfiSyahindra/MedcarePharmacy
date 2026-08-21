<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\ControlledDrugClassification;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananPsikotropikaService
{
    public const COPY_COUNT = 5;

    /**
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function psychotropicDetails(PembelianModel $purchaseOrder): Collection
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
            $isPsychotropic = $classification !== null;

            $detail->setAttribute('is_psychotropic', $isPsychotropic);
            $detail->setAttribute('psychotropic_classification', $classification);

            if ($isPsychotropic) {
                $detail->setAttribute('quantity_in_words', IndonesianNumber::spell((int) $detail->qty));
            }
        }

        $purchaseOrder->setAttribute(
            'psychotropic_document_number',
            trim((string) $purchaseOrder->no_po).'/PSI'
        );

        return $purchaseOrder->details
            ->filter(fn ($detail) => (bool) $detail->getAttribute('is_psychotropic'))
            ->values();
    }

    public function isPsychotropicDrug(?MasterObatModel $medicine): bool
    {
        return $this->matchedClassification($medicine) !== null;
    }

    public function matchedClassification(?MasterObatModel $medicine): ?string
    {
        return ControlledDrugClassification::matchedClassification(
            $medicine,
            ['psikotrop'],
            ['pis', 'psi', 'psk', 'psikotropika']
        );
    }
}
