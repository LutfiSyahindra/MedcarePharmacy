<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel as PembelianModel;
use App\Repositories\Menu\PembelianPenerimaan\PembelianRepository as PembelianRepository;

class PembelianService
{
    protected $PembelianRepository;


    public function __construct(PembelianRepository $PembelianRepository)
    {
        $this->PembelianRepository = $PembelianRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getPembelian()
    {
        $dataPembelian = $this->PembelianRepository->getPembelian();
        return $dataPembelian;
    }

    public function generatePo()
    {
        $tahun = date('Y');
        $bulan = date('m');

        $lastPo = PembelianModel::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->orderBy('id', 'desc')
            ->first();

        $lastNumber = $lastPo ? intval(substr($lastPo->no_po, -4)) : 0;
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        $noPo = 'PO-' . $tahun . '-' . $bulan . '-' . $nextNumber;

        return $noPo;
    }
    public function getPembelianTable()
    {
        $Pembelian = $this->PembelianRepository->getPembelian()->load(['distributor', 'createdBy', 'approvedBy', 'branch']);

        $dataPembelian = [];
        foreach ($Pembelian as $r) {
            $dataPembelian[] = [
                'id'            => $r->id,
                'no_po'         => $r->no_po,
                'branch_id'     => $r->branch->name??'-',
                'distributor_id'=> $r->distributor->nama??'-',
                'tanggal_po'    => $r->tanggal_po ?? '-',
                'total_estimasi'=> $r->total_estimasi ?? '-',
                'status'        => $r->status ?? '-',
                'catatan'       => $r->catatan ?? '-',
                'created_by'    => $r->createdBy->name ?? '-',
                'approved_by'   => $r->approvedBy->name ?? null,
            ];
        }

        return $dataPembelian;
    }

    public function createPembelian(array $data)
    {
        $dataPembelian = $this->PembelianRepository->createPembelian($data);
        return $dataPembelian;
    }

    public function createPembelianDetail(array $data)
    {
        $dataPembelianDetail = $this->PembelianRepository->createPembelianDetail($data);
        return $dataPembelianDetail;
    }

    public function findByIdPembelian($id)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id);
        return $Pembelian;
    }

    public function DetailPembelian($id)
    {
        return $this->PembelianRepository->DetailPembelian($id);
    }

    public function updatePembelian($id, array $data)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id);
        $Pembelian->update($data);
        return $Pembelian;
    }

    public function updateStatus($id, $status, $approvedBy = null)
    {
        return $this->PembelianRepository->updateStatus($id, $status, $approvedBy);
    }

    public function deletePembelian($id)
    {
        return $this->PembelianRepository->findByIdPembelian($id)->delete();
    }

    public function deletePembelianDetail($poId)
    {
        return PembelianDetailModel::where('purchase_order_id', $poId)->delete();
    }

    public function getKonversiSatuan($obatId)
    {
        $KonversiSatuan = $this->PembelianRepository->getKonversiSatuan($obatId);
        return $KonversiSatuan;
    }


}
