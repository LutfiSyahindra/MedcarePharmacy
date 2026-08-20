@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.dokumen.partials.style")
    @include("medcare.menu.dokumen.partials.etiket-style")
@endpush

@section("content")
    <div class="document-page etiket-page">
        <nav class="page-breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Operasional</a></li>
                <li class="breadcrumb-item"><a href="{{ route("dokumen.index") }}">Dokumen</a></li>
                <li class="breadcrumb-item active" aria-current="page">Etiket</li>
            </ol>
        </nav>

        <section class="document-hero">
            <div class="document-hero-copy">
                <span class="document-kicker"><i class="mdi mdi-label-multiple-outline"></i> Arsip Etiket Resep</span>
                <h2>Etiket obat tersusun rapi dan siap dicetak ulang.</h2>
                <p>
                    Telusuri etiket resep non-racikan dan racikan dari transaksi POS yang telah selesai di setiap
                    cabang yang dapat Anda akses.
                </p>
                <div class="document-hero-actions">
                    <button type="button" class="btn btn-light" id="scrollLabelArchive">
                        <i class="mdi mdi-format-list-bulleted"></i> Buka Arsip
                    </button>
                    <button type="button" class="btn btn-outline-light" id="refreshLabelArchive">
                        <i class="mdi mdi-refresh"></i> Sinkronkan
                    </button>
                </div>
            </div>
            <div class="document-hero-visual" aria-hidden="true">
                <span class="document-folder-back"></span>
                <span class="document-sheet is-first"><i class="mdi mdi-account-heart-outline"></i></span>
                <span class="document-sheet is-second"><i class="mdi mdi-label-outline"></i></span>
                <span class="document-folder-front"><i class="mdi mdi-pill-multiple"></i></span>
            </div>
        </section>

        <div class="document-sync-note" role="note">
            <i class="mdi mdi-link-variant"></i>
            <div>
                <strong>Terhubung langsung dengan resep POS</strong>
                <span>Arsip hanya memuat transaksi resep selesai, sehingga isi etiket selalu mengikuti data transaksi sumber.</span>
            </div>
        </div>

        <section class="document-template-library" id="labelTemplateLibrary">
            <div class="document-template-heading">
                <div class="document-template-title">
                    <span><i class="mdi mdi-label-outline"></i></span>
                    <div>
                        <small>Format Kosong</small>
                        <h5>Template Etiket</h5>
                        <p>Lihat atau cetak etiket kosong; nama, logo, dan kontak apotek tetap terisi.</p>
                    </div>
                </div>
                <div class="document-template-branch">
                    <label for="labelTemplateBranch">Profil apotek</label>
                    <select id="labelTemplateBranch" class="form-select" {{ $branches->isEmpty() ? "disabled" : "" }}>
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
                    $templateProfileComplete = $templateProfile?->name && $templateProfile?->phone;
                @endphp
                <div class="document-template-profile {{ $templateProfileComplete ? "is-complete" : "is-warning" }}">
                    <i class="mdi {{ $templateProfileComplete ? "mdi-store-check-outline" : "mdi-alert-outline" }}"></i>
                    <div>
                        <strong>{{ $templateProfile?->name ?: $selectedBranch->name }}</strong>
                        <span>
                            {{ $templateProfile?->phone ?: "Nomor telepon belum dilengkapi" }}
                            &middot; {{ $templateProfile?->logo_path ? "Logo apotek siap digunakan" : "Logo menggunakan bawaan Medcare" }}
                        </span>
                    </div>
                </div>

                <div class="document-template-grid label-template-grid">
                    @foreach ([
                        "non-racikan" => [
                            "label" => "Etiket Non Racikan",
                            "description" => "Format kosong untuk satu jenis obat resep.",
                            "icon" => "mdi-pill",
                            "tone" => "non-compound",
                        ],
                        "racikan" => [
                            "label" => "Etiket Racikan",
                            "description" => "Format kosong untuk satu kelompok obat racikan.",
                            "icon" => "mdi-mortar-pestle-plus",
                            "tone" => "compound",
                        ],
                    ] as $templateType => $template)
                        @php
                            $templateUrl = route("dokumen.etiket.template", [
                                "type" => $templateType,
                                "branch_id" => $selectedBranch->id,
                            ]);
                            $templatePrintUrl = route("dokumen.etiket.template", [
                                "type" => $templateType,
                                "branch_id" => $selectedBranch->id,
                                "print" => 1,
                            ]);
                        @endphp
                        <article class="document-template-card is-{{ $template["tone"] }}">
                            <span class="document-template-card-icon"><i class="mdi {{ $template["icon"] }}"></i></span>
                            <div class="document-template-card-copy">
                                <small>Thermal 80 mm</small>
                                <h6>{{ $template["label"] }}</h6>
                                <p>{{ $template["description"] }}</p>
                            </div>
                            <div class="document-template-card-actions">
                                <a href="{{ $templateUrl }}" target="_blank" rel="noopener" class="btn btn-outline-success">
                                    <i class="mdi mdi-eye-outline"></i> Lihat Template
                                </a>
                                <a href="{{ $templatePrintUrl }}" target="_blank" rel="noopener"
                                    class="document-template-print" title="Cetak template"
                                    aria-label="Cetak template {{ $template["label"] }}">
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

        <section class="document-metrics" aria-label="Ringkasan etiket">
            <button type="button" class="document-metric is-label-total" data-label-type="">
                <span class="document-metric-icon"><i class="mdi mdi-label-multiple-outline"></i></span>
                <span class="document-metric-copy">
                    <small>Seluruh Etiket</small>
                    <strong id="labelTotalCount">0</strong>
                    <span>Dari <b id="labelSetCount">0</b> transaksi resep</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-non-compound" data-label-type="penjualan_resep">
                <span class="document-metric-icon"><i class="mdi mdi-pill"></i></span>
                <span class="document-metric-copy">
                    <small>Etiket Resep</small>
                    <strong id="labelNonCompoundCount">0</strong>
                    <span>Non racikan</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-compound" data-label-type="penjualan_racikan">
                <span class="document-metric-icon"><i class="mdi mdi-mortar-pestle-plus"></i></span>
                <span class="document-metric-copy">
                    <small>Etiket Resep</small>
                    <strong id="labelCompoundCount">0</strong>
                    <span>Racikan</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
            <button type="button" class="document-metric is-label-set" data-label-type="">
                <span class="document-metric-icon"><i class="mdi mdi-prescription"></i></span>
                <span class="document-metric-copy">
                    <small>Set Etiket</small>
                    <strong id="labelPrescriptionCount">0</strong>
                    <span>Transaksi resep selesai</span>
                </span>
                <i class="mdi mdi-chevron-right document-metric-arrow"></i>
            </button>
        </section>

        <section class="document-archive" id="labelArchiveSection">
            <div class="document-archive-heading">
                <div>
                    <span class="document-heading-icon"><i class="mdi mdi-archive-search-outline"></i></span>
                    <div>
                        <h5>Arsip Etiket</h5>
                        <p>Cari resep, periksa isi etiket, lalu buka pratinjau atau langsung cetak ulang.</p>
                    </div>
                </div>
                <button type="button" class="document-refresh-button" id="refreshLabelTable"
                    title="Muat ulang arsip" aria-label="Muat ulang arsip etiket">
                    <i class="mdi mdi-refresh"></i>
                </button>
            </div>

            <div class="document-toolbar">
                <label class="document-search" for="labelSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="labelSearch"
                        placeholder="Cari transaksi, resep, pasien, dokter, cabang, atau obat..." autocomplete="off">
                    <button type="button" id="clearLabelSearch" title="Hapus pencarian" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </label>

                @if ($branches->count() > 1)
                    <div class="document-field label-branch-field">
                        <label for="labelBranchFilter">Cabang</label>
                        <select id="labelBranchFilter" class="form-select">
                            <option value="">Semua cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" id="labelBranchFilter" value="{{ $branches->first()?->id }}">
                @endif

                <div class="document-period">
                    <div class="document-field">
                        <label for="labelDateStart">Dari tanggal</label>
                        <input type="date" id="labelDateStart" class="form-control">
                    </div>
                    <span>s.d.</span>
                    <div class="document-field">
                        <label for="labelDateEnd">Sampai tanggal</label>
                        <input type="date" id="labelDateEnd" class="form-control">
                    </div>
                </div>

                <div class="document-field document-page-size">
                    <label for="labelPageLength">Baris</label>
                    <select id="labelPageLength" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="document-type-tabs" role="tablist" aria-label="Filter jenis etiket">
                <button type="button" class="is-active" data-type="" aria-pressed="true">Semua</button>
                <button type="button" data-type="penjualan_resep" aria-pressed="false">
                    <span class="type-dot is-non-compound"></span>Non Racikan
                </button>
                <button type="button" data-type="penjualan_racikan" aria-pressed="false">
                    <span class="type-dot is-compound"></span>Racikan
                </button>
                <button type="button" class="document-reset-filter" id="resetLabelFilters">
                    <i class="mdi mdi-filter-remove-outline"></i> Reset filter
                </button>
            </div>

            <div class="table-responsive document-table-wrap">
                <table id="labelTable" class="table document-table align-middle">
                    <thead>
                        <tr>
                            <th width="48">No</th>
                            <th>Referensi</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Pasien & Resep</th>
                            <th>Cabang</th>
                            <th>Isi Etiket</th>
                            <th>Petugas</th>
                            <th width="92">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.dokumen.partials.etiket-script")
@endpush
