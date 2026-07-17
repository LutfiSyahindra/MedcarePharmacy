@extends("template.partials.app")

@push("style")
    @include("template.AddOn.dataTables")
    @include("template.AddOn.mdiicon")
    @include("template.AddOn.sweetAlert")
    @include("template.AddOn.select2")
    @include("medcare.settings.margin.partials.style")
@endpush

@section("content")
    @php
        $marginPriority = $marginPriority ?? ["sub_golongan", "main_golongan", "golongan"];
        $marginPriorityOptions = $marginPriorityOptions ?? [
            "sub_golongan" => "Sub Golongan",
            "main_golongan" => "Main Golongan",
            "golongan" => "Golongan",
        ];
    @endphp
    <div class="margin-page">
        @include("medcare.settings.margin.modalMain")
        @include("medcare.settings.margin.partials.header")

        <section class="margin-priority-section">
            <div class="margin-priority-header">
                <div class="margin-table-title">
                    <span class="margin-table-title-icon"><i class="mdi mdi-sort-variant"></i></span>
                    <div>
                        <h5>Prioritas Margin Posting Penerimaan</h5>
                        <p>Urutan ini menentukan margin aktif yang dipakai saat harga jual batch dihitung.</p>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-secondary" id="resetMarginPriority" title="Kembalikan default">
                    <i class="mdi mdi-restore"></i>
                </button>
            </div>
            <form id="marginPriorityForm" class="margin-priority-form">
                @csrf
                <div class="margin-priority-grid">
                    @foreach ([0, 1, 2] as $index)
                        <div class="margin-priority-item">
                            <span class="margin-priority-number">{{ $index + 1 }}</span>
                            <div class="margin-priority-body">
                                <label class="form-label" for="marginPriority{{ $index }}">Prioritas {{ $index + 1 }}</label>
                                <select id="marginPriority{{ $index }}" name="priority[]" class="form-select margin-priority-select">
                                    @foreach ($marginPriorityOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(($marginPriority[$index] ?? null) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="margin-priority-controls">
                                <button type="button" class="btn margin-priority-move" data-direction="up" title="Naikkan prioritas">
                                    <i class="mdi mdi-chevron-up"></i>
                                </button>
                                <button type="button" class="btn margin-priority-move" data-direction="down" title="Turunkan prioritas">
                                    <i class="mdi mdi-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="margin-priority-footer">
                    <span id="marginPrioritySummary"></span>
                    <button type="submit" class="btn btn-primary" id="saveMarginPriority">
                        <i class="mdi mdi-content-save-outline"></i>
                        Simpan Prioritas
                    </button>
                </div>
            </form>
        </section>

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
