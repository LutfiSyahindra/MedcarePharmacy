<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pesanan Prekursor Farmasi - {{ $purchaseOrder->no_po }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #111;
            background: #e9edf2;
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
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
            background: #b7791f;
            font-weight: 700;
            cursor: pointer;
        }
        .precursor-order-sheet {
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
        .precursor-order-sheet:last-child { page-break-after: auto; break-after: auto; }
        .form-label-top { position: absolute; top: 4mm; right: 7mm; font-size: 9.5pt; }
        .document-header { margin-top: 2mm; text-align: center; }
        .document-header h1 {
            margin: 0 0 1mm;
            font-size: 12pt;
            text-decoration: underline;
        }
        .document-number { margin: 0; font-size: 11pt; }
        .section { margin-top: 5mm; }
        .section.compact { margin-top: 3mm; }
        .section > p { margin: 0 0 1.5mm; }
        .field-table { width: 100%; border-collapse: collapse; }
        .field-table td { padding: .55mm 0; vertical-align: top; }
        .field-label { width: 38mm; white-space: nowrap; }
        .field-separator { width: 5mm; text-align: center; }
        .medicine-table {
            width: 100%;
            margin: 2.5mm 0 0;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9.5pt;
            line-height: 1.25;
        }
        .medicine-table th,
        .medicine-table td {
            padding: 1.7mm 2mm;
            border: .3mm solid #4b5563;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        .medicine-table th {
            padding-top: 1.5mm;
            padding-bottom: 1.5mm;
            background: #e9edf2;
            font-size: 9.25pt;
            font-weight: 700;
            line-height: 1.2;
            text-align: center;
            vertical-align: middle;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .medicine-table .number-column { width: 7%; text-align: center; }
        .medicine-table .name-column { width: 20%; }
        .medicine-table .preparation-column { width: 13%; }
        .medicine-table .strength-column { width: 22%; }
        .medicine-table .packaging-column { width: 14%; }
        .medicine-table .quantity-column { width: 24%; }
        .medicine-table tbody .number-column,
        .medicine-table tbody .quantity-column { vertical-align: middle; }
        .medicine-table tbody .quantity-column { text-align: center; }
        .commercial-section-heading {
            display: flex;
            align-items: center;
            gap: 2.5mm;
            margin: 1.5mm 0 2mm;
            color: #0f172a;
            font-family: Arial, sans-serif;
        }
        .commercial-section-heading .copy-role-badge {
            padding: 1mm 2mm;
            border: .25mm solid #64748b;
            border-radius: 1mm;
            background: #e2e8f0;
            font-size: 8.75pt;
            font-weight: 700;
            letter-spacing: .25pt;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .commercial-section-heading strong { font-size: 10.5pt; }
        .medicine-table.commercial-detail-table {
            border: .35mm solid #334155;
            font-family: Arial, sans-serif;
            font-size: 9.75pt;
            line-height: 1.35;
        }
        .medicine-table.commercial-detail-table th,
        .medicine-table.commercial-detail-table td { padding: 2.3mm 1.8mm; }
        .medicine-table.commercial-detail-table th {
            color: #fff;
            background: #334155;
            font-size: 9.25pt;
            letter-spacing: .1pt;
        }
        .medicine-table.commercial-detail-table .number-column { width: 5%; }
        .medicine-table.commercial-detail-table .name-column { width: 28%; }
        .medicine-table.commercial-detail-table .order-quantity-column { width: 8%; text-align: center; vertical-align: middle; }
        .medicine-table.commercial-detail-table .order-unit-column { width: 11%; text-align: center; vertical-align: middle; }
        .medicine-table.commercial-detail-table .price-column { width: 16%; text-align: right; vertical-align: middle; white-space: nowrap; }
        .medicine-table.commercial-detail-table .discount-column { width: 14%; text-align: center; vertical-align: middle; }
        .medicine-table.commercial-detail-table .total-price-column { width: 18%; text-align: right; vertical-align: middle; white-space: nowrap; }
        .medicine-table.commercial-detail-table .medicine-name { color: #0f172a; font-size: 10.25pt; }
        .medicine-meta { display: block; margin-top: 1mm; color: #475569; font-size: 9pt; line-height: 1.4; }
        .medicine-meta strong { color: #1e293b; }
        .order-quantity-value,
        .price-value,
        .total-price-value { color: #0f172a; font-size: 10.25pt; font-weight: 700; }
        .discount-tier { display: block; white-space: nowrap; font-size: 9.25pt; line-height: 1.45; }
        .medicine-name { display: block; font-size: 9.75pt; font-weight: 700; line-height: 1.25; }
        .signature-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 3.5mm;
        }
        .signature { width: 82mm; text-align: left; }
        .signature p { margin: 0 0 2.5mm; }
        .signature-space { height: 10mm; }
        .signature-name { display: inline-block; min-width: 65mm; font-weight: 700; text-decoration: underline; }
        .signature-role { margin: .5mm 0; font-size: 9.5pt; }
        .notes { margin-top: 3mm; font-size: 10pt; }
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
            .precursor-order-sheet {
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
        <span>{{ $precursorDetails->count() }} item Prekursor &middot; {{ $copyCount }} rangkap &middot; {{ $copyCount }} halaman</span>
        @if ($profileIncomplete)
            <span class="profile-warning">Nama Apoteker atau SIPA belum lengkap di Profile Apotek.</span>
        @endif
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    @for ($copy = 1; $copy <= $copyCount; $copy++)
        @php
            $showsCommercialDetails = $copy >= $copyCount - 1;
            $commercialCopyLabel = $copy === $copyCount ? 'Rangkap Internal' : 'Rangkap Distributor';
        @endphp
        <main class="precursor-order-sheet">
            <span class="form-label-top">Formulir 3</span>

            <header class="document-header">
                <h1>SURAT PESANAN OBAT/BAHAN OBAT/PREKURSOR FARMASI*</h1>
                <p class="document-number">Nomor : {{ $purchaseOrder->precursor_document_number }}</p>
            </header>

            <section class="section">
                <p>Yang bertanda tangan di bawah ini :</p>
                <table class="field-table">
                    <tr><td class="field-label">Nama</td><td class="field-separator">:</td><td>{{ $pharmacistName }}</td></tr>
                    <tr><td class="field-label">Jabatan</td><td class="field-separator">:</td><td>Apoteker Penanggung Jawab</td></tr>
                </table>
            </section>

            <section class="section compact">
                <p>Mengajukan pesanan Obat/Bahan Obat/Prekursor Farmasi* kepada :</p>
                <table class="field-table">
                    <tr><td class="field-label">Nama Distributor</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->nama ?: '-' }}</td></tr>
                    <tr><td class="field-label">Alamat</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->alamat ?: '-' }}</td></tr>
                    <tr><td class="field-label">Telp</td><td class="field-separator">:</td><td>{{ $purchaseOrder->distributor?->telepon ?: '-' }}</td></tr>
                </table>
            </section>

            <section class="section compact">
                <p>dengan Obat/Bahan Obat/Prekursor Farmasi* yang dipesan adalah :</p>
                @if ($showsCommercialDetails)
                    <div class="commercial-section-heading">
                        <span class="copy-role-badge">{{ $commercialCopyLabel }}</span>
                        <strong>Rincian pemesanan dan harga</strong>
                    </div>
                @else
                    <p>(Sebutkan nama obat, bentuk sediaan, kekuatan/potensi, jumlah dalam bentuk angka dan huruf, isi kemasan)</p>
                @endif
                <table class="medicine-table{{ $showsCommercialDetails ? ' commercial-detail-table' : '' }}">
                    <thead>
                        <tr>
                            <th class="number-column" scope="col">No.</th>
                            @if ($showsCommercialDetails)
                                <th class="name-column" scope="col">Obat dan spesifikasi</th>
                                <th class="order-quantity-column" scope="col">Qty order</th>
                                <th class="order-unit-column" scope="col">Satuan order</th>
                                <th class="price-column" scope="col">Harga dasar</th>
                                <th class="discount-column" scope="col">Diskon</th>
                                <th class="total-price-column" scope="col">Total harga</th>
                            @else
                                <th class="name-column" scope="col">Nama obat</th>
                                <th class="preparation-column" scope="col">Bentuk sediaan</th>
                                <th class="strength-column" scope="col">Kekuatan/potensi</th>
                                <th class="packaging-column" scope="col">Isi kemasan</th>
                                <th class="quantity-column" scope="col">Jumlah<br>(angka dan huruf)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($precursorDetails as $index => $detail)
                            @php
                                $medicine = $detail->obat;
                                $unitName = $detail->satuanKonversi?->satuan?->nama ?: ($medicine?->satuan?->nama ?: 'unit');
                                $preparation = $medicine?->sediaan?->nama ?: '-';
                                $strength = $medicine?->komposisi ?: ($medicine?->dosis ?: '-');
                                $packaging = $medicine?->kemasan ?: '-';
                                $quantity = number_format((float) $detail->qty, 0, ',', '.');
                                $basePrice = (float) $detail->harga_estimasi;
                                $totalPrice = $detail->subtotal !== null
                                    ? (float) $detail->subtotal
                                    : \App\Support\TieredDiscount::netAmount(
                                        (float) $detail->qty * $basePrice,
                                        $detail->diskon_1,
                                        $detail->diskon_2,
                                        $detail->diskon_3
                                    );
                                $formattedBasePrice = number_format($basePrice, 0, ',', '.');
                                $formattedTotalPrice = number_format($totalPrice, 0, ',', '.');
                                $formatDiscount = static fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
                            @endphp
                            <tr>
                                <td class="number-column">{{ $index + 1 }}</td>
                                @if ($showsCommercialDetails)
                                    <td class="name-column">
                                        <span class="medicine-name">{{ $medicine?->nama_obat ?: '-' }}</span>
                                        <span class="medicine-meta"><strong>Bentuk:</strong> {{ $preparation }}<br><strong>Kekuatan:</strong> {{ $strength }}<br><strong>Kemasan:</strong> {{ $packaging }}</span>
                                    </td>
                                    <td class="order-quantity-column"><span class="order-quantity-value">{{ $quantity }}</span></td>
                                    <td class="order-unit-column">{{ $unitName }}</td>
                                    <td class="price-column"><span class="price-value">Rp {{ $formattedBasePrice }}</span></td>
                                    <td class="discount-column">
                                        <span class="discount-tier">D1 {{ $formatDiscount($detail->diskon_1) }}%</span>
                                        <span class="discount-tier">D2 {{ $formatDiscount($detail->diskon_2) }}%</span>
                                        <span class="discount-tier">D3 {{ $formatDiscount($detail->diskon_3) }}%</span>
                                    </td>
                                    <td class="total-price-column"><span class="total-price-value">Rp {{ $formattedTotalPrice }}</span></td>
                                @else
                                    <td class="name-column"><span class="medicine-name">{{ $medicine?->nama_obat ?: '-' }}</span></td>
                                    <td class="preparation-column">{{ $preparation }}</td>
                                    <td class="strength-column">{{ $strength }}</td>
                                    <td class="packaging-column">{{ $packaging }}</td>
                                    <td class="quantity-column">{{ $quantity }} {{ $unitName }} ({{ $detail->quantity_in_words }} {{ strtolower($unitName) }})</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="section compact">
                <p>Obat/Bahan Obat/Prekursor Farmasi tersebut akan dipergunakan untuk :</p>
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
                    <div class="signature-role">Apoteker/Tenaga Teknis Kefarmasian</div>
                    <div>No. SIPA/SIKTTK : {{ $pharmacistLicense }}</div>
                </div>
            </div>

            <section class="notes">
                <p>*) &nbsp;coret yang tidak perlu</p>
                <p>Catatan:</p>
                <p>Surat Pesanan dibuat 4 (empat) rangkap.</p>
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
