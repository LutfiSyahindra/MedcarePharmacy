<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianCompensationModel extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian_compensations';

    protected $guarded = [];

    protected $casts = [
        'tanggal_realisasi' => 'date',
        'nominal' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    public function returPembelian()
    {
        return $this->belongsTo(ReturPembelianModel::class, 'retur_pembelian_id');
    }

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarangModel::class, 'penerimaan_barang_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
