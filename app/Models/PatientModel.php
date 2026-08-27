<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientModel extends Model
{
    use HasFactory;

    protected $table = 'patients';

    protected $fillable = [
        'branch_id',
        'name',
        'phone',
        'created_by',
        'updated_by',
    ];

    public static function normalizePhone(?string $phone): string
    {
        $normalized = preg_replace('/\D+/', '', trim((string) $phone)) ?: '';

        if (str_starts_with($normalized, '62')) {
            return '0'.substr($normalized, 2);
        }

        return $normalized;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(\App\Models\Menu\Penjualan\PenjualanTransactionModel::class, 'patient_id');
    }
}
