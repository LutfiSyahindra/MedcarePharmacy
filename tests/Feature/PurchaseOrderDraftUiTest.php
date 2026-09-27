<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchaseOrderDraftUiTest extends TestCase
{
    public function test_purchase_order_modal_exposes_draft_and_automatic_close_save_controls(): void
    {
        $this->view('medcare.menu.pembelianPenerimaan.pembelian.modalMain')
            ->assertSee('id="saveDraftForm"', false)
            ->assertSee('Simpan sebagai Draft');

        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($script);
        $this->assertStringContainsString("$('#pembelianModal').on('hide.bs.modal'", $script);
        $this->assertStringContainsString('submitPurchaseOrder(true, true)', $script);
        $this->assertStringContainsString("name: 'save_as_draft'", $script);
    }
}
