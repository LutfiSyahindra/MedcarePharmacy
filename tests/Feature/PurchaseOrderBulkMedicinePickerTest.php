<?php

namespace Tests\Feature;

use Tests\TestCase;

class PurchaseOrderBulkMedicinePickerTest extends TestCase
{
    public function test_purchase_order_form_exposes_searchable_bulk_medicine_picker(): void
    {
        $this->view('medcare.menu.pembelianPenerimaan.pembelian.modalMain')
            ->assertSee('id="toggleMedicinePicker"', false)
            ->assertSee('Pilih Banyak Obat')
            ->assertSee('id="medicinePickerSearch"', false)
            ->assertSee('id="selectAllVisibleMedicines"', false)
            ->assertSee('id="medicinePickerList"', false)
            ->assertSee('id="addSelectedMedicines"', false)
            ->assertSee('Masukkan ke Rincian');
    }

    public function test_bulk_picker_script_prevents_duplicate_rows_and_populates_dynamic_inputs(): void
    {
        $script = file_get_contents(resource_path(
            'views/medcare/menu/pembelianPenerimaan/pembelian/jsMain.blade.php'
        ));

        $this->assertIsString($script);
        $this->assertStringContainsString('currentDetailMedicineIds()', $script);
        $this->assertStringContainsString("$('#addSelectedMedicines').on('click'", $script);
        $this->assertStringContainsString('populateMedicineSelect(', $script);
        $this->assertStringContainsString('append(detailItemTemplate())', $script);
        $this->assertStringContainsString('initializeMedicineSelect($select)', $script);
        $this->assertStringContainsString('preloadMedicineUnits(selectedItems.map', $script);
        $this->assertStringNotContainsString('medicineCatalog.forEach(function(item)', $script);
    }

    public function test_purchase_order_controller_supports_batched_unit_loading(): void
    {
        $controller = file_get_contents(app_path(
            'Http/Controllers/Medcare/Menu/PembelianDanPenerimaan/Pembelian/PembelianController.php'
        ));

        $this->assertIsString($controller);
        $this->assertStringContainsString("\$request->has('obat_ids')", $controller);
        $this->assertStringContainsString("'obat_ids' => ['required', 'array', 'max:250']", $controller);
        $this->assertStringContainsString("->whereIn('obat_id', \$medicineIds)", $controller);
    }
}
