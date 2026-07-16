<?php

namespace App\Repositories\Menu\PembelianPenerimaan;

use App\Models\KonversiSatuanModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;

class PembelianRepository
{
    public function getPembelian(?array $branchIds = null)
    {
        $query = PembelianModel::query();

        $this->scopeBranch($query, $branchIds);

        return $query->get();
    }

    public function createPembelian(array $data)
    {
        $dataPembelian = PembelianModel::create($data);

        return $dataPembelian;
    }

    public function createPembelianDetail(array $data)
    {
        $dataPembelianDetail = PembelianDetailModel::create($data);

        return $dataPembelianDetail;
    }

    public function findByIdPembelian($id, ?array $branchIds = null)
    {
        $query = PembelianModel::with('details', 'details.satuanKonversi.satuan');

        $this->scopeBranch($query, $branchIds);

        $Pembelian = $query->find($id);

        return $Pembelian;
    }

    public function updateStatus($id, $status, $approvedBy = null, ?array $branchIds = null)
    {
        $query = PembelianModel::query();

        $this->scopeBranch($query, $branchIds);

        $Pembelian = $query->findOrFail($id);
        $Pembelian->status = $status;

        if ($status === 'approved') {
            $Pembelian->approved_by = $approvedBy;
        }

        if (in_array($status, ['draft', 'waiting_approval', 'rejected'], true)) {
            $Pembelian->approved_by = null;
        }

        $Pembelian->save();

        return $Pembelian;
    }

    public function DetailPembelian($id, ?array $branchIds = null)
    {
        // Ambil header + distributor + semua detail + relasi obat
        $query = PembelianModel::with([
            'distributor',
            'details.obat',
            'details.satuanKonversi.satuan',
        ]);

        $this->scopeBranch($query, $branchIds);

        $pembelian = $query->findOrFail($id);

        // Format data agar mudah dipakai di frontend
        $pembelian->distributor_name = $pembelian->distributor->nama ?? 'Tidak diketahui';

        // Format detail
        $pembelian->details->transform(function ($item) {
            $item->nama_obat = $item->obat->nama_obat ?? 'Tidak diketahui';

            return $item;
        });

        return $pembelian;
    }

    public function getKonversiSatuan($obatId)
    {
        $KonversiSatuan = KonversiSatuanModel::with('satuan')->where('obat_id', $obatId)->get();

        return $KonversiSatuan;
    }

    private function scopeBranch($query, ?array $branchIds): void
    {
        if ($branchIds === null) {
            return;
        }

        if (empty($branchIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('branch_id', $branchIds);
    }
}
