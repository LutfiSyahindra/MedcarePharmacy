@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.settings.margin.partials.style")
@endpush

@section("content")
    <div class="margin-page">
        @include("medcare.settings.margin.modalMain")
        @include("medcare.settings.margin.partials.header")

        <div class="margin-stats-grid">
            <div class="margin-stat">
                <span class="margin-stat-icon"><i class="mdi mdi-percent-outline"></i></span>
                <div>
                    <strong id="marginTotalCount">0</strong>
                    <span>Total Margin</span>
                    <small>Semua aturan margin yang terdaftar.</small>
                </div>
            </div>
            <div class="margin-stat">
                <span class="margin-stat-icon"><i class="mdi mdi-filter-check-outline"></i></span>
                <div>
                    <strong id="marginFilteredCount">0</strong>
                    <span>Hasil Filter</span>
                    <small>Mengikuti pencarian aktif.</small>
                </div>
            </div>
            <div class="margin-stat">
                <span class="margin-stat-icon"><i class="mdi mdi-cursor-default-click-outline"></i></span>
                <div>
                    <strong id="marginSelectedCount">0</strong>
                    <span>Dipilih</span>
                    <small>Klik baris untuk menandai data.</small>
                </div>
            </div>
        </div>

        <section class="margin-table-section">
            <div class="margin-table-toolbar">
                <div class="margin-table-title">
                    <span class="margin-table-title-icon"><i class="mdi mdi-chart-line"></i></span>
                    <div>
                        <h5>Daftar Margin</h5>
                        <p>Atur faktor jual, persentase, tingkat, dan status margin.</p>
                    </div>
                </div>
                <div class="margin-table-tools">
                    <label class="margin-search" for="marginSearch">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" id="marginSearch" placeholder="Cari reference, tingkat, atau faktor">
                    </label>
                    <button type="button" class="btn btn-outline-primary margin-refresh-table" title="Refresh tabel">
                        <i class="mdi mdi-refresh"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableMargin" class="table margin-table align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Reference</th>
                            <th>Faktor Jual</th>
                            <th>Persentase</th>
                            <th>Tingkat</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push("scripts")
    @include("medcare.settings.margin.partials.scripts")
    @include("medcare.settings.margin.jsMain")
@endpush
