<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\DistributorModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBarangModel extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_barang';

    protected $guarded = [];

    protected $casts = [
        'tanggal_penerimaan' => 'date',
        'tanggal_faktur' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'supplier_compensation_discount' => 'decimal:2',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(PenerimaanBarangDetailModel::class, 'penerimaan_barang_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PembelianModel::class, 'purchase_order_id');
    }

    public function supplierCompensationAllocations()
    {
        return $this->hasMany(
            PenerimaanSupplierCompensationAllocationModel::class,
            'penerimaan_barang_id'
        );
    }

    public function distributor()
    {
        return $this->belongsTo(DistributorModel::class, 'distributor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
