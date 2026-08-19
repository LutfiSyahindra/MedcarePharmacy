<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pesanan Psikotropika - {{ $purchaseOrder->no_po }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #111;
            background: #e9edf2;
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.25;
        }
        .print-toolbar {
            position: sticky;
            z-index: 10;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 12px 18px;
            color: #f8fafc;
            background: #26364b;
            font-family: Arial, sans-serif;
            box-shadow: 0 3px 14px rgba(15, 23, 42, .22);
        }
        .print-toolbar span { font-size: 13px; }
        .print-toolbar .profile-warning { color: #fde68a; font-weight: 700; }
        .print-toolbar button {
            padding: 9px 16px;
            color: #fff;
            border: 0;
            border-radius: 7px;
            background: #4054b2;
            font-weight: 700;
            cursor: pointer;
        }
        .psychotropic-order-sheet {
            position: relative;
            width: 190mm;
            min-height: 277mm;
            margin: 12mm auto;
            padding: 8mm 9mm;
            border: .35mm solid #111;
            background: #fff;
            box-shadow: 0 4px 22px rgba(15, 23, 42, .14);
            page-break-after: always;
            break-after: page;
        }
        .psychotropic-order-sheet:last-child { page-break-after: auto; break-after: auto; }
        .form-label-top { position: absolute; top: 4mm; right: 7mm; font-size: 9.5pt; }
        .document-header { margin-top: 2mm; text-align: center; }
        .document-header h1 {
            margin: 0 0 1mm;
            font-size: 14pt;
            text-decoration: underline;
        }
        .document-number { margin: 0; font-size: 11pt; }
        .section { margin-top: 6mm; }
        .section.compact { margin-top: 3.5mm; }
        .section > p { margin: 0 0 1.5mm; }
        .field-table { width: 100%; border-collapse: collapse; }
        .field-table td { padding: .55mm 0; vertical-align: top; }
        .field-label { width: 38mm; white-space: nowrap; }
        .field-separator { width: 5mm; text-align: center; }
        .medicine-table {
            width: calc(100% - 43mm);
            margin: 2mm 0 0 43mm;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .medicine-table th,
        .medicine-table td {
            padding: 1.1mm 2mm;
            border: .25mm solid #777;
            vertical-align: top;
        }
        .medicine-table th { background: #f4f4f4; text-align: left; }
        .medicine-table .number-column { width: 9mm; text-align: center; }
        .medicine-table .quantity-column { width: 54mm; }
        .medicine-name { display: block; font-weight: 700; }
        .medicine-meta { display: block; margin-top: .7mm; }
        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 5mm;
        }
        .signature { width: 75mm; text-align: left; }
        .signature p { margin: 0 0 3.5mm; }
        .signature-space { height: 16mm; }
        .signature-name { display: inline-block; min-width: 58mm; font-weight: 700; text-decoration: underline; }
        .notes { margin-top: 5mm; font-size: 10.5pt; }
        .notes p { margin: 0 0 1.5mm; }
        .copy-mark {
            position: absolute;
            right: 8mm;
            bottom: 5mm;
            color: #444;
            font-family: Arial, sans-serif;
            font-size: 8pt;
        }
        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .psychotropic-order-sheet {
                width: 190mm;
                min-height: 277mm;
                margin: 0;
                box-shadow: none;
            }
            .medicine-table tr { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body>
    @php
        $profile = $purchaseOrder->branch?->apotekProfile;
        $facilityName = $profile?->name ?: ($purchaseOrder->branch?->name ?: '-');
        $facilityAddress = $profile?->address ?: ($purchaseOrder->branch?->address ?: '-');
        $pharmacistName = $profile?->pharmacist_name ?: '................................';
        $pharmacistLicense = $profile?->pharmacist_license_number ?: '................................';
        $profileIncomplete = ! $profile?->pharmacist_name || ! $profile?->pharmacist_license_number;
        $city = $profile?->city ?: '-';
        $date = \Illuminate\Support\Carbon::parse($purchaseOrder->tanggal_po);
        $months = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $formattedDate = $date->day.' '.$months[$date->month].' '.$date->year;
    @endphp

    <div class="print-toolbar">
        <span>{{ $psychotropicDetails->count() }} item Psikotropika &middot; {{ $copyCount }} rangkap &middot; {{ $copyCount }} halaman</span>
        @if ($profileIncomplete)
            <span class="profile-warning">Nama Apoteker atau SIPA belum lengkap di Profile Apotek.</span>
        @endif
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    @for ($copy = 1; $copy <= $copyCount; $copy++)
        <main class="psychotropic-order-sheet">
            <span class="form-label-top">Formulir 2</span>

            <header class="document-header">
                <h1>SURAT PESANAN PSIKOTROPIKA</h1>
                <p class="document-number">Nomor : {{ $purchaseOrder->psychotropic_document_number }}</p>
            </header>

            <section class="section">
                <p>Yang bertanda tangan di bawah ini :</p>
                <table class="field-table">
                    <tr><td class="field-label">Nama</td><td class="field-separator">:</td><td>{{ $pharmacistName }}</td></tr>
                    <tr><td class="field-label">Jabatan</td><td class="field-separator">:</td><td>Apoteker Penanggung Jawab</td></tr>
                </table>
            </section>

            <section class="section compact">
                <p>Mengajukan pesanan Psikotropika kepada :</p>
                <table class="field-table">
                    <tr><td class="field-label">Nama Distributor</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->nama ?: '-' }}</td></tr>
                    <tr><td class="field-label">Alamat</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->alamat ?: '-' }}</td></tr>
                    <tr><td class="field-label">Telp</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->telepon ?: '-' }}</td></tr>
                </table>
            </section>

            <section class="section compact">
                <p>dengan Psikotropika yang dipesan adalah :</p>
                <p>(Sebutkan nama obat, bentuk sediaan, kekuatan/potensi, jumlah dalam bentuk angka dan huruf)</p>
                <table class="medicine-table">
                    <thead>
                        <tr>
                            <th class="number-column">No.</th>
                            <th>Nama, bentuk sediaan, kekuatan/potensi</th>
                            <th class="quantity-column">Jumlah angka dan huruf</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($psychotropicDetails as $index => $detail)
                            @php
                                $medicine = $detail->obat;
                                $unitName = $detail->satuanKonversi?->satuan?->nama ?: ($medicine?->satuan?->nama ?: 'unit');
                                $preparation = $medicine?->sediaan?->nama ?: '-';
                                $strength = $medicine?->komposisi ?: ($medicine?->dosis ?: '-');
                                $quantity = number_format((float) $detail->qty, 0, ',', '.');
                            @endphp
                            <tr>
                                <td class="number-column">{{ $index + 1 }}</td>
                                <td>
                                    <span class="medicine-name">{{ $medicine?->nama_obat ?: '-' }}</span>
                                    <span class="medicine-meta">{{ $preparation }}; {{ $strength }}</span>
                                </td>
                                <td class="quantity-column">{{ $quantity }} {{ $unitName }} ({{ $detail->quantity_in_words }} {{ strtolower($unitName) }})</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="section compact">
                <p>Psikotropika tersebut akan dipergunakan untuk :</p>
                <table class="field-table">
                    <tr><td class="field-label">Nama Sarana</td><td class="field-separator">:</td><td>{{ $facilityName }} (Apotek)</td></tr>
                    <tr><td class="field-label">Alamat Sarana</td><td class="field-separator">:</td><td>{{ $facilityAddress }}</td></tr>
                </table>
            </section>

            <div class="signature-wrap">
                <div class="signature">
                    <p>{{ $city }}, {{ $formattedDate }}</p>
                    <p>Pesanan</p>
                    <p>Tanda tangan dan stempel</p>
                    <div class="signature-space"></div>
                    <div><span class="signature-name">{{ $pharmacistName }}</span></div>
                    <div>No. SIPA : {{ $pharmacistLicense }}</div>
                </div>
            </div>

            <section class="notes">
                <p>*) &nbsp;coret yang tidak perlu</p>
                <p>Catatan:</p>
                <p>Surat Pesanan dibuat sekurang-kurangnya 3 (tiga) rangkap.</p>
            </section>

            <span class="copy-mark">Rangkap {{ $copy }} dari {{ $copyCount }}</span>
        </main>
    @endfor

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
