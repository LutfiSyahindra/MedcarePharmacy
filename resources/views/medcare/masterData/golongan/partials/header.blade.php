<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
        <li class="breadcrumb-item active" aria-current="page">Golongan</li>
    </ol>
</nav>

<section class="golongan-hero">
    <div>
        <span class="golongan-kicker">
            <i class="mdi mdi-shape-outline"></i>
            Drug Class
        </span>
        <h2>Golongan Obat</h2>
        <p>Kelola kelompok terapi atau klasifikasi obat, lengkap dengan keterangan dan status aktif agar master obat lebih mudah dipetakan.</p>
        <div class="golongan-hero-actions">
            <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#golonganModal">
                <i class="mdi mdi-plus-circle-outline"></i>
                Tambah Golongan
            </button>
            <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#golonganModalExcell">
                <i class="mdi mdi-file-excel-outline"></i>
                Import Excel
            </button>
            <button type="button" class="btn btn-outline-light golongan-refresh-table">
                <i class="mdi mdi-refresh"></i>
                Refresh Data
            </button>
        </div>
    </div>
    <div class="golongan-flow-panel" aria-label="Alur data golongan">
        <div class="golongan-flow-item">
            <i class="mdi mdi-barcode-scan"></i>
            <div>
                <span>Kode</span>
                <small>Identitas singkat golongan</small>
            </div>
        </div>
        <div class="golongan-flow-item">
            <i class="mdi mdi-shape-plus-outline"></i>
            <div>
                <span>Golongan</span>
                <small>Kelompok terapi atau klasifikasi</small>
            </div>
        </div>
        <div class="golongan-flow-item">
            <i class="mdi mdi-text-box-check-outline"></i>
            <div>
                <span>Keterangan</span>
                <small>Catatan ringkas untuk konteks farmasi</small>
            </div>
        </div>
    </div>
</section>
