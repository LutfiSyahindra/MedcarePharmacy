<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApotekProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'slogan',
        'logo_path',
        'phone',
        'whatsapp',
        'email',
        'website',
        'instagram',
        'address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'pharmacist_name',
        'pharmacist_license_number',
        'pharmacy_license_number',
        'license_expired_at',
        'tax_id',
        'operational_hours',
        'receipt_footer',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'license_expired_at' => 'date',
            'operational_hours' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset('storage/'.ltrim($this->logo_path, '/'))
            : null;
    }
}
