@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.settings.suratPesanan.style")
@endpush

@section("content")
    @php
        $typeMeta = [
            "regular" => ["icon" => "mdi-file-document-outline", "tone" => "regular"],
            "narkotika" => ["icon" => "mdi-alert-octagon-outline", "tone" => "narkotika"],
            "psikotropika" => ["icon" => "mdi-brain", "tone" => "psikotropika"],
            "oot" => ["icon" => "mdi-account-check-outline", "tone" => "oot"],
            "prekursor" => ["icon" => "mdi-flask-outline", "tone" => "prekursor"],
        ];
    @endphp

    <div class="sp-settings-page">
        <nav class="page-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Setting SP</li>
            </ol>
        </nav>

        <section class="sp-hero">
            <div class="sp-hero-copy">
                <span class="sp-kicker"><i class="mdi mdi-file-cog-outline"></i> Klasifikasi Surat Pesanan</span>
                <h4>Setting SP</h4>
                <p>Tentukan jenis Surat Pesanan berdasarkan Golongan, Main Golongan, atau Sub Golongan obat.</p>
                <div class="sp-priority-note">
                    <i class="mdi mdi-layers-triple-outline"></i>
                    <span>Prioritas berlaku dari <strong>Sub Golongan</strong>, lalu <strong>Main Golongan</strong>, kemudian <strong>Golongan</strong>.</span>
                </div>
            </div>
            <div class="sp-hero-actions">
                <button type="button" class="btn sp-secondary-button" id="spResetChanges" disabled>
                    <i class="mdi mdi-undo-variant"></i> Batalkan Perubahan
                </button>
                <button type="submit" form="spSettingsForm" class="btn sp-save-button" id="spSaveButton" disabled>
                    <i class="mdi mdi-content-save-outline"></i>
                    <span>Simpan Setting</span>
                    <b id="spDirtyCount" class="d-none">0</b>
                </button>
            </div>
        </section>

        <section class="sp-summary-grid" aria-label="Ringkasan setting SP">
            <button type="button" class="sp-summary-card is-overview" data-filter-type="all">
                <span class="sp-summary-icon"><i class="mdi mdi-format-list-bulleted-square"></i></span>
                <span><strong>{{ number_format($classificationCount, 0, ",", ".") }}</strong><small>Total klasifikasi</small></span>
                <em><span id="spConfiguredCount">{{ number_format($configuredCount, 0, ",", ".") }}</span> diatur</em>
            </button>
            @foreach (["narkotika", "psikotropika", "oot", "prekursor"] as $type)
                <button type="button" class="sp-summary-card is-{{ $typeMeta[$type]["tone"] }}" data-filter-type="{{ $type }}">
                    <span class="sp-summary-icon"><i class="mdi {{ $typeMeta[$type]["icon"] }}"></i></span>
                    <span>
                        <strong data-summary-count="{{ $type }}">{{ number_format($typeCounts[$type] ?? 0, 0, ",", ".") }}</strong>
                        <small>{{ $typeLabels[$type] }}</small>
                    </span>
                    <em>aturan langsung</em>
                </button>
            @endforeach
        </section>

        <section class="sp-workspace">
            <div class="sp-toolbar">
                <div class="sp-search-wrap">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="spSearch" class="form-control" placeholder="Cari kode, nama, atau induk golongan..." autocomplete="off">
                    <button type="button" id="spClearSearch" aria-label="Hapus pencarian"><i class="mdi mdi-close"></i></button>
                </div>
                <select id="spLevelFilter" class="form-select" aria-label="Filter tingkat">
                    <option value="all">Semua tingkat</option>
                    <option value="golongan">Golongan</option>
                    <option value="main_golongan">Main Golongan</option>
                    <option value="sub_golongan">Sub Golongan</option>
                </select>
                <select id="spTypeFilter" class="form-select" aria-label="Filter jenis SP">
                    <option value="all">Semua jenis SP</option>
                    @foreach ($typeLabels as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="sp-result-count"><strong id="spVisibleCount">{{ $classificationCount }}</strong><span>ditampilkan</span></div>
            </div>

            <div class="sp-bulk-bar">
                <div>
                    <i class="mdi mdi-playlist-edit"></i>
                    <span>Terapkan jenis ke semua hasil yang sedang tampil</span>
                </div>
                <div class="sp-bulk-controls">
                    <select id="spBulkType" class="form-select">
                        <option value="">Otomatis / Ikuti Induk</option>
                        @foreach ($typeLabels as $type => $label)
                            <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn" id="spApplyBulk"><i class="mdi mdi-check-all"></i> Terapkan</button>
                </div>
            </div>

            <form id="spSettingsForm">
                @csrf
                @method("PUT")
                <div class="table-responsive sp-table-wrap">
                    <table class="table sp-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Klasifikasi</th>
                                <th>Tingkat</th>
                                <th class="text-center">Obat</th>
                                <th>Hasil Berlaku</th>
                                <th>Setting SP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="sp-setting-row"
                                    data-key="{{ $row["level"] }}:{{ $row["id"] }}"
                                    data-parent-key="{{ $row["parent_key"] }}"
                                    data-level="{{ $row["level"] }}"
                                    data-automatic-type="{{ $row["automatic_type"] }}"
                                    data-effective-type="{{ $row["effective_type"] }}"
                                    data-search="{{ Illuminate\Support\Str::lower($row["kode"]." ".$row["nama"]." ".$row["parent_path"]." ".$row["level_label"]) }}">
                                    <td>
                                        <div class="sp-classification sp-depth-{{ $row["depth"] }}">
                                            <span class="sp-level-node"><i class="mdi {{ $row["depth"] === 0 ? "mdi-folder-outline" : ($row["depth"] === 1 ? "mdi-folder-multiple-outline" : "mdi-file-tree-outline") }}"></i></span>
                                            <div>
                                                <strong>{{ $row["nama"] }}</strong>
                                                <small><code>{{ $row["kode"] }}</code>@if ($row["parent_path"])<span>{{ $row["parent_path"] }}</span>@endif</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="sp-level-badge is-{{ $row["level"] }}">{{ $row["level_label"] }}</span></td>
                                    <td class="text-center"><span class="sp-medicine-count">{{ number_format($row["medicine_count"], 0, ",", ".") }}</span></td>
                                    <td>
                                        <div class="sp-effective-wrap">
                                            <span class="sp-type-badge is-{{ $row["effective_type"] }}" data-effective-badge>
                                                <i class="mdi {{ $typeMeta[$row["effective_type"]]["icon"] }}"></i>
                                                <span>{{ $typeLabels[$row["effective_type"]] }}</span>
                                            </span>
                                            <small data-source-label>{{ $row["source"] }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-select sp-type-select"
                                            data-level="{{ $row["level"] }}"
                                            data-id="{{ $row["id"] }}"
                                            data-initial="{{ $row["configured_type"] }}"
                                            aria-label="Setting SP untuk {{ $row["nama"] }}">
                                            <option value="">Otomatis / Ikuti Induk</option>
                                            @foreach ($typeLabels as $type => $label)
                                                <option value="{{ $type }}" @selected($row["configured_type"] === $type)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="sp-empty-state"><i class="mdi mdi-database-off-outline"></i><strong>Belum ada klasifikasi obat</strong><span>Tambahkan Golongan pada Master Data terlebih dahulu.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="sp-no-results d-none" id="spNoResults">
                <i class="mdi mdi-magnify-close"></i>
                <strong>Tidak ada klasifikasi yang cocok</strong>
                <span>Coba ubah kata pencarian atau filter.</span>
            </div>

            <footer class="sp-workspace-footer">
                <span><i class="mdi mdi-pill-multiple"></i> {{ number_format($medicineCount, 0, ",", ".") }} obat memakai hierarki klasifikasi ini.</span>
                <span><i class="mdi mdi-information-outline"></i> Pilih Reguler untuk mengecualikan klasifikasi dari SP khusus secara eksplisit.</span>
            </footer>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.suratPesanan.script")
@endpush
