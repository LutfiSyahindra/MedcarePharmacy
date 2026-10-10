<?php

namespace App\Services\Menu\PembelianPenerimaan;

use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Repositories\Menu\PembelianPenerimaan\PembelianRepository;
use Illuminate\Validation\ValidationException;

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
    public function getPembelian(?array $branchIds = null)
    {
        $dataPembelian = $this->PembelianRepository->getPembelian($branchIds);

        return $dataPembelian;
    }

    public function generatePo()
    {
        $now = now();
        $tahun = $now->format('Y');
        $bulan = $now->format('m');
        $prefix = 'PO-'.$tahun.'-'.$bulan.'-';
        $lastNumber = PembelianModel::query()
            ->where('no_po', 'like', $prefix.'%')
            ->pluck('no_po')
            ->map(static function (string $number) use ($prefix): int {
                $sequence = substr($number, strlen($prefix));

                return ctype_digit($sequence) ? (int) $sequence : 0;
            })
            ->max() ?? 0;
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        $noPo = $prefix.$nextNumber;

        return $noPo;
    }

    public function getPembelianTable(?array $branchIds = null, ?string $medicineSearch = null, ?int $medicineId = null)
    {
        $Pembelian = $this->PembelianRepository->getPembelian($branchIds, $medicineSearch, $medicineId)->load([
            'distributor',
            'createdBy',
            'approvedBy',
            'branch',
        ]);
        $Pembelian->loadCount('details')->loadSum('details', 'qty');
        $Pembelian->loadExists([
            'penerimaanBarang as has_receipt',
            'penerimaanBarang as has_draft_receipt' => fn ($query) => $query->where('status', 'draft'),
        ]);

        $dataPembelian = [];
        foreach ($Pembelian as $r) {
            $dataPembelian[] = [
                'id' => $r->id,
                'no_po' => $r->no_po,
                'branch_key' => (int) $r->branch_id,
                'branch_id' => $r->branch->name ?? '-',
                'distributor_id' => $r->distributor->nama ?? '-',
                'tanggal_po' => $r->tanggal_po ?? '-',
                'item_count' => (int) ($r->details_count ?? 0),
                'total_qty' => (float) ($r->details_sum_qty ?? 0),
                'matched_medicines' => ($medicineId !== null || trim($medicineSearch ?? '') !== '') && $r->relationLoaded('details')
                    ? $r->details->map(fn ($detail) => [
                        'obat_id' => (int) $detail->obat_id,
                        'kode_obat' => $detail->obat->kode_obat,
                        'nama_obat' => $detail->obat->nama_obat,
                        'qty' => (float) $detail->qty,
                        'satuan' => $detail->satuanKonversi?->satuan?->nama ?? $detail->obat->satuan?->nama ?? '-',
                    ])->values()->all()
                    : [],
                'total_estimasi' => $r->total_estimasi ?? '-',
                'status' => $r->has_draft_receipt ? 'dalam_penerimaan' : ($r->status ?? '-'),
                'purchase_order_status' => $r->status,
                'has_receipt' => (bool) $r->has_receipt,
                'catatan' => $r->catatan ?? '-',
                'created_by' => $r->createdBy->name ?? '-',
                'approved_by' => $r->approvedBy->name ?? null,
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

    public function findByIdPembelian($id, ?array $branchIds = null)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds);

        return $Pembelian;
    }

    public function DetailPembelian($id, ?array $branchIds = null)
    {
        return $this->PembelianRepository->DetailPembelian($id, $branchIds);
    }

    public function updatePembelian($id, array $data, ?array $branchIds = null)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds);
        $Pembelian->update($data);

        return $Pembelian;
    }

    public function updateStatus($id, $status, $approvedBy = null, ?array $branchIds = null)
    {
        if ($status === 'approved') {
            $purchaseOrder = $this->PembelianRepository->findByIdPembelian($id, $branchIds);
            $errors = [];

            foreach ($purchaseOrder?->details ?? [] as $index => $detail) {
                $conversion = $detail->satuanKonversi;

                if (! $conversion || (int) $conversion->obat_id !== (int) $detail->obat_id
                    || (float) $conversion->konversi <= 0 || ! $conversion->satuan) {
                    $errors['satuan_id.'.$index] = 'Lengkapi satuan konversi untuk item obat ke-'.($index + 1).'. PO belum dapat diproses.';
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        }

        return $this->PembelianRepository->updateStatus($id, $status, $approvedBy, $branchIds);
    }

    public function deletePembelian($id, ?array $branchIds = null)
    {
        $Pembelian = $this->PembelianRepository->findByIdPembelian($id, $branchIds);

        if (! $Pembelian) {
            return false;
        }

        return $Pembelian->delete();
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
