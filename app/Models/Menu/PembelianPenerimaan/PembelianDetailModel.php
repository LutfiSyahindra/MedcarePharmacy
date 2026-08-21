<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetailModel extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_details';

    protected $guarded = [];

    protected $casts = [
        'is_oot' => 'boolean',
        'qty' => 'decimal:2',
        'harga_estimasi' => 'decimal:2',
        'diskon_1' => 'decimal:2',
        'diskon_2' => 'decimal:2',
        'diskon_3' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pembelian()
    {
        return $this->belongsTo(PembelianModel::class);
    }

    public function obat()
    {
        return $this->belongsTo(MasterObatModel::class);
    }

    public function satuanKonversi()
    {
        return $this->belongsTo(
            KonversiSatuanModel::class,
            'satuan_konversi'
        );
    }
}
