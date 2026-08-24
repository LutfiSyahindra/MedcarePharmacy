<?php

namespace App\Models\Menu\Stok;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameLogModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_logs';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    public function opname() { return $this->belongsTo(StockOpnameModel::class, 'stock_opname_id'); }
    public function performer() { return $this->belongsTo(User::class, 'performed_by'); }
}
