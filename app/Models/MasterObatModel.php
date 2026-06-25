<?php

namespace App\Models;

use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterObatModel extends Model
{
    use HasFactory;

    protected $table = 'master_obats';
    protected $guarded = [];

    public function kategoriUtama()  { return $this->belongsTo(CategoryModel::class, 'category_id'); }
    public function kategori()       { return $this->belongsTo(MainCategoryModel::class, 'main_category_id'); }
    public function subKategori()    { return $this->belongsTo(SubCategoryModel::class, 'sub_kategori_id'); }
    public function golongan()       { return $this->belongsTo(GolonganModel::class, 'golongan_id'); }
    public function satuan()         { return $this->belongsTo(SatuansModel::class, 'satuan_id'); }
    public function sediaan()        { return $this->belongsTo(SediaanModel::class, 'sediaan_id'); }
    public function pabrikan()       { return $this->belongsTo(PabrikanModel::class, 'pabrikan_id'); }
    public function distributor()    { return $this->belongsTo(DistributorModel::class, 'distributor_id'); }
    public function rakPenyimpanan() { return $this->belongsTo(RakPenyimpananModel::class, 'rak_id'); }
    public function konversiSatuan() { return $this->hasMany(KonversiSatuanModel::class, 'obat_id'); }
    public function stokBatches()    { return $this->hasMany(StokBatchModel::class, 'obat_id'); }
    public function kartuStok()      { return $this->hasMany(KartuStokModel::class, 'obat_id'); }
}
