<?php

namespace App\Models\Menu\PembelianPenerimaan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanSupplierCompensationAllocationModel extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_supplier_compensation_allocations';

    protected $guarded = [];

    protected $casts = [
        'nominal' => 'decimal:2',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarangModel::class, 'penerimaan_barang_id');
    }

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelianModel::class, 'retur_pembelian_id');
    }

    public function compensation()
    {
        return $this->belongsTo(
            ReturPembelianCompensationModel::class,
            'retur_pembelian_compensation_id'
        );
    }
}
