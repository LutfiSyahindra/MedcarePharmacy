@extends("template.partials.app")

@push("style")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("medcare.settings.purchaseOrder.style")
@endpush

@section("content")
    <div class="po-setting-page">
        <nav class="page-breadcrumb" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Settings</a></li>
                <li class="breadcrumb-item active" aria-current="page">Setting PO</li>
            </ol>
        </nav>

        <section class="po-setting-hero">
            <div class="po-setting-hero-copy">
                <span class="po-setting-kicker"><i class="mdi mdi-file-document-edit-outline"></i> Penomoran Purchase Order</span>
                <h4>Setting PO</h4>
                <p>Pilih distributor yang nomor PO-nya harus diketik sendiri. Distributor lain tetap memakai nomor otomatis dari sistem.</p>
                <div class="po-setting-note">
                    <i class="mdi mdi-information-outline"></i>
                    <span>Pengaturan ini berlaku saat membuat PO baru. Nomor pada PO yang sudah tersimpan tidak akan berubah.</span>
                </div>
            </div>
            <div class="po-setting-hero-actions">
                <button type="button" class="btn po-setting-secondary" id="poSettingReset" disabled>
                    <i class="mdi mdi-undo-variant"></i> Batalkan
                </button>
                <button type="submit" form="poSettingForm" class="btn po-setting-save" id="poSettingSave" disabled>
                    <i class="mdi mdi-content-save-outline"></i>
                    <span>Simpan Setting</span>
                    <b id="poSettingDirtyCount" class="d-none">0</b>
                </button>
            </div>
        </section>

        <section class="po-setting-summary" aria-label="Ringkasan setting nomor PO">
            <button type="button" class="po-setting-summary-card is-all is-active" data-filter="all">
                <span><i class="mdi mdi-truck-delivery-outline"></i></span>
                <div><strong>{{ number_format($distributors->count(), 0, ",", ".") }}</strong><small>Semua distributor</small></div>
            </button>
            <button type="button" class="po-setting-summary-card is-automatic" data-filter="automatic">
                <span><i class="mdi mdi-auto-fix"></i></span>
                <div><strong id="poSettingAutomaticCount">{{ number_format($automaticCount, 0, ",", ".") }}</strong><small>Nomor otomatis</small></div>
            </button>
            <button type="button" class="po-setting-summary-card is-manual" data-filter="manual">
                <span><i class="mdi mdi-form-textbox"></i></span>
                <div><strong id="poSettingManualCount">{{ number_format($manualCount, 0, ",", ".") }}</strong><small>Ketik manual</small></div>
            </button>
            <button type="button" class="po-setting-summary-card is-active-distributor" data-filter="active">
                <span><i class="mdi mdi-check-decagram-outline"></i></span>
                <div><strong>{{ number_format($activeCount, 0, ",", ".") }}</strong><small>Distributor aktif</small></div>
            </button>
        </section>

        <section class="po-setting-workspace">
            <div class="po-setting-toolbar">
                <label class="po-setting-search" for="poSettingSearch">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" id="poSettingSearch" placeholder="Cari kode atau nama distributor..." autocomplete="off">
                    <button type="button" id="poSettingClearSearch" aria-label="Hapus pencarian"><i class="mdi mdi-close"></i></button>
                </label>
                <div class="po-setting-visible"><strong id="poSettingVisibleCount">{{ $distributors->count() }}</strong><span>ditampilkan</span></div>
                <div class="po-setting-bulk-actions">
                    <button type="button" class="btn" id="poSettingVisibleAutomatic"><i class="mdi mdi-auto-fix"></i> Tampil → Otomatis</button>
                    <button type="button" class="btn" id="poSettingVisibleManual"><i class="mdi mdi-form-textbox"></i> Tampil → Manual</button>
                </div>
            </div>

            <form id="poSettingForm">
                @csrf
                @method("PUT")
                <div class="po-setting-list">
                    @forelse ($distributors as $distributor)
                        <article class="po-setting-row"
                            data-id="{{ $distributor->id }}"
                            data-active="{{ $distributor->is_active ? "1" : "0" }}"
                            data-search="{{ Illuminate\Support\Str::lower($distributor->kode." ".$distributor->nama." ".$distributor->alamat) }}">
                            <div class="po-setting-identity">
                                <span class="po-setting-avatar">{{ Illuminate\Support\Str::upper(Illuminate\Support\Str::substr($distributor->kode, 0, 2)) }}</span>
                                <div>
                                    <strong>{{ $distributor->nama }}</strong>
                                    <small><code>{{ $distributor->kode }}</code><span>{{ $distributor->alamat ?: "Alamat belum diisi" }}</span></small>
                                </div>
                            </div>
                            <div class="po-setting-status-wrap">
                                <span class="po-setting-distributor-status {{ $distributor->is_active ? "is-active" : "is-inactive" }}">
                                    <i class="mdi {{ $distributor->is_active ? "mdi-check-circle-outline" : "mdi-pause-circle-outline" }}"></i>
                                    {{ $distributor->is_active ? "Aktif" : "Nonaktif" }}
                                </span>
                                <span class="po-setting-mode-badge" data-mode-badge>
                                    <i class="mdi"></i><span></span>
                                </span>
                            </div>
                            <label class="po-setting-switch">
                                <input type="checkbox" class="po-setting-toggle" value="{{ $distributor->id }}"
                                    data-initial="{{ $distributor->uses_manual_po_number ? "1" : "0" }}"
                                    @checked($distributor->uses_manual_po_number)>
                                <span class="po-setting-switch-track"><span></span></span>
                                <span class="po-setting-switch-copy">
                                    <strong>Ketik nomor PO manual</strong>
                                    <small>Aktifkan bila distributor menentukan nomor PO sendiri.</small>
                                </span>
                            </label>
                        </article>
                    @empty
                        <div class="po-setting-empty">
                            <i class="mdi mdi-truck-remove-outline"></i>
                            <strong>Belum ada distributor</strong>
                            <span>Tambahkan distributor melalui menu Master Data terlebih dahulu.</span>
                        </div>
                    @endforelse
                </div>
            </form>

            <div class="po-setting-no-result d-none" id="poSettingNoResult">
                <i class="mdi mdi-magnify-close"></i>
                <strong>Distributor tidak ditemukan</strong>
                <span>Coba kata pencarian atau filter yang lain.</span>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.purchaseOrder.script")
@endpush
