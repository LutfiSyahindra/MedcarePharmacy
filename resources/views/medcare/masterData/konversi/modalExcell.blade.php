<div class="modal fade obat-modal konversi-modal" id="konversiModalExcell" tabindex="-1" aria-labelledby="konversiModalExcellLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title-wrap">
                    <span class="modal-icon"><i class="mdi mdi-file-excel-outline" aria-hidden="true"></i></span>
                    <div>
                        <span class="konversi-modal-kicker">Import data</span>
                        <h2 class="modal-title mb-0" id="konversiModalExcellLabel">Import Konversi Satuan</h2>
                        <p class="modal-subtitle">Gunakan template agar data konversi terbaca dengan benar.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="konversiExcellForm" enctype="multipart/form-data">
                    @csrf
                    <ol class="konversi-import-steps" aria-label="Langkah import"><li><span>1</span>Unduh template</li><li><span>2</span>Lengkapi data</li><li><span>3</span>Upload Excel</li></ol>
                    <div class="konversi-import-template">
                        <div><strong>Mulai dari template Excel</strong><p>Isi data sesuai kolom yang tersedia pada template.</p></div>
                        <button type="button" id="downloadTemplateBtn" class="btn btn-outline-primary"><i class="mdi mdi-download" aria-hidden="true"></i>Unduh Template</button>
                    </div>
                    <div class="konversi-import-upload">
                        <label class="form-label" for="myDropify">File konversi satuan</label>
                        <input type="file" id="myDropify" name="file" class="dropify" accept=".xls,.xlsx" aria-describedby="konversiFileHelp"
                            data-allowed-file-extensions="xls xlsx" data-max-file-size="5M"
                            data-messages='{"default":"Pilih file Excel atau tarik file ke sini","replace":"Pilih atau tarik file untuk mengganti","remove":"Hapus","error":"File belum sesuai. Periksa format dan ukurannya."}'
                            data-error='{"fileSize":"Ukuran file terlalu besar (maksimal @{{ value }}).","fileExtension":"Gunakan format Excel (@{{ value }})."}' />
                        <small class="konversi-field-help" id="konversiFileHelp">Format .xls atau .xlsx · Maksimal 5 MB</small>
                    </div>
                    <div class="konversi-modal-note mt-3"><i class="mdi mdi-information-outline" aria-hidden="true"></i><div>Pastikan kode obat, satuan pembelian, dan isi konversi sesuai master data sebelum mengunggah.</div></div>
                </form>
            </div>
            <div class="modal-footer">
                <span class="konversi-footer-hint"><i class="mdi mdi-file-check-outline" aria-hidden="true"></i>Gunakan format template yang disediakan.</span>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" id="submitFormExcell" class="btn btn-primary"><i class="mdi mdi-upload" aria-hidden="true"></i>Import Sekarang</button>
            </div>
        </div>
    </div>
</div>
