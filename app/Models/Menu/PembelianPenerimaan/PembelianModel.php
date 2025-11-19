<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianModel extends Model
{
    use HasFactory;

    // ✅ Sesuaikan dengan nama tabel yang benar di migrasi (plural)
    protected $table = 'purchase_orders';

    // ✅ Semua kolom bisa diisi (gunakan guarded kosong)
    protected $guarded = [];

    /**
     * Relasi ke detail purchase order
     */
    public function details()
    {
        return $this->hasMany(PembelianDetailModel::class, 'purchase_order_id');
    }

    /**
     * Relasi ke distributor
     */
    public function distributor()
    {
        return $this->belongsTo(DistributorModel::class, 'distributor_id');
    }

    /**
     * Relasi ke user yang membuat (created_by)
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke user yang menyetujui (approved_by)
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke cabang (branch)
     */
    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
}
