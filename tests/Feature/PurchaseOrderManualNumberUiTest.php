<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchaseOrderManualNumberUiTest extends TestCase
{
    public function test_purchase_order_form_switches_number_input_from_distributor_setting(): void
    {
        $modal = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/modalMain.blade.php'
        ));
        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($modal);
        $this->assertIsString($script);
        $this->assertStringContainsString('id="purchaseNumberModeTitle"', $modal);
        $this->assertStringContainsString('name="no_po"', $modal);
        $this->assertStringContainsString('selectedDistributorUsesManualPoNumber()', $script);
        $this->assertStringContainsString('data-manual-po-number', $script);
        $this->assertStringContainsString("field.prop('readonly', false)", $script);
        $this->assertStringContainsString('Nomor PO wajib diketik manual', $script);
    }

    public function test_setting_page_exposes_distributor_manual_number_toggles(): void
    {
        $view = file_get_contents(resource_path('views/medcare/settings/purchaseOrder/index.blade.php'));
        $script = file_get_contents(resource_path('views/medcare/settings/purchaseOrder/script.blade.php'));

        $this->assertIsString($view);
        $this->assertIsString($script);
        $this->assertStringContainsString('Setting PO', $view);
        $this->assertStringContainsString('po-setting-toggle', $view);
        $this->assertStringContainsString('manual_distributor_ids', $script);
        $this->assertStringContainsString("route('settings.purchase-order.update')", $script);
    }
}
