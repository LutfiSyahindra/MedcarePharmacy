<?php

namespace App\Models\Menu\Stok;

use App\Models\BranchModel;
use App\Models\RakPenyimpananModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameModel extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_COUNTING = 'counting';

    public const STATUS_AWAITING_VERIFICATION = 'awaiting_verification';

    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_ADJUSTED = 'adjusted';

    public const MODE_FREEZE = 'freeze';

    public const MODE_RECORD = 'record';

    protected $table = 'stock_opnames';

    protected $guarded = [];

    protected $casts = [
        'tanggal_opname' => 'date',
        'posting_summary' => 'array',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'adjusted_at' => 'datetime',
    ];

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_COUNTING,
            self::STATUS_AWAITING_VERIFICATION,
            self::STATUS_AWAITING_APPROVAL,
            self::STATUS_APPROVED,
        ];
    }

    public static function lockingStatuses(): array
    {
        return [self::STATUS_COUNTING];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_COUNTING => 'Proses Penghitungan',
            self::STATUS_AWAITING_VERIFICATION => 'Review Selisih',
            self::STATUS_AWAITING_APPROVAL => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_ADJUSTED => 'Penyesuaian Stok',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function rack()
    {
        return $this->belongsTo(RakPenyimpananModel::class, 'rak_id');
    }

    public function details()
    {
        return $this->hasMany(StockOpnameDetailModel::class, 'stock_opname_id');
    }

    public function movements()
    {
        return $this->hasMany(StockOpnameMovementModel::class, 'stock_opname_id');
    }

    public function logs()
    {
        return $this->hasMany(StockOpnameLogModel::class, 'stock_opname_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function starter()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjuster()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
