<?php

namespace App\Repositories\Menu\PembelianPenerimaan;

use App\Models\KonversiSatuanModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;

class PembelianRepository
{
    public function getPembelian(?array $branchIds = null, ?string $medicineSearch = null, ?int $medicineId = null, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $query = PembelianModel::query();

        $this->scopeBranch($query, $branchIds);

        $query->when($dateStart, fn ($query) => $query->where('tanggal_po', '>=', $dateStart))
            ->when($dateEnd, fn ($query) => $query->where('tanggal_po', '<=', $dateEnd));

        $medicineSearch = trim($medicineSearch ?? '');

        if ($medicineId !== null || $medicineSearch !== '') {
            if ($medicineId !== null) {
                $medicineFilter = fn ($medicineQuery) => $medicineQuery->whereKey($medicineId);
            } else {
                $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($medicineSearch)).'%';
                $medicineFilter = fn ($medicineQuery) => $medicineQuery->where(function ($searchQuery) use ($pattern) {
                    $searchQuery->whereRaw("LOWER(nama_obat) LIKE ? ESCAPE '!'", [$pattern])
                        ->orWhereRaw("LOWER(kode_obat) LIKE ? ESCAPE '!'", [$pattern]);
                });
            }

            $query->whereHas('details.obat', $medicineFilter)
                ->with([
                    'details' => fn ($detailQuery) => $detailQuery->whereHas('obat', $medicineFilter)
                        ->with(['obat.satuan', 'satuanKonversi.satuan']),
                ]);
        }

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

    public function findByIdPembelian($id, ?array $branchIds = null, bool $lockForUpdate = false)
    {
        $query = PembelianModel::with('details', 'details.satuanKonversi.satuan')
            ->withExists([
                'penerimaanBarang as has_receipt',
                'penerimaanBarang as has_non_draft_receipt' => fn ($query) => $query
                    ->where(fn ($receipts) => $receipts->where('status', '!=', 'draft')->orWhereNotNull('posted_at')),
            ]);

        $this->scopeBranch($query, $branchIds);

        $Pembelian = $query->when($lockForUpdate, fn ($query) => $query->lockForUpdate())->find($id);

        if ($Pembelian && $lockForUpdate) {
            // Re-read receipt states under a lock after acquiring the PO lock, so a
            // posting that just committed cannot be missed by a transaction snapshot.
            $receipts = $Pembelian->penerimaanBarang()->lockForUpdate()->get(['id', 'status', 'posted_at']);
            $Pembelian->setAttribute('has_receipt', $receipts->isNotEmpty());
            $Pembelian->setAttribute('has_non_draft_receipt', $receipts
                ->contains(fn ($receipt) => $receipt->status !== 'draft' || $receipt->posted_at !== null));
            $Pembelian->syncOriginalAttributes(['has_receipt', 'has_non_draft_receipt']);
        }

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
            'branch.apotekProfile',
            'details.obat.golongan',
            'details.obat.mainGolongan.golongan',
            'details.obat.subGolongan.mainGolongan.golongan',
            'details.obat.sediaan',
            'details.obat.satuan',
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
        $KonversiSatuan = KonversiSatuanModel::with('satuan')
            ->where('obat_id', $obatId)
            ->orderByDesc('is_default')
            ->orderBy('konversi')
            ->orderBy('id')
            ->get();

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
