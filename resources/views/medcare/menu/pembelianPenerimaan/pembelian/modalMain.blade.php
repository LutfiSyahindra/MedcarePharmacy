<div class="modal fade purchase-modal" id="pembelianModal" tabindex="-1" aria-labelledby="pembelianModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-title-icon"><i class="mdi mdi-cart-plus"></i></span>
                    <div>
                        <h5 class="modal-title" id="pembelianModalLabel">Form Purchase Order</h5>
                        <p class="modal-subtitle">Pilih distributor, tambahkan obat, lalu periksa total pesanan.</p>
                    </div>
                </div>
                <div class="purchase-modal-header-meta">
                    <span id="purchaseDraftStatus" class="purchase-modal-status" role="status" aria-live="polite">
                        Belum disimpan
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            </div>

            <nav class="purchase-mobile-navigation" aria-label="Bagian form purchase order">
                <button type="button" class="purchase-section-link is-current" data-purchase-section="purchaseInformationSection"
                    aria-controls="purchaseInformationSection" aria-current="step">
                    <span class="purchase-step-number" aria-hidden="true">1</span> Informasi
                </button>
                <button type="button" class="purchase-section-link" data-purchase-section="purchaseItemsSection"
                    aria-controls="purchaseItemsSection">
                    <span class="purchase-step-number" aria-hidden="true">2</span> Obat
                </button>
                <button type="button" class="purchase-section-link" data-purchase-section="purchaseSummarySection"
                    aria-controls="purchaseSummarySection">
                    <span class="purchase-step-number" aria-hidden="true">3</span> Ringkasan
                </button>
            </nav>

            <div class="modal-body">
                <form id="pembelianForm" novalidate>
                    @csrf

                    <input type="hidden" class="form-control" name="pembelian_id" id="pembelian_id">

                    <div class="purchase-form-layout">
                        <div class="purchase-form-main">
                            <section class="purchase-form-section purchase-information-section" id="purchaseInformationSection"
                                aria-labelledby="purchaseInformationTitle" tabindex="-1">
                                <div class="purchase-form-section-header">
                                    <div class="purchase-form-section-title">
                                        <i class="mdi mdi-file-document-outline"></i>
                                        <div>
                                            <strong id="purchaseInformationTitle">1. Informasi Pesanan</strong>
                                            <small>Mulai dengan memilih distributor. Kolom bertanda * wajib diisi.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="purchase-form-section-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-5 purchase-distributor-field">
                                            <label class="form-label" for="distributor_id">Distributor <span class="purchase-required" aria-hidden="true">*</span></label>
                                            <select class="js-example-basic-single form-select" data-width="100%"
                                                name="distributor_id" id="distributor_id" required>
                                                <option value="">-- Pilih Distributor --</option>
                                            </select>
                                        </div>

                                        <div class="col-12 col-md-7 col-lg-4">
                                            <div class="purchase-field-heading">
                                                <label class="form-label" for="purchaseNumber">Nomor PO <span class="purchase-required" aria-hidden="true">*</span></label>
                                                <div class="purchase-number-mode">
                                                    <strong id="purchaseNumberModeTitle">Pilih Distributor</strong>
                                                    <span id="purchaseNumberModeDescription" class="visually-hidden">Mode nomor PO ditentukan oleh distributor.</span>
                                                </div>
                                            </div>
                                            <div class="purchase-input-icon">
                                                <i class="mdi mdi-file-document-edit-outline"></i>
                                                <input type="text" class="form-control" name="no_po" id="purchaseNumber"
                                                    aria-describedby="purchaseNumberHint"
                                                    placeholder="Pilih distributor terlebih dahulu" maxlength="50" readonly required>
                                            </div>
                                            <small class="purchase-field-hint" id="purchaseNumberHint">Nomor PO mengikuti pengaturan distributor.</small>
                                        </div>

                                        <div class="col-12 col-md-5 col-lg-3">
                                            <label class="form-label" for="purchaseDate">Tanggal PO <span class="purchase-required" aria-hidden="true">*</span></label>
                                            <div class="input-group flatpickr" id="flatpickr-date" data-wrap="true"
                                                data-click-opens="true">
                                                <input type="text" class="form-control" placeholder="Pilih tanggal"
                                                    name="tanggal" id="purchaseDate" data-input required>
                                                <button type="button" class="input-group-text" data-toggle aria-label="Buka kalender tanggal PO">
                                                    <i class="mdi mdi-calendar-month-outline"></i>
                                                </button>
                                            </div>
                                            <small class="purchase-field-hint">Gunakan tanggal rencana pembelian.</small>
                                        </div>

                                        <div class="col-md-12">
                                            <details class="purchase-notes-details">
                                                <summary><i class="mdi mdi-text-box-outline" aria-hidden="true"></i> Catatan <span class="text-muted">(Opsional)</span></summary>
                                                <label class="form-label visually-hidden" for="purchaseNotes">Catatan pesanan</label>
                                                <textarea class="form-control mt-2" name="catatan" id="purchaseNotes" rows="2"
                                                    placeholder="Contoh: kirim sebelum Jumat atau sertakan faktur."></textarea>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section class="purchase-form-section purchase-items-section" id="purchaseItemsSection"
                                aria-labelledby="purchaseItemsTitle" tabindex="-1">
                                <div class="purchase-form-section-header">
                                    <div class="purchase-form-section-title">
                                        <i class="mdi mdi-pill-multiple"></i>
                                        <div>
                                            <strong id="purchaseItemsTitle">2. Rincian Obat</strong>
                                            <small><span class="purchase-desktop-text">Tab: pindah kolom · Enter: baris berikutnya. Harga mengikuti satuan dasar.</span><span class="purchase-mobile-text">Pilih obat, isi jumlah dan harga, lalu periksa subtotal.</span></small>
                                        </div>
                                    </div>
                                    <div class="purchase-detail-actions">
                                        <button type="button" id="toggleMedicinePicker"
                                            class="btn btn-primary btn-sm purchase-add-detail" aria-expanded="false"
                                            aria-controls="medicinePicker">
                                            <i class="mdi mdi-checkbox-multiple-marked-outline"></i>
                                            Pilih Banyak Obat
                                        </button>
                                        <button type="button" id="addDetail"
                                            class="btn btn-outline-success btn-sm purchase-add-detail">
                                            <i class="mdi mdi-plus-circle-outline"></i>
                                            <span class="purchase-desktop-text">Tambah Baris Manual</span><span class="purchase-mobile-text">Tambah Obat</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="purchase-form-section-body">
                                    <div id="medicinePicker" class="purchase-medicine-picker d-none" aria-hidden="true">
                                        <div class="purchase-medicine-picker-head">
                                            <div>
                                                <strong><i class="mdi mdi-format-list-checks"></i> Pilih Item Obat</strong>
                                                <small>Cari lalu centang beberapa obat untuk dimasukkan sekaligus ke rincian PO. Stok mengikuti branch PO.</small>
                                            </div>
                                            <button type="button" id="closeMedicinePicker"
                                                class="btn btn-sm btn-light purchase-picker-close" aria-label="Tutup daftar obat"
                                                title="Tutup daftar obat">
                                                <i class="mdi mdi-close"></i>
                                            </button>
                                        </div>

                                        <div class="purchase-medicine-picker-toolbar">
                                            <div class="purchase-picker-search">
                                                <i class="mdi mdi-magnify"></i>
                                                <input type="search" id="medicinePickerSearch"
                                                    placeholder="Cari kode, nama, atau satuan obat..." autocomplete="off"
                                                    aria-label="Cari item obat">
                                                <button type="button" id="clearMedicinePickerSearch"
                                                    aria-label="Hapus pencarian obat" title="Hapus pencarian">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                            <div class="purchase-picker-selection">
                                                <label class="purchase-picker-select-all" for="selectAllVisibleMedicines">
                                                    <input type="checkbox" class="form-check-input"
                                                        id="selectAllVisibleMedicines"
                                                        aria-label="Pilih semua obat di halaman ini">
                                                    <span>Pilih semua di halaman ini</span>
                                                </label>
                                                <span id="medicinePickerResultCount" class="purchase-picker-result-count" aria-live="polite">
                                                    Memuat daftar obat...
                                                </span>
                                            </div>
                                        </div>

                                        <div class="purchase-picker-table-wrap">
                                            <table class="table table-hover align-middle purchase-picker-table">
                                                <thead>
                                                    <tr>
                                                        <th class="purchase-picker-check-cell">Pilih</th>
                                                        <th>Kode</th>
                                                        <th>Nama Obat</th>
                                                        <th>Satuan Dasar</th>
                                                        <th class="text-end">Stok Saat Ini</th>
                                                        <th class="text-end">Harga Beli</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="medicinePickerList">
                                                    <tr>
                                                        <td colspan="7" class="purchase-picker-state">
                                                            <i class="mdi mdi-loading mdi-spin"></i> Memuat daftar obat...
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="purchase-picker-pagination">
                                            <button type="button" id="medicinePickerPrevious" class="btn btn-light btn-sm">Sebelumnya</button>
                                            <button type="button" id="medicinePickerNext" class="btn btn-light btn-sm">Berikutnya</button>
                                        </div>
                                        <div class="purchase-medicine-picker-footer">
                                            <span>
                                                <strong id="medicinePickerSelectedCount">0</strong> obat dipilih
                                            </span>
                                            <button type="button" id="addSelectedMedicines" class="btn btn-primary btn-sm"
                                                disabled>
                                                <i class="mdi mdi-playlist-plus"></i>
                                                Masukkan ke Rincian
                                            </button>
                                        </div>
                                    </div>

                                    @include('medcare.menu.pembelianPenerimaan.pembelian.partials.entryTools')
                                    <div class="purchase-detail-summary" aria-live="polite">
                                        <span><i class="mdi mdi-format-list-numbered"></i> <strong
                                                id="purchaseModalLineCount">1</strong> baris obat</span>
                                        <span><strong id="purchaseReadyCount">0</strong> lengkap</span>
                                        <span><strong id="purchaseIssueCount">1</strong> perlu diperiksa</span>
                                        <span id="purchaseUnitLoadingNotice" class="d-none">
                                            <i class="mdi mdi-loading mdi-spin"></i> Memuat satuan obat...
                                        </span>
                                    </div>
                                    <div id="purchaseConversionWarning" class="alert alert-warning d-none" role="alert">
                                        <i class="mdi mdi-alert-outline"></i>
                                        <strong>Satuan konversi belum lengkap.</strong>
                                        Klik <strong>Set Satuan Konversi</strong> pada baris obat untuk melengkapinya langsung di form ini.
                                        PO dapat disimpan sebagai draft selama konversi belum lengkap.
                                    </div>
                                    <div class="purchase-entry-table-wrap">
                                        <table class="table purchase-entry-table" aria-label="Rincian obat purchase order">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="purchase-selection-column"><input type="checkbox" id="purchaseSelectVisible" class="form-check-input" aria-label="Pilih semua baris PO yang tampil"></th>
                                                    <th scope="col" class="purchase-medicine-column">Obat <small>Stok saat ini</small></th>
                                                    <th scope="col" class="purchase-unit-column">Satuan beli <small>Konversi pembelian</small></th>
                                                    <th scope="col" class="purchase-qty-column">Qty</th>
                                                    <th scope="col" class="purchase-price-column">Harga dasar <small>Rp / satuan dasar</small></th>
                                                    <th scope="col" class="purchase-subtotal-column">Subtotal <small>Termasuk PPN (Rp)</small></th>
                                                    <th scope="col" class="purchase-actions-column">Diskon &amp; PPN <small>Pengaturan / hapus</small></th>
                                                </tr>
                                            </thead>
                                            <tbody id="detail-wrapper">@include('medcare.menu.pembelianPenerimaan.pembelian.partials.detailRow')</tbody>
                                        </table>
                                    </div>
                                    <p id="purchaseNoItemResults" class="text-muted d-none">Tidak ada item yang sesuai dengan filter.</p>
                                    <button type="button" id="addAnotherDetail" class="btn btn-outline-success purchase-add-another">
                                        <i class="mdi mdi-plus-circle-outline" aria-hidden="true"></i> Tambah Obat Lagi
                                    </button>
                                </div>
                            </section>
                        </div>

                        <aside class="purchase-form-aside" id="purchaseSummarySection" aria-label="Biaya dan ringkasan pesanan" tabindex="-1">
                            <section class="purchase-form-section" aria-labelledby="purchaseCostsTitle">
                                <div class="purchase-form-section-header">
                                    <div class="purchase-form-section-title">
                                        <i class="mdi mdi-truck-delivery-outline"></i>
                                        <div>
                                            <strong id="purchaseCostsTitle">3. Biaya &amp; Ringkasan</strong>
                                            <small>Biaya tambahan boleh dikosongkan.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="purchase-form-section-body">
                                    <details class="purchase-additional-costs" id="purchaseAdditionalCosts" open>
                                        <summary>
                                            <span><i class="mdi mdi-plus-circle-outline" aria-hidden="true"></i> Biaya tambahan</span>
                                            <span id="purchaseAdditionalCostsValue">Opsional</span>
                                            <i class="mdi mdi-chevron-down" aria-hidden="true"></i>
                                        </summary>
                                        <div class="purchase-additional-costs-fields">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label" for="purchaseInsurance">Biaya Asuransi</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="number" class="form-control purchase-additional-cost"
                                                            name="biaya_asuransi" min="0" max="9999999999999.99" step="0.01"
                                                            value="0" id="purchaseInsurance" inputmode="decimal">
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label" for="purchaseShipping">Biaya Pengiriman</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="number" class="form-control purchase-additional-cost"
                                                            name="biaya_pengiriman" min="0" max="9999999999999.99" step="0.01"
                                                            value="0" id="purchaseShipping" inputmode="decimal">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </section>

                            <div class="purchase-total-panel">
                                <div class="purchase-total-copy">
                                    <span>Ringkasan Pesanan</span>
                                    <small>Diskon dihitung berurutan; PPN dihitung setelah diskon.</small>
                                </div>
                                <div class="purchase-total-stats">
                                    <span>Baris obat <strong id="purchaseModalItemCount">1</strong></span>
                                    <span>Subtotal obat sebelum diskon <strong id="purchaseModalGrossSubtotal">Rp 0</strong></span>
                                    <span>Subtotal diskon <strong id="purchaseModalDiscountSubtotal">Rp 0</strong></span>
                                    <span>Subtotal PPN <strong id="purchaseModalTaxSubtotal">Rp 0</strong></span>
                                    <span class="purchase-medicine-total">Subtotal akhir obat <strong id="purchaseModalMedicineSubtotal">Rp 0</strong></span>
                                    <span>Biaya tambahan <strong id="purchaseModalAdditionalCost">Rp 0</strong></span>
                                </div>
                                <div class="purchase-grand-total">
                                    <span>Total Estimasi</span>
                                    <strong id="total_estimasi">Rp 0</strong>
                                </div>
                                <input type="hidden" name="total_estimasi" id="total_estimasi_input" value="0">
                            </div>
                            <p class="purchase-save-hint"><i class="mdi mdi-information-outline" aria-hidden="true"></i> Simpan sebagai draft untuk melanjutkan pengisian nanti.</p>
                        </aside>
                    </div>
                </form>
            </div>

            <div class="modal-footer purchase-form-footer">
                <div class="purchase-mobile-total">
                    <div class="purchase-footer-total-copy" role="status" aria-live="polite" aria-atomic="true">
                        <span>Total Estimasi <span class="purchase-footer-item-count">· <span id="purchaseFooterItemCount">0</span> obat</span></span>
                        <strong id="purchaseMobileTotal">Rp 0</strong>
                    </div>
                    <button type="button" id="purchaseReviewTotal" class="purchase-review-total" aria-controls="purchaseSummarySection">
                        Rincian <i class="mdi mdi-chevron-up" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="purchase-save-actions">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="mdi mdi-close-circle-outline"></i>
                        Tutup
                    </button>
                    <button type="button" id="saveDraftForm" class="btn btn-outline-primary">
                        <i class="mdi mdi-content-save-move-outline"></i>
                        <span class="purchase-desktop-text">Simpan sebagai Draft</span><span class="purchase-mobile-text">Simpan Draft</span>
                    </button>
                    <button type="submit" id="submitForm" class="btn btn-primary" form="pembelianForm">
                        <i class="mdi mdi-content-save-outline"></i>
                        <span class="purchase-desktop-text">Periksa &amp; Ajukan</span><span class="purchase-mobile-text">Ajukan PO</span>
                    </button>
                </div>
            </div>
            @include('medcare.menu.pembelianPenerimaan.pembelian.partials.conversionModal')
        </div>
    </div>
</div>

<template id="purchaseDetailRowTemplate">@include('medcare.menu.pembelianPenerimaan.pembelian.partials.detailRow')</template>
