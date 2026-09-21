<?php

namespace Tests\Feature;

use Tests\TestCase;

class PenjualanPosPrinterViewTest extends TestCase
{
    public function test_printer_panel_offers_putian_bluetooth_and_serial_pairing(): void
    {
        $html = view('medcare.menu.penjualan.pos.partials.printerModal')->render();

        $this->assertStringContainsString('id="posPrinterModal"', $html);
        $this->assertStringContainsString('Putian POS Thermal 80 mm', $html);
        $this->assertStringContainsString('id="searchBluetoothPrinterBtn"', $html);
        $this->assertStringContainsString('Cari printer Bluetooth BLE', $html);
        $this->assertStringContainsString('id="connectSerialPrinterBtn"', $html);
        $this->assertStringContainsString('Hubungkan Putian (disarankan)', $html);
        $this->assertStringContainsString('Bluetooth Classic/SPP', $html);
        $this->assertStringContainsString('Chrome Android terbaru', $html);
        $this->assertStringContainsString('printer BLE memakai pilihan pencarian BLE', $html);
        $this->assertStringContainsString('id="testThermalPrinterBtn"', $html);
        $this->assertStringContainsString('48 karakter (normal)', $html);
        $this->assertStringContainsString('id="posPrinterAutoCut"', $html);
        $this->assertStringNotContainsString('id="posPrinterAutoCut" checked', $html);
        $this->assertStringContainsString('Biarkan nonaktif untuk Putian portable tanpa cutter', $html);
    }

    public function test_printer_manager_supports_persistent_ble_serial_and_escpos_printing(): void
    {
        $script = file_get_contents(resource_path('views/medcare/menu/penjualan/pos/partials/printerScript.blade.php'));
        $posScript = file_get_contents(resource_path('views/medcare/menu/penjualan/pos/jsMain.blade.php'));
        $appScript = file_get_contents(resource_path('js/app.js'));

        $this->assertIsString($script);
        $this->assertIsString($posScript);
        $this->assertIsString($appScript);
        $this->assertStringContainsString('medcare.pos.thermal-printer.v1', $script);
        $this->assertStringContainsString('formatVersion: 3', $script);
        $this->assertStringContainsString('navigator.bluetooth.requestDevice', $script);
        $this->assertStringContainsString('optionalServices: bluetoothServiceUuids', $script);
        $this->assertStringContainsString('navigator.bluetooth.getDevices', $script);
        $this->assertStringContainsString('bluetoothConnectTimeoutMs = 15000', $script);
        $this->assertStringContainsString('bluetoothDiscoveryTimeoutMs = 12000', $script);
        $this->assertStringContainsString('function withTimeout(operation, duration, message)', $script);
        $this->assertStringContainsString("error.name = 'TimeoutError'", $script);
        $this->assertStringContainsString('let selectingDevice = false;', $script);
        $this->assertStringContainsString('if (selectingDevice || connecting) return;', $script);
        $this->assertStringContainsString('Tunggu hingga printer selesai menyiapkan koneksi.', $script);
        $this->assertStringContainsString('Printer tidak merespons dalam 15 detik', $script);
        $this->assertStringContainsString('connectOperation.then(() =>', $script);
        $this->assertStringContainsString('navigator.bluetooth.getDevices(),', $script);
        $this->assertStringContainsString('Daftar perangkat Bluetooth tersimpan tidak merespons.', $script);
        $this->assertStringContainsString('navigator.serial.requestPort', $script);
        $this->assertStringContainsString('navigator.serial.getPorts', $script);
        $this->assertStringContainsString('serialOpenTimeoutMs = 12000', $script);
        $this->assertStringContainsString('Daftar port printer tersimpan tidak merespons.', $script);
        $this->assertStringContainsString('openOperation.then(async () =>', $script);
        $this->assertStringContainsString('Perbarui Chrome Android ke versi terbaru', $script);
        $this->assertStringContainsString('new Uint8Array([0x1b, 0x40])', $script);
        $this->assertStringContainsString('new Uint8Array([0x1d, 0x56, 0x00])', $script);
        $this->assertStringNotContainsString('new Uint8Array([0x1d, 0x56, 0x42, 0x00])', $script);
        $this->assertStringContainsString('writeValueWithoutResponse', $script);
        $this->assertStringContainsString('activeDirectPrintPromise', $script);
        $this->assertStringContainsString('writeCommandSequence', $script);
        $this->assertStringContainsString('offset += 256', $script);
        $this->assertStringContainsString('top += 64', $script);
        $this->assertStringContainsString('rasterReceiptDocument', $script);
        $this->assertStringContainsString('printBrowserFrame', $script);
        $this->assertStringContainsString('printableCanvases', $script);
        $this->assertStringContainsString('compactThermalCanvas', $script);
        $this->assertStringContainsString('monochromeThermalCanvas', $script);
        $this->assertStringContainsString('function monochromeThermalCanvas(canvas, threshold = 175)', $script);
        $this->assertStringContainsString('const thermalColor = luminance < threshold ? 0 : 255;', $script);
        $this->assertStringContainsString('.map(canvas => monochromeThermalCanvas(canvas))', $script);
        $this->assertStringContainsString('function cropMonochromeThermalCanvas(canvas, edgePadding = 4)', $script);
        $this->assertStringContainsString('.map(canvas => cropMonochromeThermalCanvas(canvas))', $script);
        $this->assertStringContainsString('image.offsetTop + image.offsetHeight', $script);
        $this->assertStringContainsString('style.textContent = printStyles(pageHeightMm);', $script);
        $this->assertStringContainsString('height: auto !important;', $script);
        $this->assertStringContainsString('min-height: 0 !important;', $script);
        $this->assertStringNotContainsString('max-height: ${pageHeightMm}mm !important;', $script);
        $this->assertStringContainsString('image-rendering: pixelated;', $script);
        $this->assertStringContainsString('luminance < 245', $script);
        $this->assertStringContainsString('Math.min(sourceHeight, maxBlankRows)', $script);
        $this->assertSame(2, substr_count($script, '.map(canvas => compactThermalCanvas(canvas))'));
        $this->assertStringContainsString('const printableWidthMm = 72;', $script);
        $this->assertStringContainsString('const interDocumentGapMm = 0.8;', $script);
        $this->assertStringContainsString('height: `${pageHeightMm}mm`', $script);
        $this->assertStringContainsString('@page { size: ${paperWidthMm}mm ${heightMm}mm; margin: 0; }', $script);
        $this->assertStringContainsString("canvas.toDataURL('image/png')", $script);
        $this->assertStringContainsString('printableCanvas', $script);
        $this->assertStringContainsString("querySelectorAll('main.receipt, article.label-sheet')", $script);
        $this->assertStringContainsString('window.html2canvas(printable', $script);
        $this->assertStringContainsString('foreignObjectRendering: false', $script);
        $this->assertStringContainsString('allowTaint: false', $script);
        $this->assertStringContainsString('waitForPrintableAssets', $script);
        $this->assertStringContainsString('inlinePrintableImages', $script);
        $this->assertStringContainsString('reader.readAsDataURL(blob)', $script);
        $this->assertStringContainsString("querySelectorAll('main.receipt img, article.label-sheet img')", $script);
        $this->assertStringContainsString("import html2canvas from 'html2canvas';", $appScript);
        $this->assertStringContainsString('window.html2canvas = html2canvas;', $appScript);
        $this->assertStringContainsString('0x1d, 0x76, 0x30, 0x00', $script);
        $this->assertStringContainsString('printWidth = 576', $script);
        $this->assertStringContainsString('await wait(8);', $script);
        $this->assertStringContainsString("inkPixels < 100", $script);
        $this->assertStringNotContainsString('const text = receiptText(frameWindow);', $script);
        $this->assertStringContainsString('const finalFeed = new Uint8Array([0x0a]);', $script);
        $this->assertStringNotContainsString('const finalFeed = new Uint8Array([0x0a, 0x0a]);', $script);
        $this->assertStringContainsString('partials.printerScript', $posScript);
        $this->assertStringContainsString('pendingDocumentPrint', $posScript);
        $this->assertStringContainsString("if (posThermalPrinter.isDirectReady())", $posScript);
        $this->assertStringContainsString("'Cetak etiket langsung'", $script);
        $this->assertStringContainsString('posThermalPrinter.printReceiptFrame', $posScript);
        $this->assertStringContainsString('posThermalPrinter.printBrowserFrame', $posScript);
    }

    public function test_transaction_history_reuses_printer_configuration_for_receipt_reprints(): void
    {
        $historyView = file_get_contents(resource_path('views/medcare/menu/penjualan/pos/history.blade.php'));
        $historyScript = file_get_contents(resource_path('views/medcare/menu/penjualan/pos/historyJs.blade.php'));
        $printerScript = file_get_contents(resource_path('views/medcare/menu/penjualan/pos/partials/printerScript.blade.php'));

        $this->assertIsString($historyView);
        $this->assertIsString($historyScript);
        $this->assertIsString($printerScript);
        $this->assertStringContainsString('partials.printerStyle', $historyView);
        $this->assertStringContainsString('id="posPrinterButton"', $historyView);
        $this->assertStringContainsString('id="posPrinterStatus"', $historyView);
        $this->assertStringContainsString('partials.printerModal', $historyView);
        $this->assertStringContainsString('partials.printerScript', $historyScript);
        $this->assertStringContainsString('loadHistoryPrintFrame', $historyScript);
        $this->assertStringContainsString("printHistoryDocument(id, 'receipt', this)", $historyScript);
        $this->assertStringContainsString("printHistoryDocument(id, 'labels', this)", $historyScript);
        $this->assertStringContainsString('posThermalPrinter.printReceiptFrame(frameWindow)', $historyScript);
        $this->assertStringContainsString('posThermalPrinter.printBrowserFrame(frameWindow)', $historyScript);
        $this->assertStringContainsString("typeof activeReceiptDocument === 'undefined'", $printerScript);
        $this->assertStringContainsString("typeof posReceiptModalInstance === 'function'", $printerScript);
    }
}
