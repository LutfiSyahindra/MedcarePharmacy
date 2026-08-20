<div class="modal fade" id="documentDetailModal" tabindex="-1" aria-labelledby="documentDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content document-detail-modal">
            <div class="modal-header">
                <div class="document-modal-title">
                    <span id="documentDetailIcon"><i class="mdi mdi-file-document-outline"></i></span>
                    <div>
                        <small>Detail Dokumen</small>
                        <h5 class="modal-title" id="documentDetailTitle">Surat Pesanan</h5>
                        <p id="documentDetailNumber">-</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="document-detail-loading" id="documentDetailLoading">
                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                    Menyiapkan detail dokumen...
                </div>
                <div id="documentDetailContent" class="d-none">
                    <div class="document-detail-summary" id="documentDetailSummary"></div>

                    <section class="document-number-section">
                        <div class="document-section-title">
                            <span><i class="mdi mdi-identifier"></i></span>
                            <div><h6>Nomor Dokumen</h6><p>Nomor surat yang tercakup dalam kelompok ini.</p></div>
                        </div>
                        <div class="document-number-list" id="documentNumberList"></div>
                    </section>

                    <section class="document-item-section">
                        <div class="document-section-title">
                            <span><i class="mdi mdi-pill-multiple"></i></span>
                            <div><h6>Item Obat</h6><p>Rincian obat yang dimuat dalam surat pesanan.</p></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table document-item-table align-middle">
                                <thead><tr><th>No</th><th>Nama Obat</th><th>Klasifikasi</th><th>Jumlah</th><th>Nomor Surat</th></tr></thead>
                                <tbody id="documentItemList"></tbody>
                            </table>
                        </div>
                    </section>

                    <div class="document-detail-note" id="documentDetailNote"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <a href="#" class="btn btn-outline-primary" target="_blank" rel="noopener" id="documentPreviewButton">
                    <i class="mdi mdi-eye-outline"></i> Pratinjau
                </a>
                <a href="#" class="btn btn-primary" target="_blank" rel="noopener" id="documentPrintButton">
                    <i class="mdi mdi-printer-outline"></i> Cetak / Simpan PDF
                </a>
            </div>
        </div>
    </div>
</div>
