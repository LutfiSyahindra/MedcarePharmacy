<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lembar Stock Opname {{ $opname->nomor }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #111827; font: 11px Arial, sans-serif; }
        .toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .toolbar button { border: 0; border-radius: 6px; padding: 9px 16px; color: #fff; background: #1d4ed8; font-weight: 700; cursor: pointer; }
        .header { display: flex; justify-content: space-between; gap: 24px; padding-bottom: 10px; border-bottom: 2px solid #111827; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .subtitle { color: #4b5563; }
        .meta { min-width: 310px; border-collapse: collapse; }
        .meta td { padding: 2px 4px; }
        .meta td:first-child { color: #6b7280; }
        .notice { margin: 10px 0; padding: 7px 10px; border: 1px solid #bfdbfe; background: #eff6ff; font-weight: 700; }
        table.items { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .items th, .items td { border: 1px solid #4b5563; padding: 5px 6px; vertical-align: top; }
        .items th { background: #e5e7eb; text-align: left; }
        .items .no { width: 34px; text-align: center; }
        .items .code { width: 95px; }
        .items .name { width: 22%; }
        .items .rack { width: 13%; }
        .items .batch { width: 16%; }
        .items .unit { width: 70px; }
        .items .physical { width: 105px; height: 28px; }
        .page-break { page-break-before: always; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; margin-top: 24px; text-align: center; }
        .signature-space { height: 55px; }
        .signature-line { border-top: 1px solid #111827; padding-top: 4px; }
        .footer { margin-top: 10px; color: #6b7280; font-size: 9px; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak Lembar Opname</button></div>
    <header class="header">
        <div>
            <h1>LEMBAR PENGHITUNGAN STOCK OPNAME</h1>
            <div class="subtitle">Blind count — stok sistem sengaja tidak dicantumkan</div>
        </div>
        <table class="meta">
            <tr><td>Nomor</td><td>: <strong>{{ $opname->nomor }}</strong></td></tr>
            <tr><td>Tanggal</td><td>: {{ optional($opname->tanggal_opname)->format('d/m/Y') }}</td></tr>
            <tr><td>Cabang</td><td>: {{ $opname->branch?->name ?: '-' }}</td></tr>
            <tr><td>Area</td><td>: {{ $opname->rack ? $opname->rack->kode.' - '.$opname->rack->nama : 'Semua Rak' }}</td></tr>
        </table>
    </header>

    <div class="notice">Petunjuk: hitung stok fisik langsung di rak, tulis angkanya pada kolom kosong, lalu input hasilnya ke sistem tanpa membuka referensi stok lain.</div>

    <table class="items">
        <thead><tr><th class="no">No</th><th class="code">Kode</th><th class="name">Nama Obat</th><th class="rack">Rak</th><th class="batch">Batch / ED</th><th class="unit">Satuan</th><th class="physical">Stok Fisik</th><th>Catatan</th></tr></thead>
        <tbody>
            @foreach ($opname->details as $detail)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td>{{ $detail->kode_obat ?: '-' }}</td>
                    <td><strong>{{ $detail->nama_obat }}</strong></td>
                    <td>{{ $detail->rack ? $detail->rack->kode.' - '.$detail->rack->nama : '-' }}</td>
                    <td>{{ $detail->no_batch }}<br><small>ED: {{ optional($detail->expired_date)->format('d/m/Y') ?: '-' }}</small></td>
                    <td>{{ $detail->satuan ?: '-' }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <section class="signatures">
        <div><div>Petugas Hitung</div><div class="signature-space"></div><div class="signature-line">Nama / Tanda Tangan</div></div>
        <div><div>Saksi</div><div class="signature-space"></div><div class="signature-line">Nama / Tanda Tangan</div></div>
        <div><div>Apoteker / Penanggung Jawab</div><div class="signature-space"></div><div class="signature-line">Nama / Tanda Tangan</div></div>
    </section>
    <div class="footer">Dicetak {{ now()->format('d/m/Y H:i') }} · Dokumen ini tidak menampilkan stok sistem untuk menjaga integritas blind count.</div>
</body>
</html>
