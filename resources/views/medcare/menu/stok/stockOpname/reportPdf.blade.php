<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Lengkap Stock Opname {{ $opname->nomor }}</title>
    <style>
        @page { margin: 10mm 10mm 15mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #172033; font-family: "DejaVu Sans", sans-serif; font-size: 9pt; line-height: 1.38; }
        h1, h2, h3, p { margin: 0; }
        table { border-collapse: collapse; width: 100%; }
        .header { border-bottom: 2.5px solid #0f766e; margin-bottom: 7px; padding-bottom: 7px; }
        .header td { vertical-align: middle; }
        .brand-cell { width: 58%; white-space: nowrap; }
        .logo { display: inline-block; height: auto; max-height: 36pt; max-width: 104pt; width: auto; vertical-align: middle; }
        .logo-fallback { background: #0f766e; color: #fff; display: inline-block; font-size: 14pt; font-weight: 800; height: 34pt; line-height: 34pt; text-align: center; width: 34pt; }
        .brand-copy { display: inline-block; margin-left: 9pt; max-width: 325pt; vertical-align: middle; white-space: normal; }
        .brand-copy h2 { color: #0f766e; font-size: 13pt; line-height: 1.15; }
        .brand-copy p { color: #64748b; font-size: 7.5pt; margin-top: 2px; }
        .title-cell { text-align: right; }
        .eyebrow { color: #0f766e; font-size: 7pt; font-weight: 800; letter-spacing: .8px; }
        h1 { font-size: 16pt; line-height: 1.15; margin: 2px 0 4px; }
        .status { background: #e6fffb; border: 1px solid #99f6e4; color: #115e59; display: inline-block; font-size: 7pt; font-weight: 800; padding: 3px 7px; text-transform: uppercase; }
        .section { margin-top: 7px; }
        .keep-together { page-break-inside: avoid; }
        .section-title { border-left: 3px solid #0f766e; font-size: 10pt; font-weight: 800; margin-bottom: 4px; padding-left: 5px; }
        .meta { border: 1px solid #dbe3ea; }
        .meta td { border-right: 1px solid #e4eaf0; padding: 5px 7px; vertical-align: top; width: 25%; }
        .meta td:last-child { border-right: 0; }
        .label { color: #64748b; display: block; font-size: 7pt; font-weight: 700; letter-spacing: .35px; margin-bottom: 1px; text-transform: uppercase; }
        .value { color: #172033; font-size: 8.5pt; font-weight: 700; }
        .muted { color: #64748b; }
        .summary-grid { border-spacing: 4px 0; border-collapse: separate; margin-left: -4px; width: calc(100% + 8px); }
        .summary-grid td { background: #f8fafc; border: 1px solid #dbe3ea; padding: 6px 7px; vertical-align: top; width: 12.5%; }
        .summary-grid td.is-good { background: #f0fdf4; border-color: #bbf7d0; }
        .summary-grid td.is-bad { background: #fff1f2; border-color: #fecdd3; }
        .summary-grid td.is-warn { background: #fffbeb; border-color: #fde68a; }
        .metric { display: block; font-size: 13pt; font-weight: 800; line-height: 1.15; margin-top: 2px; }
        .metric.small { font-size: 10.5pt; }
        .negative { color: #be123c; }
        .positive { color: #047857; }
        .neutral { color: #334155; }
        .reconcile { background: #f0fdfa; border: 1px solid #99f6e4; margin-top: 5px; }
        .reconcile td { border-right: 1px solid #ccfbf1; padding: 5px 7px; text-align: center; width: 14.28%; }
        .reconcile td:last-child { border-right: 0; }
        .timeline { border: 1px solid #dbe3ea; }
        .timeline td { border-right: 1px solid #e4eaf0; padding: 5px 6px; vertical-align: top; width: 16.66%; }
        .timeline td:last-child { border-right: 0; }
        .timeline .step { color: #0f766e; font-size: 6.8pt; font-weight: 800; letter-spacing: .4px; }
        .timeline strong { display: block; font-size: 8pt; margin: 1px 0; }
        .notes td { border: 1px solid #dbe3ea; padding: 5px 7px; vertical-align: top; width: 33.33%; }
        .notes p { margin-top: 2px; min-height: 18px; white-space: pre-line; }
        .data-table { border: 1px solid #94a3b8; table-layout: fixed; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th { background: #0f766e; border: 1px solid #0b5f59; color: #fff; font-size: 7pt; font-weight: 800; line-height: 1.25; padding: 5px 3px; text-align: center; text-transform: uppercase; }
        .data-table td { border: 1px solid #cbd5e1; font-size: 7.6pt; padding: 5px 4px; vertical-align: top; word-wrap: break-word; }
        .data-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .data-table .no { text-align: center; width: 3%; }
        .data-table .item { width: 19%; }
        .data-table .batch { width: 9%; }
        .data-table .qty { text-align: right; width: 6.7%; }
        .data-table .movement { text-align: right; width: 7.5%; }
        .data-table .value-col { text-align: right; width: 10%; }
        .data-table .item strong { display: block; font-size: 8pt; }
        .sub { color: #64748b; display: block; font-size: 6.8pt; margin-top: 2px; }
        .reason-row td { background: #fffbeb !important; border-top: 0; color: #713f12; padding: 3px 6px; }
        .continued-title { margin-top: 9px; }
        .movement-table th:nth-child(1) { width: 12%; }
        .movement-table th:nth-child(2) { width: 23%; }
        .movement-table th:nth-child(3) { width: 12%; }
        .movement-table th:nth-child(4) { width: 13%; }
        .movement-table th:nth-child(5), .movement-table th:nth-child(6) { width: 8%; }
        .movement-table th:nth-child(7) { width: 14%; }
        .movement-table th:nth-child(8) { width: 10%; }
        .audit-table th:nth-child(1) { width: 14%; }
        .audit-table th:nth-child(2) { width: 18%; }
        .audit-table th:nth-child(3) { width: 19%; }
        .audit-table th:nth-child(4) { width: 13%; }
        .audit-table th:nth-child(5) { width: 36%; }
        .empty { border: 1px dashed #cbd5e1; color: #64748b; padding: 8px; text-align: center; }
        .signatures { margin-top: 8px; page-break-inside: avoid; }
        .signatures td { padding: 4px 12px 0; text-align: center; vertical-align: top; width: 25%; }
        .signature-space { height: 31px; }
        .signature-name { border-top: 1px solid #64748b; font-weight: 700; padding-top: 3px; }
        .signature-role { color: #64748b; font-size: 7pt; margin-top: 1px; }
        .document-control { background: #f8fafc; border: 1px solid #dbe3ea; font-size: 7.5pt; margin-top: 7px; padding: 5px 7px; }
        .document-control td { vertical-align: middle; }
        .document-control td:last-child { text-align: right; }
        .page-footer { bottom: -10mm; color: #64748b; font-size: 7pt; left: 0; position: fixed; }
        .page-number { bottom: -10mm; color: #64748b; font-size: 7pt; position: fixed; right: 0; }
        .page-number::after { content: "Halaman " counter(page); }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
@php
    $profile = $opname->branch?->apotekProfile;
    $pharmacyName = $profile?->name ?: ($opname->branch?->name ?: 'Medcare Pharmacy');
    $pharmacyAddress = $profile?->address ?: $opname->branch?->address;
    $pharmacyPhone = $profile?->phone ?: $opname->branch?->phone;
    $pharmacyEmail = $profile?->email ?: $opname->branch?->email;
    $qty = fn ($value) => $value === null ? '-' : number_format((float) $value, 2, ',', '.');
    $money = fn ($value) => 'Rp '.number_format(abs((float) $value), 0, ',', '.');
    $signedMoney = fn ($value) => ((float) $value < -0.005 ? '- ' : ((float) $value > 0.005 ? '+ ' : '')).$money($value);
    $date = fn ($value) => $value ? optional($value)->format('d/m/Y') : '-';
    $dateTime = fn ($value) => $value ? \Illuminate\Support\Carbon::parse($value)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-';
    $differenceClass = fn ($value) => (float) $value < -0.005 ? 'negative' : ((float) $value > 0.005 ? 'positive' : 'neutral');
    $location = $opname->rack ? $opname->rack->kode.' - '.$opname->rack->nama : 'Semua rak';
    $statusLabel = $statusLabels[$opname->status] ?? $opname->status;
@endphp

<div class="page-footer">Dokumen {{ $opname->nomor }} | Dicetak {{ $generatedAt->format('d/m/Y H:i') }} WIB</div>
<div class="page-number"></div>

<table class="header">
    <tr>
        <td class="brand-cell">
            @if ($logoDataUri)
                <img src="{{ $logoDataUri }}" class="logo" alt="Logo">
            @else
                <span class="logo-fallback">MC</span>
            @endif
            <div class="brand-copy">
                <h2>{{ $pharmacyName }}</h2>
                <p>{{ $pharmacyAddress ?: 'Alamat cabang belum dilengkapi' }}</p>
                <p>{{ collect([$pharmacyPhone, $pharmacyEmail])->filter()->implode(' | ') ?: 'Kontak cabang belum dilengkapi' }}</p>
            </div>
        </td>
        <td class="title-cell">
            <span class="eyebrow">INVENTORY CONTROL DOCUMENT</span>
            <h1>LAPORAN LENGKAP STOCK OPNAME</h1>
            <span class="status">{{ $statusLabel }}</span>
        </td>
    </tr>
</table>

<table class="meta">
    <tr>
        <td><span class="label">Nomor Dokumen</span><span class="value">{{ $opname->nomor }}</span></td>
        <td><span class="label">Tanggal Opname</span><span class="value">{{ $date($opname->tanggal_opname) }}</span></td>
        <td><span class="label">Cabang</span><span class="value">{{ $opname->branch?->code }} - {{ $opname->branch?->name }}</span></td>
        <td><span class="label">Area Penghitungan</span><span class="value">{{ $location }}</span></td>
    </tr>
</table>

<section class="section keep-together">
    <h3 class="section-title">Ringkasan Eksekutif</h3>
    <table class="summary-grid">
        <tr>
            <td><span class="label">Total Item / Batch</span><span class="metric neutral">{{ number_format((int) ($summary['total_items'] ?? 0), 0, ',', '.') }}</span></td>
            <td class="is-good"><span class="label">Sesuai</span><span class="metric positive">{{ number_format((int) ($summary['matching_items'] ?? 0), 0, ',', '.') }}</span></td>
            <td class="is-bad"><span class="label">Selisih Minus</span><span class="metric negative">{{ number_format((int) ($summary['minus_items'] ?? 0), 0, ',', '.') }}</span><span class="sub">{{ $qty($summary['minus_qty'] ?? 0) }} unit</span></td>
            <td class="is-warn"><span class="label">Selisih Plus</span><span class="metric positive">{{ number_format((int) ($summary['plus_items'] ?? 0), 0, ',', '.') }}</span><span class="sub">{{ $qty($summary['plus_qty'] ?? 0) }} unit</span></td>
            <td class="is-bad"><span class="label">Nilai Kehilangan</span><span class="metric small negative">{{ $money($summary['loss_value'] ?? 0) }}</span></td>
            <td class="is-good"><span class="label">Nilai Surplus</span><span class="metric small positive">{{ $money($summary['surplus_value'] ?? 0) }}</span></td>
            <td colspan="2"><span class="label">Nilai Selisih Bersih</span><span class="metric small {{ $differenceClass($summary['net_value'] ?? 0) }}">{{ $signedMoney($summary['net_value'] ?? 0) }}</span><span class="sub">Minus berarti potensi kehilangan persediaan</span></td>
        </tr>
    </table>
    <table class="reconcile">
        <tr>
            <td><span class="label">Stok Sistem Saat Hitung</span><strong>{{ $qty($summary['system_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Stok Fisik</span><strong>{{ $qty($summary['physical_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Mutasi Masuk</span><strong class="positive">{{ $qty($summary['movement_in_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Mutasi Keluar</span><strong class="negative">{{ $qty($summary['movement_out_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Stok Sistem Terkini</span><strong>{{ $qty($summary['current_system_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Stok Target</span><strong>{{ $qty($summary['target_qty'] ?? 0) }}</strong></td>
            <td><span class="label">Total Baris Disesuaikan</span><strong>{{ number_format((int) ($summary['adjusted_rows'] ?? 0), 0, ',', '.') }}</strong></td>
        </tr>
    </table>
</section>

<section class="section keep-together">
    <h3 class="section-title">Kronologi dan Penanggung Jawab</h3>
    <table class="timeline">
        <tr>
            <td><span class="step">01 DIBUAT</span><strong>{{ $opname->creator?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->created_at) }}</span></td>
            <td><span class="step">02 DIMULAI</span><strong>{{ $opname->starter?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->started_at) }}</span></td>
            <td><span class="step">03 DISUBMIT</span><strong>{{ $opname->submitter?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->submitted_at) }}</span></td>
            <td><span class="step">04 DIVALIDASI</span><strong>{{ $opname->verifier?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->verified_at) }}</span></td>
            <td><span class="step">05 DISETUJUI</span><strong>{{ $opname->approver?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->approved_at) }}</span></td>
            <td><span class="step">06 DIPOSTING</span><strong>{{ $opname->adjuster?->name ?: '-' }}</strong><span class="muted">{{ $dateTime($opname->adjusted_at) }}</span></td>
        </tr>
    </table>
</section>

<section class="section keep-together">
    <table class="notes">
        <tr>
            <td><span class="label">Catatan Penghitungan</span><p>{{ $opname->catatan ?: 'Tidak ada catatan.' }}</p></td>
            <td><span class="label">Catatan Validasi</span><p>{{ $opname->verification_note ?: 'Belum ada catatan validasi.' }}</p></td>
            <td><span class="label">Catatan Persetujuan</span><p>{{ $opname->approval_note ?: 'Belum ada catatan persetujuan.' }}</p></td>
        </tr>
    </table>
</section>

<section class="section page-break">
    <h3 class="section-title">Rincian Hasil dan Rekonsiliasi</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="item">Obat / Rak / Petugas</th>
                <th class="batch">Batch / ED</th>
                <th class="qty">Sistem Saat Hitung</th>
                <th class="qty">Fisik</th>
                <th class="qty">Selisih Awal</th>
                <th class="movement">Mutasi<br>Masuk / Keluar</th>
                <th class="qty">Sistem Terkini</th>
                <th class="qty">Target</th>
                <th class="qty">Selisih Final</th>
                <th class="value-col">Nilai Selisih</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($opname->details as $detail)
                @php
                    $initialDifference = (float) ($detail->selisih ?? 0);
                    $finalDifference = (float) ($detail->selisih_validasi ?? $detail->selisih ?? 0);
                    $differenceValue = $finalDifference * (float) $detail->hpp;
                @endphp
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td class="item">
                        <strong>{{ $detail->nama_obat }}</strong>
                        <span class="sub">{{ $detail->kode_obat ?: '-' }} | {{ $detail->satuan ?: '-' }} | {{ $detail->rack ? $detail->rack->kode.' - '.$detail->rack->nama : 'Tanpa rak' }}</span>
                        <span class="sub">Dihitung: {{ $detail->counter?->name ?: '-' }} | {{ $dateTime($detail->counted_at) }}</span>
                    </td>
                    <td class="batch"><strong>{{ $detail->no_batch ?: '-' }}</strong><span class="sub">ED {{ $date($detail->expired_date) }}</span></td>
                    <td class="qty">{{ $qty($detail->stok_sistem_hitung) }}</td>
                    <td class="qty"><strong>{{ $qty($detail->stok_fisik) }}</strong></td>
                    <td class="qty {{ $differenceClass($initialDifference) }}">{{ $qty($initialDifference) }}</td>
                    <td class="movement"><span class="positive">+{{ $qty($detail->mutasi_masuk) }}</span><br><span class="negative">-{{ $qty($detail->mutasi_keluar) }}</span></td>
                    <td class="qty">{{ $qty($detail->stok_sistem_validasi) }}</td>
                    <td class="qty">{{ $qty($detail->stok_target_validasi) }}</td>
                    <td class="qty {{ $differenceClass($finalDifference) }}"><strong>{{ $qty($finalDifference) }}</strong></td>
                    <td class="value-col {{ $differenceClass($differenceValue) }}"><strong>{{ $signedMoney($differenceValue) }}</strong><span class="sub">HPP {{ $money($detail->hpp) }}</span></td>
                </tr>
                @if (abs($initialDifference) >= 0.005 || abs($finalDifference) >= 0.005 || $detail->alasan_selisih)
                    <tr class="reason-row"><td></td><td colspan="10"><strong>Alasan selisih:</strong> {{ $detail->alasan_selisih ?: 'Belum dicatat.' }}</td></tr>
                @endif
            @endforeach
        </tbody>
    </table>
</section>

<section class="section continued-title">
    <h3 class="section-title">Mutasi Stok Setelah Submit ({{ $opname->movements->count() }})</h3>
    @if ($opname->movements->isEmpty())
        <div class="empty">Tidak ada mutasi stok yang terekam setelah blind count disubmit.</div>
    @else
        <table class="data-table movement-table">
            <thead><tr><th>Waktu</th><th>Obat</th><th>Batch / ED</th><th>Jenis Mutasi</th><th>Masuk</th><th>Keluar</th><th>Referensi</th><th>User</th></tr></thead>
            <tbody>
                @foreach ($opname->movements as $movement)
                    @php
                        $movementName = $movement->detail?->nama_obat ?: ($movement->kartuStok?->obat?->nama_obat ?: 'Obat tidak ditemukan');
                        $movementCode = $movement->detail?->kode_obat ?: $movement->kartuStok?->obat?->kode_obat;
                        $movementBatch = $movement->detail?->no_batch ?: $movement->kartuStok?->no_batch;
                        $movementExpiry = $movement->detail?->expired_date ?? $movement->kartuStok?->expired_date;
                    @endphp
                    <tr>
                        <td>{{ $dateTime($movement->occurred_at) }}</td>
                        <td><strong>{{ $movementName }}</strong><span class="sub">{{ $movementCode ?: '-' }}</span></td>
                        <td>{{ $movementBatch ?: '-' }}<span class="sub">ED {{ $date($movementExpiry) }}</span></td>
                        <td>{{ ucwords(str_replace('_', ' ', $movement->jenis_mutasi)) }}</td>
                        <td class="qty positive">{{ $qty($movement->qty_masuk) }}</td>
                        <td class="qty negative">{{ $qty($movement->qty_keluar) }}</td>
                        <td>{{ $movement->kartuStok?->nomor_referensi ?: '-' }}</td>
                        <td>{{ $movement->kartuStok?->createdBy?->name ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section class="section continued-title">
    <h3 class="section-title">Jejak Audit ({{ $opname->logs->count() }})</h3>
    @if ($opname->logs->isEmpty())
        <div class="empty">Belum ada aktivitas audit yang tercatat.</div>
    @else
        <table class="data-table audit-table">
            <thead><tr><th>Waktu</th><th>Aktivitas</th><th>Perubahan Status</th><th>Pelaksana</th><th>Catatan</th></tr></thead>
            <tbody>
                @foreach ($opname->logs as $log)
                    <tr>
                        <td>{{ $dateTime($log->performed_at) }}</td>
                        <td><strong>{{ $actionLabels[$log->action] ?? ucwords(str_replace('_', ' ', $log->action)) }}</strong></td>
                        <td>{{ $statusLabels[$log->from_status] ?? ($log->from_status ?: '-') }}<br><span class="sub">ke {{ $statusLabels[$log->to_status] ?? ($log->to_status ?: '-') }}</span></td>
                        <td>{{ $log->performer?->name ?: 'Sistem' }}</td>
                        <td>{{ $log->note ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<table class="signatures">
    <tr>
        <td><strong>Petugas Hitung / Submit</strong><div class="signature-space"></div><div class="signature-name">{{ $opname->submitter?->name ?: $opname->starter?->name ?: '-' }}</div><div class="signature-role">{{ $dateTime($opname->submitted_at) }}</div></td>
        <td><strong>Validator</strong><div class="signature-space"></div><div class="signature-name">{{ $opname->verifier?->name ?: '-' }}</div><div class="signature-role">{{ $dateTime($opname->verified_at) }}</div></td>
        <td><strong>Penyetuju</strong><div class="signature-space"></div><div class="signature-name">{{ $opname->approver?->name ?: '-' }}</div><div class="signature-role">{{ $dateTime($opname->approved_at) }}</div></td>
        <td><strong>Petugas Posting</strong><div class="signature-space"></div><div class="signature-name">{{ $opname->adjuster?->name ?: '-' }}</div><div class="signature-role">{{ $dateTime($opname->adjusted_at) }}</div></td>
    </tr>
</table>

<table class="document-control">
    <tr>
        <td><strong>Kontrol dokumen:</strong> {{ $documentFingerprint }}<br><span class="muted">Laporan dibuat otomatis dari transaksi dan audit trail Medcare. Verifikasi terhadap dokumen sumber tetap diperlukan untuk keperluan audit formal.</span></td>
        <td><span class="label">Waktu Pembuatan</span><strong>{{ $generatedAt->format('d/m/Y H:i:s') }} WIB</strong></td>
    </tr>
</table>
</body>
</html>
