<?php

namespace Tests\Unit;

use App\Support\TieredDiscount;
use PHPUnit\Framework\TestCase;

class TieredDiscountTest extends TestCase
{
    public function test_discounts_are_applied_sequentially(): void
    {
        $this->assertSame(167580.0, TieredDiscount::netAmount(200000, 10, 5, 2));
        $this->assertSame(32420.0, TieredDiscount::discountAmount(200000, 10, 5, 2));
        $this->assertSame(16.21, TieredDiscount::effectivePercentage(10, 5, 2));
    }

    public function test_percentages_are_clamped_to_valid_range(): void
    {
        $this->assertSame([0.0, 100.0, 5.56], TieredDiscount::percentages(-5, 150, 5.555));
    }
}
