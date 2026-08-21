@extends("template.partials.app")

@php
    $receiptTypes = [
        "penjualan_bebas" => [
            "label" => "Penjualan Bebas",
            "short" => "Bebas",
            "description" => "Nota kasir untuk obat dan produk tanpa resep.",
            "icon" => "mdi-cart-outline",
            "tone" => "regular",
        ],
        "penjualan_resep" => [
            "label" => "Resep Non Racikan",
            "short" => "Non Racikan",
            "description" => "Nota resep per obat beserta aturan pakai.",
            "icon" => "mdi-prescription",
            "tone" => "psychotropic",
        ],
        "penjualan_racikan" => [
            "label" => "Resep Racikan",
            "short" => "Racikan",
            "description" => "Salinan pasien dan lembar peracikan apotek.",
            "icon" => "mdi-mortar-pestle-plus",
            "tone" => "precursor",
        ],
        "penjualan_kredit" => [
            "label" => "Penjualan Kredit",
            "short" => "Kredit",
            "description" => "Nota dengan pembayaran dan sisa tagihan.",
            "icon" => "mdi-credit-card-clock-outline",
            "tone" => "narcotic",
        ],
        "penjualan_instansi" => [
            "label" => "Penjualan Instansi",
            "short" => "Instansi",
            "description" => "Nota transaksi dan referensi pembayaran instansi.",
            "icon" => "mdi-domain",
            "tone" => "oot",
        ],
    ];
    $templateProfile = $selectedBranch?->apotekProfile;
    $templateProfileComplete = $templateProfile?->name && $templateProfile?->phone;
@endphp

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("medcare.menu.dokumen.partials.style")
    @include("medcare.menu.dokumen.partials.nota-style")
@endpush

@section("content")
    <div class="document-page nota-page">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Operasional</a></li>
                <li class="breadcrumb-item">Dokumen</li>
                <li class="breadcrumb-item active" aria-current="page">Nota</li>
            </ol>
        </nav>

        <section class="document-command" aria-labelledby="notaPageTitle">
            <div class="document-command-copy">
                <span class="document-kicker"><i class="mdi mdi-receipt-text-check-outline"></i> Arsip Nota Penjualan</span>
                <h2 id="notaPageTitle">Nota setiap transaksi, siap ditemukan dan dicetak ulang.</h2>
                <p>
                    Telusuri transaksi POS selesai berdasarkan jenis transaksi, cabang, periode, pelanggan, resep,
                    instansi, atau item yang tercantum pada nota.
                </p>
                <div class="document-command-meta">
                    <span class="document-live-status"><i></i> Terhubung langsung dengan POS</span>
                    @if ($selectedBranch)
                        <span><i class="mdi mdi-storefront-outline"></i> {{ $templateProfile?->name ?: $selectedBranch->name }}</span>
                    @endif
                </div>
            </div>

            <div class="document-command-actions">
                <button type="button" class="btn document-button-secondary" id="openReceiptTemplates">
                    <i class="mdi mdi-file-outline"></i> Template nota
                </button>
                <button type="button" class="btn document-button-primary" id="refreshReceiptArchive">
                    <i class="mdi mdi-refresh"></i> <span>Perbarui arsip</span>
                </button>
            </div>

            <div class="document-command-art" aria-hidden="true">
                <span class="is-back"><i class="mdi mdi-cart-outline"></i></span>
                <span class="is-middle"><i class="mdi mdi-prescription"></i></span>
                <span class="is-front"><i class="mdi mdi-receipt-text-check-outline"></i></span>
            </div>
        </section>

        <section class="document-overview" aria-label="Ringkasan nota">
            <button type="button" class="document-total-card is-active" data-receipt-type="" aria-pressed="true">
                <span class="document-total-icon"><i class="mdi mdi-receipt-text-multiple-outline"></i></span>
                <span class="document-total-copy">
                    <small>Seluruh nota</small>
                    <span><strong id="receiptTotalCount">0</strong> transaksi</span>
                    <small>Nilai <b id="receiptGrandTotal">Rp0</b></small>
                </span>
                <i class="mdi mdi-arrow-right document-total-arrow"></i>
            </button>

            <div class="document-overview-types">
                @foreach ($receiptTypes as $type => $meta)
                    <button type="button" class="document-type-metric is-{{ $meta["tone"] }}"
                        data-receipt-type="{{ $type }}" aria-pressed="false">
                        <span class="document-type-metric-icon"><i class="mdi {{ $meta["icon"] }}"></i></span>
                        <span>
                            <small>{{ $meta["short"] }}</small>
                            <strong id="receiptMetric{{ \Illuminate\Support\Str::studly($type) }}">0</strong>
                        </span>
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="document-archive" id="receiptArchiveSection" aria-labelledby="receiptArchiveTitle">
            <header class="document-archive-heading">
                <div class="document-heading-copy">
                    <span class="document-heading-icon"><i class="mdi mdi-archive-search-outline"></i></span>
                    <div>
                        <span class="document-eyebrow">Transaksi POS selesai</span>
                        <h5 id="receiptArchiveTitle">Arsip Nota</h5>
                        <p>Gunakan filter jenis transaksi untuk menemukan nota racikan, non-racikan, dan transaksi lainnya.</p>
                    </div>
                </div>
                <div class="document-archive-state">
                    <span class="document-result-label"><b id="receiptResultCount">0</b> nota ditampilkan</span>
                    <span class="document-last-sync" id="receiptLastSync"><i class="mdi mdi-cloud-sync-outline"></i> Menyiapkan data...</span>
                    <button type="button" class="document-refresh-button" id="refreshReceiptTable"
                        title="Muat ulang arsip" aria-label="Muat ulang arsip nota">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </header>

            <div class="document-toolbar">
                <label class="document-search" for="receiptSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="receiptSearch"
                        placeholder="Cari nomor nota, pelanggan, resep, instansi, cabang, atau obat..." autocomplete="off">
                    <span class="document-search-shortcut" aria-hidden="true">/</span>
                    <button type="button" id="clearReceiptSearch" title="Hapus pencarian" aria-label="Hapus pencarian">
                        <i class="mdi mdi-close"></i>
                    </button>
                </label>

                <button type="button" class="document-filter-toggle" id="toggleReceiptFilters"
                    aria-expanded="false" aria-controls="receiptAdvancedFilters">
                    <i class="mdi mdi-tune-variant"></i> Filter
                    <span id="receiptFilterCount" class="d-none">0</span>
                    <i class="mdi mdi-chevron-down document-filter-chevron"></i>
                </button>

                <div class="document-field document-page-size">
                    <label for="receiptPageLength">Tampilkan</label>
                    <select id="receiptPageLength" class="form-select">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                </div>
            </div>

            <div class="document-advanced-filters" id="receiptAdvancedFilters" aria-hidden="true" inert>
                @if ($branches->count() > 1)
                    <div class="document-field">
                        <label for="receiptBranchFilter">Cabang</label>
                        <select id="receiptBranchFilter" class="form-select">
                            <option value="">Semua cabang</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" id="receiptBranchFilter" value="{{ $branches->first()?->id }}">
                @endif
                <div class="document-field">
                    <label for="receiptDateStart">Dari tanggal</label>
                    <input type="date" id="receiptDateStart" class="form-control">
                </div>
                <span class="document-date-divider">hingga</span>
                <div class="document-field">
                    <label for="receiptDateEnd">Sampai tanggal</label>
                    <input type="date" id="receiptDateEnd" class="form-control">
                </div>
                <button type="button" class="document-reset-button" id="resetReceiptAdvancedFilters">
                    <i class="mdi mdi-backup-restore"></i> Reset
                </button>
            </div>

            <div class="document-type-tabs" role="tablist" aria-label="Filter jenis transaksi nota">
                <button type="button" class="is-active" data-type="" aria-pressed="true">
                    Semua <span id="receiptTabAll">0</span>
                </button>
                @foreach ($receiptTypes as $type => $meta)
                    <button type="button" data-type="{{ $type }}" aria-pressed="false">
                        <span class="type-dot is-{{ $meta["tone"] }}"></span>{{ $meta["short"] }}
                        <span id="receiptTab{{ \Illuminate\Support\Str::studly($type) }}">0</span>
                    </button>
                @endforeach
            </div>

            <div class="document-active-filters d-none" id="receiptActiveFilters" aria-live="polite">
                <span>Filter aktif</span>
                <div id="receiptFilterChips"></div>
                <button type="button" id="resetReceiptFilters">Hapus semua</button>
            </div>

            <div class="document-archive-alert d-none" id="receiptArchiveAlert" role="alert"></div>

            <div class="table-responsive document-table-wrap">
                <table id="receiptTable" class="table document-table align-middle">
                    <caption class="visually-hidden">Daftar arsip nota transaksi penjualan</caption>
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Jenis Transaksi</th>
                            <th>Tanggal</th>
                            <th>Pelanggan / Resep</th>
                            <th>Isi Nota</th>
                            <th>Total</th>
                            <th>Cabang</th>
                            <th width="96">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>

        <section class="document-template-library is-collapsed" id="receiptTemplateLibrary" aria-labelledby="receiptTemplateTitle">
            <header class="document-template-heading">
                <div class="document-template-title">
                    <span><i class="mdi mdi-receipt-text-outline"></i></span>
                    <div>
                        <span class="document-eyebrow">Thermal 80 mm</span>
                        <h5 id="receiptTemplateTitle">Template Nota per Jenis Transaksi</h5>
                        <p>Pratinjau format nota dengan identitas apotek dan field khusus setiap transaksi.</p>
                    </div>
                </div>
                <div class="document-template-controls">
                    <span class="document-template-count">{{ count($receiptTypes) }} format</span>
                    <button type="button" id="toggleReceiptTemplates" aria-expanded="false" aria-controls="receiptTemplateContent">
                        <span>Buka template</span><i class="mdi mdi-chevron-down"></i>
                    </button>
                </div>
            </header>

            <div class="document-template-content" id="receiptTemplateContent" aria-hidden="true" inert>
                <div class="document-template-context">
                    <div class="document-template-branch">
                        <label for="receiptTemplateBranch">Profil apotek pada template</label>
                        <select id="receiptTemplateBranch" class="form-select" {{ $branches->isEmpty() ? "disabled" : "" }}>
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
                            <i class="mdi {{ $templateProfileComplete ? "mdi-store-check-outline" : "mdi-alert-outline" }}"></i>
                            <div>
                                <strong>{{ $templateProfile?->name ?: $selectedBranch->name }}</strong>
                                <span>{{ $templateProfile?->phone ?: "Nomor telepon belum dilengkapi" }} &middot; {{ $templateProfile?->logo_path ? "Logo apotek siap" : "Logo bawaan Medcare" }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($selectedBranch)
                    <div class="document-template-grid">
                        @foreach ($receiptTypes as $type => $meta)
                            @php
                                $templateUrl = route("dokumen.nota.template", ["type" => $type, "branch_id" => $selectedBranch->id]);
                                $templatePrintUrl = route("dokumen.nota.template", ["type" => $type, "branch_id" => $selectedBranch->id, "print" => 1]);
                            @endphp
                            <article class="document-template-card is-{{ $meta["tone"] }}">
                                <span class="document-template-card-icon"><i class="mdi {{ $meta["icon"] }}"></i></span>
                                <div class="document-template-card-copy">
                                    <small>Nota {{ $meta["short"] }}</small>
                                    <h6>{{ $meta["label"] }}</h6>
                                    <p>{{ $meta["description"] }}</p>
                                </div>
                                <div class="document-template-card-actions">
                                    <a href="{{ $templateUrl }}" target="_blank" rel="noopener" class="document-template-view">
                                        <i class="mdi mdi-eye-outline"></i> Lihat
                                    </a>
                                    <a href="{{ $templatePrintUrl }}" target="_blank" rel="noopener" class="document-template-print"
                                        title="Cetak template" aria-label="Cetak template {{ $meta["label"] }}">
                                        <i class="mdi mdi-printer-outline"></i>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="document-template-empty">
                        <i class="mdi mdi-source-branch-remove"></i>
                        <div><strong>Profil apotek belum tersedia</strong><span>Hubungkan user ke cabang untuk membuka template nota.</span></div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.menu.dokumen.partials.nota-script")
@endpush
