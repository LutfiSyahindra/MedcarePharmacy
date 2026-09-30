<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchaseOrderDraftUiTest extends TestCase
{
    public function test_purchase_order_form_uses_conversion_for_subtotal_and_prioritizes_unit_width(): void
    {
        $modal = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/modalMain.blade.php'
        ));
        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($modal);
        $this->assertIsString($script);
        $this->assertStringContainsString('col-lg-3 col-md-6 purchase-unit-field', $modal);
        $this->assertStringContainsString('col-lg-1 col-md-4 purchase-qty-field', $modal);
        $this->assertStringContainsString('name="harga_estimasi_satuan[]"', $modal);
        $this->assertStringContainsString('type="hidden" class="harga_estimasi" name="harga_estimasi[]"', $modal);
        $this->assertStringContainsString('class="form-control purchase-tax"', $modal);
        $this->assertStringContainsString('name="ppn[]" min="0" max="100" step="0.01" value="11"', $modal);
        $this->assertStringContainsString('const purchaseUnitPrice = pricePerUnit * conversion;', $script);
        $this->assertStringNotContainsString('convertedPurchaseUnitPrice', $script);
        $this->assertStringContainsString('let subtotal = qty * purchaseUnitPrice;', $script);
        $this->assertStringContainsString('subtotal = roundPurchaseMoney(subtotal * (1 + (ppn / 100)));', $script);
        $this->assertStringContainsString('value="${options.ppn ?? 11}"', $script);
        $this->assertStringContainsString("row.find('.harga_estimasi').val(purchaseUnitPrice.toFixed(2));", $script);
    }

    public function test_purchase_order_modal_only_closes_explicitly_and_never_auto_saves(): void
    {
        $this->view('medcare.menu.pembelianPenerimaan.pembelian.modalMain')
            ->assertSee('data-bs-backdrop="static"', false)
            ->assertSee('data-bs-keyboard="false"', false)
            ->assertSee('class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"', false)
            ->assertSee('class="btn btn-light" data-bs-dismiss="modal"', false)
            ->assertSee('id="saveDraftForm"', false)
            ->assertSee('Simpan sebagai Draft');

        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($script);
        $this->assertStringNotContainsString("$('#pembelianModal').on('hide.bs.modal'", $script);
        $this->assertStringNotContainsString('automaticSave', $script);
        $this->assertStringContainsString('submitPurchaseOrder(true)', $script);
        $this->assertStringContainsString('submitPurchaseOrder(false)', $script);
        $this->assertStringContainsString("name: 'save_as_draft'", $script);
    }
}
