<?php

namespace App\Models\Menu\Stok;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameMovementModel extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_movements';

    protected $guarded = [];

    protected $casts = [
        'qty_masuk' => 'decimal:2',
        'qty_keluar' => 'decimal:2',
        'saldo_batch' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    public function opname() { return $this->belongsTo(StockOpnameModel::class, 'stock_opname_id'); }
    public function detail() { return $this->belongsTo(StockOpnameDetailModel::class, 'stock_opname_detail_id'); }
    public function kartuStok() { return $this->belongsTo(KartuStokModel::class, 'kartu_stok_id'); }
    public function batch() { return $this->belongsTo(StokBatchModel::class, 'stok_batch_id'); }
}
