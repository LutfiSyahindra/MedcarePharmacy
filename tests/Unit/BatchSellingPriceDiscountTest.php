<?php

namespace Tests\Unit;

use App\Models\Menu\Stok\StokBatchModel;
use App\Services\Menu\Stok\StockService;
use Tests\TestCase;

class BatchSellingPriceDiscountTest extends TestCase
{
    public function test_batch_discount_keeps_other_costs_out_of_the_discount(): void
    {
        $batch = new StokBatchModel([
            'harga_jual' => 1450,
            'biaya_lain' => 100,
            'diskon' => 10,
        ]);
        $batch->setRelation('latestPostedReceiptDetail', null);

        $preview = app(StockService::class)->batchSellingPriceDiscountPreview($batch);

        $this->assertSame(10.0, $preview['diskon_persen']);
        $this->assertNull($preview['diskon_untuk']);
        $this->assertSame(1600.0, $preview['harga_jual_sebelum_diskon']);
        $this->assertSame(1450.0, $preview['harga_jual_sesudah_diskon']);
        $this->assertSame('1450.00', $batch->harga_jual);
    }

    public function test_full_discount_does_not_invent_an_unknown_gross_selling_price(): void
    {
        $batch = new StokBatchModel([
            'harga_jual' => 100,
            'biaya_lain' => 100,
            'diskon' => 100,
        ]);
        $batch->setRelation('latestPostedReceiptDetail', null);

        $preview = app(StockService::class)->batchSellingPriceDiscountPreview($batch);

        $this->assertNull($preview['harga_jual_sebelum_diskon']);
        $this->assertSame(100.0, $preview['harga_jual_sesudah_diskon']);
    }
}
