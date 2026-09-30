<?php

namespace Tests\Unit;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Services\Menu\Penjualan\PenjualanPosService;
use ReflectionClass;
use Tests\TestCase;

class PenjualanPosUnitConversionTest extends TestCase
{
    public function test_product_payload_contains_every_distinct_batch_price(): void
    {
        $bottle = (new SatuansModel)->forceFill([
            'id' => 1,
            'nama' => 'Botol',
        ]);
        $medicine = (new MasterObatModel)->forceFill([
            'id' => 10,
            'kode_obat' => 'CENDO-HIALID',
            'nama_obat' => 'CENDO HIALID',
            'satuan_id' => 1,
            'total_stok' => 22,
        ]);
        $medicine->setRelation('satuan', $bottle);
        $medicine->setRelation('konversiSatuan', collect());
        $medicine->setRelation('stokBatches', collect([
            $this->batch(1, 'BATCH-LAMA', 50000),
            $this->batch(2, 'BATCH-BARU', 55000),
            $this->batch(3, 'BATCH-HARGA-SAMA', 50000),
        ]));

        foreach (['kategori', 'golongan', 'mainGolongan', 'subGolongan', 'sediaan', 'pabrikan'] as $relation) {
            $medicine->setRelation($relation, null);
        }

        $service = (new ReflectionClass(PenjualanPosService::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass($service))->getMethod('productPayload');
        $payload = $method->invoke($service, $medicine);

        $this->assertSame(50000.0, $payload['harga_jual']);
        $this->assertSame([50000.0, 55000.0], $payload['harga_jual_options']);
        $this->assertSame(10.0, $payload['harga_jual_variants'][0]['total_stok']);
        $this->assertSame(2, $payload['harga_jual_variants'][0]['layer_count']);
        $this->assertSame(5.0, $payload['harga_jual_variants'][1]['total_stok']);
    }

    public function test_cart_unit_options_include_stock_and_conversion_units(): void
    {
        $tablet = (new SatuansModel)->forceFill([
            'id' => 1,
            'nama' => 'Tablet',
        ]);
        $box = (new SatuansModel)->forceFill([
            'id' => 2,
            'nama' => 'Box',
        ]);
        $conversion = (new KonversiSatuanModel)->forceFill([
            'satuan_id' => 2,
            'konversi' => 10,
            'is_default' => false,
        ]);
        $conversion->setRelation('satuan', $box);

        $medicine = (new MasterObatModel)->forceFill([
            'id' => 10,
            'satuan_id' => 1,
        ]);
        $medicine->setRelation('satuan', $tablet);
        $medicine->setRelation('konversiSatuan', collect([$conversion]));

        $service = (new ReflectionClass(PenjualanPosService::class))->newInstanceWithoutConstructor();

        $this->assertSame([
            [
                'satuan_id' => 1,
                'nama' => 'Tablet',
                'konversi' => 1.0,
                'is_default' => true,
            ],
            [
                'satuan_id' => 2,
                'nama' => 'Box',
                'konversi' => 10.0,
                'is_default' => false,
            ],
        ], $service->unitsForProduct($medicine));
    }

    private function batch(int $id, string $number, float $sellingPrice): StokBatchModel
    {
        return (new StokBatchModel)->forceFill([
            'id' => $id,
            'no_batch' => $number,
            'expired_date' => now()->addMonths($id),
            'qty' => 5,
            'harga_jual' => $sellingPrice,
        ]);
    }
}
