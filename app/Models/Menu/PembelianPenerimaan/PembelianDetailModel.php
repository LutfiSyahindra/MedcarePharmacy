<?php

namespace App\Models\Menu\PembelianPenerimaan;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetailModel extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_details';

    protected $guarded = [];

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
