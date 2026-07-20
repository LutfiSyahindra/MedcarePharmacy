<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\DistributorModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturPembelianModel extends Model
{
    use HasFactory;

    protected $table = 'retur_pembelian';

    protected $guarded = [];

    protected $casts = [
        'tanggal_retur' => 'date',
        'expects_compensation' => 'boolean',
        'compensation_due_date' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = [
        'compensation_expected_value',
        'compensation_received_value',
        'compensation_outstanding_value',
        'compensation_status',
    ];

    public function details()
    {
        return $this->hasMany(ReturPembelianDetailModel::class, 'retur_pembelian_id');
    }

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarangModel::class, 'penerimaan_barang_id');
    }

    public function compensations()
    {
        return $this->hasMany(ReturPembelianCompensationModel::class, 'retur_pembelian_id');
    }

    public function activeCompensations()
    {
        return $this->compensations()->whereNull('cancelled_at');
    }

    public function getCompensationExpectedValueAttribute(): float
    {
        if (! $this->expects_compensation || $this->status === 'cancelled') {
            return 0;
        }

        return round((float) $this->grand_total, 2);
    }

    public function getCompensationReceivedValueAttribute(): float
    {
        $compensations = $this->relationLoaded('compensations')
            ? $this->compensations->whereNull('cancelled_at')
            : $this->activeCompensations()->get();

        return round((float) $compensations->sum('nominal'), 2);
    }

    public function getCompensationOutstandingValueAttribute(): float
    {
        return max(0, round($this->compensation_expected_value - $this->compensation_received_value, 2));
    }

    public function getCompensationStatusAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if (! $this->expects_compensation) {
            return 'not_required';
        }

        if ($this->status !== 'posted') {
            return 'not_started';
        }

        if ($this->compensation_outstanding_value <= 0.009) {
            return 'settled';
        }

        if ($this->compensation_due_date?->isBefore(now()->startOfDay())) {
            return 'overdue';
        }

        return $this->compensation_received_value > 0 ? 'partial' : 'waiting';
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PembelianModel::class, 'purchase_order_id');
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
