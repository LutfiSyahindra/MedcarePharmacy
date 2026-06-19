<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">Satuan</li>
    </ol>
</nav>

<section class="satuan-hero">
    <div>
        <span class="satuan-kicker">
            <i class="mdi mdi-scale-balance"></i>
            Unit Master
        </span>
        <h2>Satuan Obat</h2>
        <p>Kelola satuan pemakaian obat seperti tablet, botol, vial, atau strip agar master obat dan transaksi tetap konsisten.</p>
        <div class="satuan-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#satuanModal">
                <i class="mdi mdi-plus-circle-outline"></i>
                Tambah Satuan
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#satuanModalExcell">
                <i class="mdi mdi-file-excel-outline"></i>
                Import Excel
            </button>
            <button type="button" class="btn btn-outline-light satuan-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="satuan-flow-panel" aria-label="Alur data satuan">
        <div class="satuan-flow-item">
            <i class="mdi mdi-barcode-scan"></i>
            <div>
                <span>Kode</span>
                <small>Identitas singkat satuan</small>
            </div>
        </div>
        <div class="satuan-flow-item">
            <i class="mdi mdi-ruler-square"></i>
            <div>
                <span>Nama Satuan</span>
                <small>Dipakai di master obat dan transaksi</small>
            </div>
        </div>
        <div class="satuan-flow-item">
            <i class="mdi mdi-toggle-switch-outline"></i>
            <div>
                <span>Status</span>
                <small>Aktifkan hanya satuan yang digunakan</small>
            </div>
        </div>
    </div>
</section>
