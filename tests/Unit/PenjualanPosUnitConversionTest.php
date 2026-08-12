<?php

namespace Tests\Unit;

use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\SatuansModel;
use App\Services\Menu\Penjualan\PenjualanPosService;
use ReflectionClass;
use Tests\TestCase;

class PenjualanPosUnitConversionTest extends TestCase
{
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
}
