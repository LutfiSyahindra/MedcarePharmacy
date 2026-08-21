<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananRegulerService
{
    public const COPY_COUNT = 2;

    public function __construct(
        private readonly SuratPesananNarkotikaService $narcoticOrders,
        private readonly SuratPesananPsikotropikaService $psychotropicOrders,
        private readonly SuratPesananPrekursorService $precursorOrders,
    ) {}

    /**
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function regularDetails(PembelianModel $purchaseOrder): Collection
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
            $isRegular = ! (bool) $detail->is_oot
                && $this->isRegularDrug($detail->obat);

            $detail->setAttribute('is_regular', $isRegular);
            $detail->setAttribute(
                'regular_classification',
                $isRegular ? 'Selain Narkotika, Psikotropika, dan Prekursor' : null
            );

            if ($isRegular) {
                $detail->setAttribute('quantity_in_words', IndonesianNumber::spell((int) $detail->qty));
            }
        }

        $purchaseOrder->setAttribute(
            'regular_document_number',
            trim((string) $purchaseOrder->no_po).'/REG'
        );

        return $purchaseOrder->details
            ->filter(fn ($detail) => (bool) $detail->getAttribute('is_regular'))
            ->values();
    }

    public function isRegularDrug(?MasterObatModel $medicine): bool
    {
        return $medicine !== null
            && ! $this->narcoticOrders->isNarcoticDrug($medicine)
            && ! $this->psychotropicOrders->isPsychotropicDrug($medicine)
            && ! $this->precursorOrders->isPrecursorDrug($medicine);
    }
}
