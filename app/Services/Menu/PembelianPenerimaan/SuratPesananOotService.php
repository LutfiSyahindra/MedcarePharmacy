<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Support\IndonesianNumber;
use Illuminate\Database\Eloquent\Collection;

class SuratPesananOotService
{
    public const COPY_COUNT = 4;

    /**
     * OOT tidak ditentukan dari golongan master obat. Hanya pilihan manual
     * apoteker pada setiap detail PO yang menjadi sumber surat pesanan ini.
     *
     * @return Collection<int, \App\Models\Menu\PembelianPenerimaan\PembelianDetailModel>
     */
    public function ootDetails(PembelianModel $purchaseOrder): Collection
    {
        $purchaseOrder->loadMissing([
            'details.obat.sediaan',
            'details.obat.satuan',
            'details.satuanKonversi.satuan',
        ]);

        foreach ($purchaseOrder->details as $detail) {
            $isOot = (bool) $detail->is_oot;

            $detail->setAttribute('is_oot', $isOot);
            $detail->setAttribute(
                'oot_classification',
                $isOot ? 'Ditentukan manual oleh apoteker pada PO' : null
            );

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
}
