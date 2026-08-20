<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template {{ $meta["label"] }} - {{ $branch->apotekProfile?->name ?: $branch->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #111;
            background: #e9edf2;
            font-family: "Times New Roman", Times, serif;
            font-size: 11.5pt;
            line-height: 1.25;
        }
        .template-toolbar {
            position: sticky;
            z-index: 10;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 11px 18px;
            color: #f8fafc;
            background: #26364b;
            font-family: Arial, sans-serif;
            box-shadow: 0 3px 14px rgba(15, 23, 42, .22);
        }
        .template-toolbar span { font-size: 12px; }
        .template-toolbar strong { color: #fff; }
        .template-toolbar .profile-warning { color: #fde68a; font-weight: 700; }
        .template-toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            color: #fff;
            border: 0;
            border-radius: 7px;
            background: {{ $type === "narkotika" ? "#c62828" : ($type === "psikotropika" ? "#4054b2" : "#b7791f") }};
            font-weight: 700;
            cursor: pointer;
        }
        .template-toolbar button.secondary { color: #e6ebf3; border: 1px solid #65758c; background: transparent; }
        .order-sheet {
            position: relative;
            width: 190mm;
            min-height: 277mm;
            margin: 12mm auto;
            padding: 8mm 9mm;
            border: .35mm solid #111;
            background: #fff;
            box-shadow: 0 4px 22px rgba(15, 23, 42, .14);
        }
        .screen-template-label {
            position: absolute;
            top: 4mm;
            left: 7mm;
            padding: 1.2mm 2.4mm;
            color: #55657a;
            border: .2mm solid #cfd6df;
            border-radius: 2mm;
            background: #f7f9fb;
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .form-label-top { position: absolute; top: 4mm; right: 7mm; font-size: 9.5pt; }
        .document-header { margin-top: 3mm; text-align: center; }
        .document-header h1 { margin: 0 0 1mm; font-size: {{ $type === "prekursor" ? "12pt" : "14pt" }}; text-decoration: underline; }
        .document-number { display: flex; align-items: end; justify-content: center; gap: 2mm; margin: 0; font-size: 11pt; }
        .fill-line { display: inline-block; min-width: 65mm; min-height: 5mm; border-bottom: .25mm dotted #333; }
        .fill-line.is-wide { min-width: 105mm; }
        .section { margin-top: {{ $type === "narkotika" ? "8mm" : "6mm" }}; }
        .section.compact { margin-top: {{ $type === "narkotika" ? "5mm" : "3.5mm" }}; }
        .section > p { margin: 0 0 1.5mm; }
        .field-table { width: 100%; border-collapse: collapse; }
        .field-table td { height: 6mm; padding: .55mm 0; vertical-align: top; }
        .field-label { width: 38mm; white-space: nowrap; }
        .field-separator { width: 5mm; text-align: center; }
        .field-value-line { display: block; width: 100%; min-height: 5mm; border-bottom: .25mm dotted #555; }
        .medicine-table {
            width: 100%;
            margin: 2.5mm 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: {{ $type === "prekursor" ? "9.5pt" : "10.5pt" }};
            line-height: 1.3;
        }
        .medicine-table th,
        .medicine-table td {
            padding: 2mm 2.2mm;
            border: .3mm solid #4b5563;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        .medicine-table th {
            padding-top: 1.7mm;
            padding-bottom: 1.7mm;
            background: #e9edf2;
            font-size: {{ $type === "prekursor" ? "9.25pt" : "10pt" }};
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .medicine-table .number-column { width: 7%; text-align: center; }
        .medicine-table .name-column { width: {{ $type === "prekursor" ? "20%" : "25%" }}; }
        .medicine-table .preparation-column { width: {{ $type === "prekursor" ? "13%" : "16%" }}; }
        .medicine-table .strength-column { width: {{ $type === "prekursor" ? "22%" : "27%" }}; }
        .medicine-table .packaging-column { width: 14%; }
        .medicine-table .quantity-column { width: {{ $type === "prekursor" ? "24%" : "25%" }}; }
        .medicine-table .empty-row td { height: 10mm; }
        .medicine-table .empty-row.is-single-item td { height: 27mm; }
        .signature-wrap { display: flex; justify-content: flex-end; margin-top: {{ $type === "narkotika" ? "7mm" : "5mm" }}; }
        .signature { width: {{ $type === "prekursor" ? "82mm" : "75mm" }}; text-align: left; }
        .signature p { margin: 0 0 3mm; }
        .signature-date { display: flex; align-items: end; gap: 1mm; }
        .signature-date .fill-line { min-width: 36mm; }
        .signature-space { height: {{ $type === "narkotika" ? "22mm" : "16mm" }}; }
        .signature-name { display: inline-block; min-width: 58mm; font-weight: 700; text-decoration: underline; }
        .signature-role { margin: .8mm 0; }
        .notes { margin-top: {{ $type === "narkotika" ? "8mm" : "5mm" }}; font-size: 10pt; }
        .notes p { margin: 0 0 1.3mm; }
        .notes ul { margin: 0; padding-left: 7mm; }
        .copy-mark { position: absolute; right: 8mm; bottom: 5mm; color: #555; font-family: Arial, sans-serif; font-size: 8pt; }
        @media print {
            body { background: #fff; }
            .template-toolbar,
            .screen-template-label { display: none !important; }
            .order-sheet { width: 190mm; min-height: 277mm; margin: 0; box-shadow: none; }
            .medicine-table tr { page-break-inside: avoid; break-inside: avoid; }
        }
        @media screen and (max-width: 800px) {
            .order-sheet { transform-origin: top left; }
        }
    </style>
</head>
<body>
    @php
        $profile = $branch->apotekProfile;
        $facilityName = $profile?->name ?: ($branch->name ?: "-");
        $facilityAddress = $profile?->address ?: ($branch->address ?: "-");
        $pharmacistName = $profile?->pharmacist_name ?: "................................";
        $pharmacistLicense = $profile?->pharmacist_license_number ?: "................................";
        $profileIncomplete = ! $profile?->pharmacist_name || ! $profile?->pharmacist_license_number;
        $city = $profile?->city ?: "................................";
        $formLabel = match ($type) {
            "narkotika" => "Formulir 1",
            "psikotropika" => "Formulir 2",
            default => "Formulir 3",
        };
        $documentTitle = match ($type) {
            "narkotika" => "SURAT PESANAN NARKOTIKA",
            "psikotropika" => "SURAT PESANAN PSIKOTROPIKA",
            default => "SURAT PESANAN OBAT/BAHAN OBAT/PREKURSOR FARMASI*",
        };
        $orderSubject = match ($type) {
            "narkotika" => "Narkotika",
            "psikotropika" => "Psikotropika",
            default => "Obat/Bahan Obat/Prekursor Farmasi*",
        };
    @endphp

    <div class="template-toolbar">
        <span><strong>Template kosong</strong> &middot; {{ $meta["label"] }} &middot; {{ $facilityName }}</span>
        @if ($profileIncomplete)
            <span class="profile-warning">Nama Apoteker atau SIPA belum lengkap di Profil Apotek.</span>
        @endif
        <button type="button" class="secondary" onclick="window.close()">Tutup</button>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <main class="order-sheet">
        <span class="screen-template-label">Template kosong</span>
        <span class="form-label-top">{{ $formLabel }}</span>

        <header class="document-header">
            <h1>{{ $documentTitle }}</h1>
            <p class="document-number">Nomor : <span class="fill-line"></span></p>
        </header>

        <section class="section">
            <p>Yang bertanda tangan di bawah ini :</p>
            <table class="field-table">
                <tr><td class="field-label">Nama</td><td class="field-separator">:</td><td>{{ $pharmacistName }}</td></tr>
                <tr><td class="field-label">Jabatan</td><td class="field-separator">:</td><td>Apoteker Penanggung Jawab</td></tr>
            </table>
        </section>

        <section class="section compact">
            <p>Mengajukan pesanan {{ $orderSubject }} kepada :</p>
            <table class="field-table">
                <tr><td class="field-label">Nama Distributor</td><td class="field-separator">:</td><td><span class="field-value-line"></span></td></tr>
                <tr><td class="field-label">Alamat</td><td class="field-separator">:</td><td><span class="field-value-line"></span></td></tr>
                <tr><td class="field-label">Telp</td><td class="field-separator">:</td><td><span class="field-value-line"></span></td></tr>
            </table>
        </section>

        <section class="section compact">
            <p>dengan {{ $orderSubject }} yang dipesan adalah :</p>
            <p>
                {{ $type === "prekursor"
                    ? "(Sebutkan nama obat, bentuk sediaan, kekuatan/potensi, jumlah dalam bentuk angka dan huruf, isi kemasan)"
                    : "(Sebutkan nama obat, bentuk sediaan, kekuatan/potensi, jumlah dalam bentuk angka dan huruf)" }}
            </p>
            <table class="medicine-table">
                <thead>
                    <tr>
                        <th class="number-column" scope="col">No.</th>
                        <th class="name-column" scope="col">Nama obat</th>
                        <th class="preparation-column" scope="col">Bentuk sediaan</th>
                        <th class="strength-column" scope="col">Kekuatan/potensi</th>
                        @if ($type === "prekursor")
                            <th class="packaging-column" scope="col">Isi kemasan</th>
                        @endif
                        <th class="quantity-column" scope="col">Jumlah<br>(angka dan huruf)</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($row = 1; $row <= ($type === "narkotika" ? 1 : 5); $row++)
                        <tr class="empty-row{{ $type === "narkotika" ? " is-single-item" : "" }}">
                            <td class="number-column">{{ $row }}</td>
                            <td class="name-column"></td>
                            <td class="preparation-column"></td>
                            <td class="strength-column"></td>
                            @if ($type === "prekursor")
                                <td class="packaging-column"></td>
                            @endif
                            <td class="quantity-column"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </section>

        <section class="section compact">
            <p>{{ $type === "prekursor" ? "Obat/Bahan Obat/Prekursor Farmasi tersebut" : $orderSubject }} akan dipergunakan untuk :</p>
            <table class="field-table">
                <tr><td class="field-label">Nama Sarana</td><td class="field-separator">:</td><td>{{ $facilityName }} (Apotek)</td></tr>
                <tr><td class="field-label">Alamat Sarana</td><td class="field-separator">:</td><td>{{ $facilityAddress }}</td></tr>
            </table>
        </section>

        <div class="signature-wrap">
            <div class="signature">
                <p class="signature-date">{{ $city }}, <span class="fill-line"></span></p>
                <p>Pesanan</p>
                <p>Tanda tangan dan stempel</p>
                <div class="signature-space"></div>
                <div><span class="signature-name">{{ $pharmacistName }}</span></div>
                @if ($type === "prekursor")
                    <div class="signature-role">Apoteker/Tenaga Teknis Kefarmasian</div>
                    <div>No. SIPA/SIKTTK : {{ $pharmacistLicense }}</div>
                @else
                    <div>No. SIPA : {{ $pharmacistLicense }}</div>
                @endif
            </div>
        </div>

        <section class="notes">
            <p>*) &nbsp;coret yang tidak perlu</p>
            <p>Catatan:</p>
            @if ($type === "narkotika")
                <ul>
                    <li>Satu surat pesanan hanya berlaku untuk satu jenis Narkotika.</li>
                    <li>Surat Pesanan dibuat sekurang-kurangnya 3 (tiga) rangkap.</li>
                </ul>
            @else
                <p>Surat Pesanan dibuat sekurang-kurangnya 3 (tiga) rangkap.</p>
            @endif
        </section>

        <span class="copy-mark">Template kosong &middot; Cetak sesuai kebutuhan</span>
    </main>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', function() {
                window.focus();
                window.setTimeout(function() { window.print(); }, 100);
            });
        </script>
    @endif
</body>
</html>
