@extends("template.partials.app")

@php
    $documentTemplates = [
        "reguler" => [
            "label" => "Reguler",
            "form" => "Surat Umum",
            "icon" => "mdi-file-document-edit-outline",
            "tone" => "regular",
            "description" => "Obat di luar golongan khusus.",
        ],
        "narkotika" => [
            "label" => "Narkotika",
            "form" => "Formulir 1",
            "icon" => "mdi-alert-octagon-outline",
            "tone" => "narcotic",
            "description" => "Satu surat untuk setiap jenis obat.",
        ],
        "psikotropika" => [
            "label" => "Psikotropika",
            "form" => "Formulir 2",
            "icon" => "mdi-brain",
            "tone" => "psychotropic",
            "description" => "Format khusus obat Psikotropika.",
        ],
        "prekursor" => [
            "label" => "Prekursor",
            "form" => "Formulir 3",
            "icon" => "mdi-flask-outline",
            "tone" => "precursor",
            "description" => "Format bahan Prekursor Farmasi.",
        ],
        "oot" => [
            "label" => "OOT",
            "form" => "Formulir 4",
            "icon" => "mdi-account-check-outline",
            "tone" => "oot",
            "description" => "Format Obat-Obat Tertentu.",
        ],
    ];
    $templateProfile = $selectedBranch?->apotekProfile;
    $templateProfileComplete = $templateProfile?->pharmacist_name
        && $templateProfile?->pharmacist_license_number;
@endphp

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.dokumen.partials.style")
@endpush

@section("content")
    <div class="document-page">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Operasional</a></li>
                <li class="breadcrumb-item">Dokumen</li>
                <li class="breadcrumb-item active" aria-current="page">Surat Pesanan</li>
            </ol>
        </nav>

        <section class="document-command" aria-labelledby="documentPageTitle">
            <div class="document-command-copy">
                <span class="document-kicker">
                    <i class="mdi mdi-shield-check-outline"></i>
                    Pusat Dokumen Apotek
                </span>
                <h2 id="documentPageTitle">Dokumen Surat Pesanan</h2>
                <p>
                    Temukan, periksa, dan cetak seluruh surat pesanan dari purchase order setiap cabang dalam satu
                    ruang kerja yang selalu mengikuti data transaksi.
                </p>
                <div class="document-command-meta">
                    <span class="document-live-status">
                        <i></i>
                        Tersinkron otomatis
                    </span>
                    @if ($selectedBranch)
                        <span>
                            <i class="mdi mdi-storefront-outline"></i>
                            {{ $templateProfile?->name ?: $selectedBranch->name }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="document-command-actions">
                <button type="button" class="btn document-button-secondary" id="openTemplateLibrary">
                    <i class="mdi mdi-file-outline"></i>
                    Template kosong
                </button>
                <button type="button" class="btn document-button-primary" id="refreshDocumentArchive">
                    <i class="mdi mdi-refresh"></i>
                    <span>Perbarui arsip</span>
                </button>
            </div>

            <div class="document-command-art" aria-hidden="true">
                <span class="is-back"><i class="mdi mdi-file-document-outline"></i></span>
                <span class="is-middle"><i class="mdi mdi-pill-multiple"></i></span>
                <span class="is-front"><i class="mdi mdi-file-check-outline"></i></span>
            </div>
        </section>

        <section class="document-overview" aria-label="Ringkasan surat pesanan">
            <button type="button" class="document-total-card is-active" data-document-type="" aria-pressed="true">
                <span class="document-total-icon"><i class="mdi mdi-archive-outline"></i></span>
                <span class="document-total-copy">
                    <small>Seluruh surat pesanan</small>
                    <span><strong id="documentTotalCount">0</strong> dokumen</span>
                    <small><b id="documentSetCount">0</b> kelompok dari <b id="documentPoCount">0</b> PO</small>
                </span>
                <i class="mdi mdi-arrow-right document-total-arrow"></i>
            </button>

            <div class="document-overview-types">
                @foreach ([
                    ["type" => "reguler", "label" => "Reguler", "count" => "documentRegularCount", "icon" => "mdi-file-document-edit-outline", "tone" => "regular"],
                    ["type" => "narkotika", "label" => "Narkotika", "count" => "documentNarcoticCount", "icon" => "mdi-alert-octagon-outline", "tone" => "narcotic"],
                    ["type" => "psikotropika", "label" => "Psikotropika", "count" => "documentPsychotropicCount", "icon" => "mdi-brain", "tone" => "psychotropic"],
                    ["type" => "prekursor", "label" => "Prekursor", "count" => "documentPrecursorCount", "icon" => "mdi-flask-outline", "tone" => "precursor"],
                    ["type" => "oot", "label" => "OOT", "count" => "documentOotCount", "icon" => "mdi-account-check-outline", "tone" => "oot"],
                ] as $metric)
                    <button type="button" class="document-type-metric is-{{ $metric["tone"] }}"
                        data-document-type="{{ $metric["type"] }}" aria-pressed="false">
                        <span class="document-type-metric-icon"><i class="mdi {{ $metric["icon"] }}"></i></span>
                        <span>
                            <small>{{ $metric["label"] }}</small>
                            <strong id="{{ $metric["count"] }}">0</strong>
                        </span>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="document-archive" id="documentArchiveSection" aria-labelledby="documentArchiveTitle">
            <header class="document-archive-heading">
                <div class="document-heading-copy">
                    <span class="document-heading-icon"><i class="mdi mdi-archive-search-outline"></i></span>
                    <div>
                        <span class="document-eyebrow">Arsip aktif</span>
                        <h5 id="documentArchiveTitle">Surat Pesanan</h5>
                        <p>Cari dokumen, periksa item obat, lalu buka pratinjau atau langsung cetak.</p>
                    </div>
                </div>
                <div class="document-archive-state">
                    <span class="document-result-label"><b id="documentResultCount">0</b> kelompok ditampilkan</span>
                    <span class="document-last-sync" id="documentLastSync">
                        <i class="mdi mdi-cloud-sync-outline"></i>
                        Menyiapkan data...
                    </span>
                    <button type="button" class="document-refresh-button" id="refreshDocumentTable"
                        title="Muat ulang arsip" aria-label="Muat ulang arsip">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </header>

            <div class="document-toolbar">
                <label class="document-search" for="documentSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="documentSearch"
                        placeholder="Cari nomor surat, PO, distributor, cabang, atau obat..." autocomplete="off">
                    <span class="document-search-shortcut" aria-hidden="true">/</span>
                    <button type="button" id="clearDocumentSearch" title="Hapus pencarian" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </label>

                <button type="button" class="document-filter-toggle" id="toggleAdvancedFilters"
                    aria-expanded="false" aria-controls="documentAdvancedFilters">
                    <i class="mdi mdi-tune-variant"></i>
                    Filter
                    <span id="documentFilterCount" class="d-none">0</span>
                    <i class="mdi mdi-chevron-down document-filter-chevron"></i>
                </button>

                <div class="document-field document-page-size">
                    <label for="documentPageLength">Tampilkan</label>
                    <select id="documentPageLength" class="form-select">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>
            </div>

            <div class="document-advanced-filters" id="documentAdvancedFilters" aria-hidden="true" inert>
                <div class="document-field">
                    <label for="documentStatusFilter">Status purchase order</label>
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
                <div class="document-field">
                    <label for="documentDateStart">Dari tanggal</label>
                    <input type="date" id="documentDateStart" class="form-control">
                </div>
                <span class="document-date-divider">hingga</span>
                <div class="document-field">
                    <label for="documentDateEnd">Sampai tanggal</label>
                    <input type="date" id="documentDateEnd" class="form-control">
                </div>
                <button type="button" class="document-reset-button" id="resetAdvancedFilters">
                    <i class="mdi mdi-backup-restore"></i>
                    Reset
                </button>
            </div>

            <div class="document-type-tabs" role="tablist" aria-label="Filter jenis surat pesanan">
                <button type="button" class="is-active" data-type="" aria-pressed="true">Semua <span id="documentAllTabCount">0</span></button>
                <button type="button" data-type="reguler" aria-pressed="false"><span class="type-dot is-regular"></span>Reguler <span id="documentRegularTabCount">0</span></button>
                <button type="button" data-type="narkotika" aria-pressed="false"><span class="type-dot is-narcotic"></span>Narkotika <span id="documentNarcoticTabCount">0</span></button>
                <button type="button" data-type="psikotropika" aria-pressed="false"><span class="type-dot is-psychotropic"></span>Psikotropika <span id="documentPsychotropicTabCount">0</span></button>
                <button type="button" data-type="prekursor" aria-pressed="false"><span class="type-dot is-precursor"></span>Prekursor <span id="documentPrecursorTabCount">0</span></button>
                <button type="button" data-type="oot" aria-pressed="false"><span class="type-dot is-oot"></span>OOT <span id="documentOotTabCount">0</span></button>
            </div>

            <div class="document-active-filters d-none" id="documentActiveFilters" aria-live="polite">
                <span>Filter aktif</span>
                <div id="documentFilterChips"></div>
                <button type="button" id="resetDocumentFilters">Hapus semua</button>
            </div>

            <div class="document-archive-alert d-none" id="documentArchiveAlert" role="alert"></div>

            <div class="table-responsive document-table-wrap">
                <table id="documentTable" class="table document-table align-middle">
                    <caption class="visually-hidden">Daftar arsip surat pesanan</caption>
                    <thead>
                        <tr>
                            <th>Dokumen</th>
                            <th>Tanggal</th>
                            <th>Purchase Order</th>
                            <th>Cabang</th>
                            <th>Isi Dokumen</th>
                            <th>Status PO</th>
                            <th width="154">Aksi</th>
                            <th class="d-none">Pencarian</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section class="document-template-library is-collapsed" id="documentTemplateLibrary" aria-labelledby="documentTemplateTitle">
            <header class="document-template-heading">
                <div class="document-template-title">
                    <span><i class="mdi mdi-file-cabinet"></i></span>
                    <div>
                        <span class="document-eyebrow">Format siap pakai</span>
                        <h5 id="documentTemplateTitle">Template Surat Pesanan</h5>
                        <p>Gunakan format kosong dengan identitas apotek dan apoteker yang sudah terisi.</p>
                    </div>
                </div>
                <div class="document-template-controls">
                    <span class="document-template-count">{{ count($documentTemplates) }} format</span>
                    <button type="button" id="toggleDocumentTemplates" aria-expanded="false" aria-controls="documentTemplateContent">
                        <span>Buka template</span>
                        <i class="mdi mdi-chevron-down"></i>
                    </button>
                </div>
            </header>

            <div class="document-template-content" id="documentTemplateContent" aria-hidden="true" inert>
                <div class="document-template-context">
                    <div class="document-template-branch">
                        <label for="documentTemplateBranch">Profil apotek pada template</label>
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

                    @if ($selectedBranch)
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
                    @endif
                </div>

                @if ($selectedBranch)
                    <div class="document-template-grid">
                        @foreach ($documentTemplates as $templateType => $template)
                            @php
                                $templateUrl = route("dokumen.template", ["type" => $templateType, "branch_id" => $selectedBranch->id]);
                                $templatePrintUrl = route("dokumen.template", ["type" => $templateType, "branch_id" => $selectedBranch->id, "print" => 1]);
                            @endphp
                            <article class="document-template-card is-{{ $template["tone"] }}">
                                <span class="document-template-card-icon"><i class="mdi {{ $template["icon"] }}"></i></span>
                                <div class="document-template-card-copy">
                                    <small>{{ $template["form"] }}</small>
                                    <h6>{{ $template["label"] }}</h6>
                                    <p>{{ $template["description"] }}</p>
                                </div>
                                <div class="document-template-card-actions">
                                    <a href="{{ $templateUrl }}" target="_blank" rel="noopener" class="document-template-view">
                                        <i class="mdi mdi-eye-outline"></i> Lihat
                                    </a>
                                    <a href="{{ $templatePrintUrl }}" target="_blank" rel="noopener" class="document-template-print"
                                        title="Cetak template" aria-label="Cetak template {{ $template["label"] }}">
                                        <i class="mdi mdi-printer-outline"></i>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="document-template-empty">
                        <i class="mdi mdi-source-branch-remove"></i>
                        <div>
                            <strong>Profil apotek belum tersedia</strong>
                            <span>Hubungkan user ke cabang terlebih dahulu untuk membuka template.</span>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    @include("medcare.menu.dokumen.partials.detail-modal")
@endsection

@push("scripts")
    @include("medcare.menu.dokumen.partials.script")
@endpush
