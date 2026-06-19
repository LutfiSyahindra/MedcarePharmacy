<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">Sediaan</li>
    </ol>
</nav>

<section class="sediaan-hero">
    <div>
        <span class="sediaan-kicker">
            <i class="mdi mdi-bottle-tonic-outline"></i>
            Dosage Form
        </span>
        <h2>Sediaan Obat</h2>
        <p>Kelola bentuk sediaan obat seperti tablet, kapsul, sirup, injeksi, atau salep agar master obat lebih jelas dan konsisten.</p>
        <div class="sediaan-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#sediaanModal">
                <i class="mdi mdi-plus-circle-outline"></i>
                Tambah Sediaan
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#sediaanModalExcell">
                <i class="mdi mdi-file-excel-outline"></i>
                Import Excel
            </button>
            <button type="button" class="btn btn-outline-light sediaan-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="sediaan-flow-panel" aria-label="Alur data sediaan">
        <div class="sediaan-flow-item">
            <i class="mdi mdi-barcode-scan"></i>
            <div>
                <span>Kode</span>
                <small>Identitas singkat sediaan</small>
            </div>
        </div>
        <div class="sediaan-flow-item">
            <i class="mdi mdi-pill"></i>
            <div>
                <span>Bentuk Sediaan</span>
                <small>Tablet, kapsul, sirup, injeksi, dan lainnya</small>
            </div>
        </div>
        <div class="sediaan-flow-item">
            <i class="mdi mdi-text-box-check-outline"></i>
            <div>
                <span>Keterangan</span>
                <small>Catatan tambahan untuk konteks pemakaian</small>
            </div>
        </div>
    </div>
</section>
