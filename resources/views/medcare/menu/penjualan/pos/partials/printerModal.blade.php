<div class="modal fade pos-printer-modal" id="posPrinterModal" tabindex="-1"
    aria-labelledby="posPrinterModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <span class="pos-printer-modal-icon"><i class="mdi mdi-printer-wireless"></i></span>
                <div>
                    <small>PRINTER KASIR</small>
                    <h2 class="modal-title" id="posPrinterModalTitle">Putian POS Thermal 80 mm</h2>
                    <p>Cari, pasangkan, lalu tes printer dari perangkat kasir ini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <section class="pos-printer-state" id="posPrinterConnectionState" data-state="idle" aria-live="polite">
                    <span class="pos-printer-state-icon"><i class="mdi mdi-printer-alert"></i></span>
                    <div>
                        <small>STATUS KONEKSI</small>
                        <strong id="posPrinterConnectionTitle">Belum ada printer terhubung</strong>
                        <p id="posPrinterConnectionCopy">Pilih cara koneksi yang sesuai dengan Bluetooth printer.</p>
                    </div>
                    <span class="pos-printer-state-badge" id="posPrinterConnectionBadge">OFFLINE</span>
                </section>

                <div class="pos-printer-methods">
                    <button type="button" class="pos-printer-method" id="connectSerialPrinterBtn">
                        <span><i class="mdi mdi-serial-port"></i></span>
                        <div>
                            <strong>Hubungkan Putian (disarankan)</strong>
                            <small>Untuk Putian Bluetooth Classic/SPP di Chrome Android terbaru atau Chrome/Edge desktop.</small>
                        </div>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>

                    <button type="button" class="pos-printer-method" id="searchBluetoothPrinterBtn">
                        <span><i class="mdi mdi-bluetooth"></i></span>
                        <div>
                            <strong>Cari printer Bluetooth BLE</strong>
                            <small>Untuk varian printer BLE di Chrome Android atau desktop.</small>
                        </div>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>

                    <button type="button" class="pos-printer-method" id="useBrowserPrinterBtn">
                        <span><i class="mdi mdi-printer-settings"></i></span>
                        <div>
                            <strong>Gunakan dialog cetak browser</strong>
                            <small>Pilih Putian POS, kertas Receipt/Continuous 80 mm, margin none, dan skala 100%.</small>
                        </div>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                </div>

                <div class="pos-printer-help" id="posPrinterCompatibilityHelp">
                    <i class="mdi mdi-information-outline"></i>
                    <p><strong>Putian tidak muncul saat pencarian?</strong> Untuk model Bluetooth Classic/SPP, pasangkan printer dari pengaturan Bluetooth lalu pilih <b>Hubungkan Putian</b>. Gunakan Chrome Android terbaru; printer BLE memakai pilihan pencarian BLE.</p>
                </div>

                <div class="pos-printer-options">
                    <label for="posPrinterColumns">
                        <span><strong>Lebar cetak</strong><small>Kertas thermal 80 mm</small></span>
                        <select class="form-select" id="posPrinterColumns">
                            <option value="48">48 karakter (normal)</option>
                            <option value="42">42 karakter (lebih besar)</option>
                        </select>
                    </label>
                    <label class="pos-printer-cut-option" for="posPrinterAutoCut">
                        <span><strong>Potong otomatis</strong><small>Biarkan nonaktif untuk Putian portable tanpa cutter</small></span>
                        <input class="form-check-input" type="checkbox" id="posPrinterAutoCut">
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn pos-printer-release d-none" id="releasePrinterBtn">
                    <i class="mdi mdi-link-off"></i> Lepaskan
                </button>
                <button type="button" class="btn pos-printer-test" id="testThermalPrinterBtn" disabled>
                    <i class="mdi mdi-text-box-check-outline"></i> Tes cetak
                </button>
                <button type="button" class="btn pos-printer-done" data-bs-dismiss="modal">Selesai</button>
            </div>
        </div>
    </div>
</div>
