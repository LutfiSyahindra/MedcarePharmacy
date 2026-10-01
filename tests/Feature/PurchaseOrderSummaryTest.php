<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchaseOrderSummaryTest extends TestCase
{
    public function test_purchase_order_page_wires_completed_summary_from_controller_to_card(): void
    {
        $controller = file_get_contents(app_path(
            'Http/Controllers/Medcare/Menu/PembelianDanPenerimaan/Pembelian/PembelianController.php'
        ));
        $view = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/pembelian.blade.php'
        ));
        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($controller);
        $this->assertStringContainsString("'selesai' => \$summary->where('status', 'selesai')->count()", $controller);
        $this->assertStringContainsString('id="purchaseCompletedCount"', $view);
        $this->assertStringContainsString('data-status="selesai"', $view);
        $this->assertStringContainsString('id="purchaseCompletedFilterCount"', $view);
        $this->assertStringContainsString('<span>Selesai</span>', $view);
        $this->assertStringContainsString('Number(summary.selesai)', $script);
        $this->assertStringContainsString("$('#purchaseCompletedCount, #purchaseCompletedFilterCount').text", $script);
        $this->assertStringContainsString("'total_item' => \$summary->sum", $controller);
        $this->assertStringContainsString("'total_qty' => \$summary->sum", $controller);
        $this->assertStringContainsString('class="purchase-estimate-panel"', $view);
        $this->assertStringContainsString('id="purchaseTotalValue"', $view);
        $this->assertStringContainsString('id="purchaseTotalItem"', $view);
        $this->assertStringContainsString('id="purchaseTotalQty"', $view);
        $this->assertStringNotContainsString('1. Susun PO', $view);
        $this->assertStringContainsString("$('#purchaseTotalItem').text", $script);
        $this->assertStringContainsString("$('#purchaseTotalQty').text", $script);
    }
}
