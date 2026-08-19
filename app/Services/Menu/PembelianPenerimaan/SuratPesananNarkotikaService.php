<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\ControlledDrugClassification;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananNarkotikaService
{
    public const COPY_COUNT = 3;

    /**
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function narcoticDetails(PembelianModel $purchaseOrder): Collection
    {
        $purchaseOrder->loadMissing([
            'details.obat.golongan',
            'details.obat.mainGolongan.golongan',
            'details.obat.subGolongan.mainGolongan.golongan',
            'details.obat.sediaan',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ]);

        $sequence = 0;

        foreach ($purchaseOrder->details as $detail) {
            $isNarcotic = $this->isNarcoticDrug($detail->obat);

            $detail->setAttribute('is_narcotic', $isNarcotic);
            $detail->setAttribute('narcotic_classification', $this->matchedClassification($detail->obat));

            if (! $isNarcotic) {
                continue;
            }

            $sequence++;
            $detail->setAttribute('narcotic_document_number', $this->documentNumber($purchaseOrder, $sequence));
            $detail->setAttribute('quantity_in_words', $this->quantityInWords((int) $detail->qty));
        }

        return $purchaseOrder->details
            ->filter(fn ($detail) => (bool) $detail->getAttribute('is_narcotic'))
            ->values();
    }

    public function isNarcoticDrug(?MasterObatModel $medicine): bool
    {
        return $this->matchedClassification($medicine) !== null;
    }

    public function matchedClassification(?MasterObatModel $medicine): ?string
    {
        return ControlledDrugClassification::matchedClassification(
            $medicine,
            ['narkot'],
            ['nar', 'nark', 'narkotika']
        );
    }

    public function quantityInWords(int $quantity): string
    {
        return IndonesianNumber::spell($quantity);
    }

    private function documentNumber(PembelianModel $purchaseOrder, int $sequence): string
    {
        return trim((string) $purchaseOrder->no_po).'/NAR/'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }
}
