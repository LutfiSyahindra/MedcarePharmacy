<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPesananSetting extends Model
{
    public const TYPE_REGULAR = 'regular';

    public const TYPE_NARCOTIC = 'narkotika';

    public const TYPE_PSYCHOTROPIC = 'psikotropika';

    public const TYPE_OOT = 'oot';

    public const TYPE_PRECURSOR = 'prekursor';

    public const TYPES = [
        self::TYPE_REGULAR,
        self::TYPE_NARCOTIC,
        self::TYPE_PSYCHOTROPIC,
        self::TYPE_OOT,
        self::TYPE_PRECURSOR,
    ];

    public const TYPE_LABELS = [
        self::TYPE_REGULAR => 'Reguler',
        self::TYPE_NARCOTIC => 'Narkotika',
        self::TYPE_PSYCHOTROPIC => 'Psikotropika',
        self::TYPE_OOT => 'OOT',
        self::TYPE_PRECURSOR => 'Prekursor',
    ];

    protected $fillable = [
        'golongan_id',
        'main_golongan_id',
        'sub_golongan_id',
        'sp_type',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => \App\Support\ControlledDrugClassification::flushSettingsCache());
        static::deleted(fn () => \App\Support\ControlledDrugClassification::flushSettingsCache());
    }

    public function golongan()
    {
        return $this->belongsTo(GolonganModel::class, 'golongan_id');
    }

    public function mainGolongan()
    {
        return $this->belongsTo(MainGolonganModel::class, 'main_golongan_id');
    }

    public function subGolongan()
    {
        return $this->belongsTo(SubGolonganModel::class, 'sub_golongan_id');
    }

    public static function foreignKeyForLevel(string $level): ?string
    {
        return match ($level) {
            'golongan' => 'golongan_id',
            'main_golongan' => 'main_golongan_id',
            'sub_golongan' => 'sub_golongan_id',
            default => null,
        };
    }
}
