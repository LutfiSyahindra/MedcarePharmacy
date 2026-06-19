<?php

namespace App\Repositories\Menu\PembelianPenerimaan;

use App\Models\KonversiSatuanModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel as PembelianModel;

class PembelianRepository
{
    public function getPembelian()
    {
        $dataPembelian = PembelianModel::all();
        return $dataPembelian;
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

    public function findByIdPembelian($id)
    {
        $Pembelian = PembelianModel::with('details', 'details.satuanKonversi.satuan')->find($id);
        return $Pembelian;
    }

    public function updateStatus($id, $status, $approvedBy = null)
    {
        $Pembelian = PembelianModel::findOrFail($id);
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

    public function DetailPembelian($id)
    {
        // Ambil header + distributor + semua detail + relasi obat
        $pembelian = PembelianModel::with([
            'distributor',
            'details.obat',
            'details.satuanKonversi.satuan'
        ])->findOrFail($id);

        // Format data agar mudah dipakai di frontend
        $pembelian->distributor_name = $pembelian->distributor->nama ?? 'Tidak diketahui';

        // Format detail
        $pembelian->details->transform(function ($item) {
            $item->nama_obat = $item->obat->nama_obat ?? 'Tidak diketahui';
            return $item;
        });

        return response()->json($pembelian);
    }

    public function getKonversiSatuan($obatId)
    {
        $KonversiSatuan = KonversiSatuanModel::with('satuan')->where('obat_id', $obatId)->get();
        return $KonversiSatuan;
    }

}
