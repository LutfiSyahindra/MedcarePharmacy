@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.dokumen.partials.style")
@endpush

@section("content")
    <div class="document-page">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Operasional</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dokumen</li>
            </ol>
        </nav>

        <section class="document-hero">
            <div class="document-hero-copy">
                <span class="document-kicker"><i class="mdi mdi-folder-multiple-outline"></i> Pusat Dokumen Apotek</span>
                <h2>Seluruh dokumen penting apotek, tersusun dalam satu arsip.</h2>
                <p>
                    Telusuri Surat Pesanan Narkotika, Psikotropika, dan Prekursor yang terbentuk dari purchase order
                    setiap cabang.
                </p>
                <div class="document-hero-actions">
                    <button type="button" class="btn btn-light" id="scrollDocumentArchive">
                        <i class="mdi mdi-format-list-bulleted"></i> Buka Arsip
                    </button>
                    <button type="button" class="btn btn-outline-light" id="refreshDocumentArchive">
                        <i class="mdi mdi-refresh"></i> Sinkronkan
                    </button>
                </div>
            </div>
            <div class="document-hero-visual" aria-hidden="true">
                <span class="document-folder-back"></span>
                <span class="document-sheet is-first"><i class="mdi mdi-pill-multiple"></i></span>
                <span class="document-sheet is-second"><i class="mdi mdi-file-check-outline"></i></span>
                <span class="document-folder-front"><i class="mdi mdi-shield-check-outline"></i></span>
            </div>
        </section>

        <div class="document-sync-note" role="note">
            <i class="mdi mdi-link-variant"></i>
            <div>
                <strong>Arsip tersinkronisasi otomatis</strong>
                <span>Dokumen mengikuti data dan status PO sumber sehingga tidak ada salinan data transaksi.</span>
            </div>
        </div>

        <section class="document-template-library" id="documentTemplateLibrary">
            <div class="document-template-heading">
                <div class="document-template-title">
                    <span><i class="mdi mdi-file-outline"></i></span>
                    <div>
                        <small>Format Kosong</small>
                        <h5>Template Surat Pesanan</h5>
                        <p>Lihat atau cetak format tanpa data distributor dan obat; identitas apotek tetap terisi.</p>
                    </div>
                </div>
                <div class="document-template-branch">
                    <label for="documentTemplateBranch">Profil apotek</label>
                    <select id="documentTemplateBranch" class="form-select" {{ $branches->isEmpty() ? "disabled" : "" }}>
                        @forelse ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranch?->id === $branch->id)>
                                {{ $branch->apotekProfile?->name ?: $branch->name }}
                            </option>
                        @empty
                            <option value="">Tidak ada cabang</option>
                        @endforelse
                    </select>
                </div>
            </div>

            @if ($selectedBranch)
                @php
                    $templateProfile = $selectedBranch->apotekProfile;
                    $templateProfileComplete = $templateProfile?->pharmacist_name
                        && $templateProfile?->pharmacist_license_number;
                @endphp
                <div class="document-template-profile {{ $templateProfileComplete ? "is-complete" : "is-warning" }}">
                    <i class="mdi {{ $templateProfileComplete ? "mdi-account-check-outline" : "mdi-alert-outline" }}"></i>
                    <div>
                        <strong>{{ $templateProfile?->name ?: $selectedBranch->name }}</strong>
                        <span>
                            {{ $templateProfile?->pharmacist_name ?: "Nama apoteker belum dilengkapi" }}
                            &middot; SIPA {{ $templateProfile?->pharmacist_license_number ?: "belum dilengkapi" }}
                        </span>
                    </div>
                </div>

                <div class="document-template-grid">
                    @foreach ([
                        "narkotika" => ["label" => "Narkotika", "form" => "Formulir 1", "icon" => "mdi-alert-octagon-outline", "tone" => "narcotic"],
                        "psikotropika" => ["label" => "Psikotropika", "form" => "Formulir 2", "icon" => "mdi-brain", "tone" => "psychotropic"],
                        "prekursor" => ["label" => "Prekursor", "form" => "Formulir 3", "icon" => "mdi-flask-outline", "tone" => "precursor"],
                    ] as $templateType => $template)
                        @php
                            $templateUrl = route("dokumen.template", [
                                "type" => $templateType,
                                "branch_id" => $selectedBranch->id,
                            ]);
                            $templatePrintUrl = route("dokumen.template", [
                                "type" => $templateType,
                                "branch_id" => $selectedBranch->id,
                                "print" => 1,
                            ]);
                        @endphp
                        <article class="document-template-card is-{{ $template["tone"] }}">
                            <span class="document-template-card-icon"><i class="mdi {{ $template["icon"] }}"></i></span>
                            <div class="document-template-card-copy">
                                <small>{{ $template["form"] }}</small>
                                <h6>Surat Pesanan {{ $template["label"] }}</h6>
                                <p>Format kosong dengan identitas apotek dan apoteker.</p>
                            </div>
                            <div class="document-template-card-actions">
                                <a href="{{ $templateUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                                    <i class="mdi mdi-eye-outline"></i> Lihat Template
                                </a>
                                <a href="{{ $templatePrintUrl }}" target="_blank" rel="noopener"
                                    class="document-template-print" title="Cetak template" aria-label="Cetak template {{ $template["label"] }}">
                                    <i class="mdi mdi-printer-outline"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="document-template-empty">
                    <i class="mdi mdi-source-branch-remove"></i>
                    <div><strong>Profil apotek belum tersedia</strong><span>Hubungkan user ke cabang terlebih dahulu.</span></div>
                </div>
            @endif
        </section>

        <section class="document-metrics" aria-label="Ringkasan dokumen">
            <button type="button" class="document-metric is-total" data-document-type="">
                <span class="document-metric-icon"><i class="mdi mdi-file-document-multiple-outline"></i></span>
                <span class="document-metric-copy">
                    <small>Seluruh Dokumen</small>
                    <strong id="documentTotalCount">0</strong>
                    <span><b id="documentSetCount">0</b> kelompok dari <b id="documentPoCount">0</b> PO</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-narcotic" data-document-type="narkotika">
                <span class="document-metric-icon"><i class="mdi mdi-alert-octagon-outline"></i></span>
                <span class="document-metric-copy">
                    <small>Surat Pesanan</small>
                    <strong id="documentNarcoticCount">0</strong>
                    <span>Narkotika</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-psychotropic" data-document-type="psikotropika">
                <span class="document-metric-icon"><i class="mdi mdi-brain"></i></span>
                <span class="document-metric-copy">
                    <small>Surat Pesanan</small>
                    <strong id="documentPsychotropicCount">0</strong>
                    <span>Psikotropika</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-precursor" data-document-type="prekursor">
                <span class="document-metric-icon"><i class="mdi mdi-flask-outline"></i></span>
                <span class="document-metric-copy">
                    <small>Surat Pesanan</small>
                    <strong id="documentPrecursorCount">0</strong>
                    <span>Prekursor</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
        </section>

        <section class="document-archive" id="documentArchiveSection">
            <div class="document-archive-heading">
                <div>
                    <span class="document-heading-icon"><i class="mdi mdi-archive-search-outline"></i></span>
                    <div>
                        <h5>Arsip Surat Pesanan</h5>
                        <p>Cari dokumen, periksa item, kemudian buka pratinjau atau langsung cetak.</p>
                    </div>
                </div>
                <button type="button" class="document-refresh-button" id="refreshDocumentTable"
                    title="Muat ulang arsip" aria-label="Muat ulang arsip">
                    <i class="mdi mdi-refresh"></i>
                </button>
            </div>

            <div class="document-toolbar">
                <label class="document-search" for="documentSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="documentSearch"
                        placeholder="Cari nomor dokumen, PO, distributor, cabang, atau obat..." autocomplete="off">
                    <button type="button" id="clearDocumentSearch" title="Hapus pencarian" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </label>

                <div class="document-field">
                    <label for="documentStatusFilter">Status PO</label>
                    <select id="documentStatusFilter" class="form-select">
                        <option value="">Semua status</option>
                        <option value="draft">Draft</option>
                        <option value="waiting_approval">Menunggu Approval</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="diterima_sebagian">Diterima Sebagian</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                <div class="document-period">
                    <div class="document-field">
                        <label for="documentDateStart">Dari tanggal</label>
                        <input type="date" id="documentDateStart" class="form-control">
                    </div>
                    <span>s.d.</span>
                    <div class="document-field">
                        <label for="documentDateEnd">Sampai tanggal</label>
                        <input type="date" id="documentDateEnd" class="form-control">
                    </div>
                </div>

                <div class="document-field document-page-size">
                    <label for="documentPageLength">Baris</label>
                    <select id="documentPageLength" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="document-type-tabs" role="tablist" aria-label="Filter jenis surat pesanan">
                <button type="button" class="is-active" data-type="" aria-pressed="true">Semua</button>
                <button type="button" data-type="narkotika" aria-pressed="false"><span class="type-dot is-narcotic"></span>Narkotika</button>
                <button type="button" data-type="psikotropika" aria-pressed="false"><span class="type-dot is-psychotropic"></span>Psikotropika</button>
                <button type="button" data-type="prekursor" aria-pressed="false"><span class="type-dot is-precursor"></span>Prekursor</button>
                <button type="button" class="document-reset-filter" id="resetDocumentFilters">
                    <i class="mdi mdi-filter-remove-outline"></i> Reset filter
                </button>
            </div>

            <div class="table-responsive document-table-wrap">
                <table id="documentTable" class="table document-table align-middle">
                    <thead>
                        <tr>
                            <th width="48">No</th>
                            <th>Nomor Dokumen</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Referensi</th>
                            <th>Cabang</th>
                            <th>Isi Dokumen</th>
                            <th>Status PO</th>
                            <th width="126">Aksi</th>
                            <th class="d-none">Pencarian</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>

    @include("medcare.menu.dokumen.partials.detail-modal")
@endsection

@push("scripts")
    @include("medcare.menu.dokumen.partials.script")
@endpush
