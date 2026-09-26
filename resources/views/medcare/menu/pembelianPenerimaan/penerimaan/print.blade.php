<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penerimaan {{ $penerimaan->nomor_penerimaan }}</title>
    <style>
        @page { size: A4 portrait; margin: 9mm; }

        :root {
            --ink: #172033;
            --muted: #64748b;
            --line: #d8e1ea;
            --soft: #f4f7fa;
            --brand: #0f766e;
            --brand-deep: #164e63;
            --brand-soft: #e6f5f2;
            --danger: #b42318;
            --warning: #9a6700;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: var(--ink);
            background: #e8edf2;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            font-size: 9.25px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-toolbar {
            position: sticky;
            z-index: 10;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 11px 18px;
            color: #fff;
            background: #172033;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .2);
        }

        .print-toolbar__copy strong,
        .print-toolbar__copy span { display: block; }
        .print-toolbar__copy strong { font-size: 13px; }
        .print-toolbar__copy span { color: #cbd5e1; font-size: 11px; }
        .print-toolbar__actions { display: flex; gap: 8px; }
        .print-toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 13px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 7px;
            color: #fff;
            background: transparent;
            font: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .print-toolbar button.primary { border-color: var(--brand); background: var(--brand); }
        .print-toolbar svg { width: 15px; height: 15px; }

        .sheet {
            position: relative;
            width: 192mm;
            min-height: 279mm;
            margin: 10mm auto;
            padding: 8mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .13);
        }

        .sheet::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2.2mm;
            content: "";
            background: linear-gradient(90deg, var(--brand-deep), var(--brand) 58%, #5eead4);
        }

        .document-header {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            gap: 6mm;
            padding: 2mm 0 4mm;
            border-bottom: .45mm solid var(--ink);
        }

        .brand { display: flex; align-items: center; gap: 4mm; min-width: 0; }
        .brand-logo,
        .brand-fallback {
            width: 16mm;
            height: 16mm;
            flex: 0 0 16mm;
            border-radius: 4mm;
        }
        .brand-logo { object-fit: contain; }
        .brand-fallback {
            display: grid;
            place-items: center;
            color: #fff;
            background: linear-gradient(145deg, var(--brand-deep), var(--brand));
            font-size: 14pt;
            font-weight: 800;
            letter-spacing: -.5pt;
        }
        .brand-name { margin: 0 0 .6mm; font-size: 14pt; line-height: 1.1; }
        .brand-address { max-width: 125mm; color: var(--muted); font-size: 8.5px; }
        .brand-contact { margin-top: .7mm; color: var(--brand-deep); font-size: 8.5px; font-weight: 700; }

        .document-identity { min-width: 55mm; text-align: right; }
        .document-kicker {
            margin-bottom: 1mm;
            color: var(--brand);
            font-size: 7.5px;
            font-weight: 800;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }
        .document-title { margin: 0; font-size: 17pt; line-height: 1.05; letter-spacing: -.35pt; }
        .document-number { margin-top: 2mm; font-size: 10.5pt; font-weight: 800; }
        .status-pill {
            display: inline-block;
            margin-top: 2mm;
            padding: 1mm 2.5mm;
            border: .25mm solid currentColor;
            border-radius: 999px;
            color: var(--brand);
            background: var(--brand-soft);
            font-size: 7.5px;
            font-weight: 800;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .status-pill.is-draft { color: var(--warning); background: #fffaeb; }
        .status-pill.is-cancelled { color: var(--danger); background: #fff1f0; }

        .meta-grid {
            display: grid;
            grid-template-columns: .95fr 1.35fr 1fr;
            gap: 2mm;
            margin: 4mm 0;
        }
        .meta-card {
            min-width: 0;
            padding: 2.5mm;
            border: .25mm solid var(--line);
            border-radius: 2.2mm;
            background: #fff;
        }
        .meta-card__title {
            margin-bottom: 2mm;
            color: var(--brand);
            font-size: 7px;
            font-weight: 800;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }
        .meta-list { width: 100%; border-collapse: collapse; }
        .meta-list td { padding: .45mm 0; vertical-align: top; }
        .meta-list td:first-child { width: 25mm; color: var(--muted); }
        .meta-list td:last-child { font-weight: 700; overflow-wrap: anywhere; }
        .supplier-name { margin: 0 0 .8mm; font-size: 10.5px; }
        .supplier-detail { color: var(--muted); }

        .section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 3mm;
            margin: 0 0 2mm;
        }
        .section-heading h2 { margin: 0; font-size: 10.5pt; }
        .section-heading span { color: var(--muted); font-size: 8px; }

        .items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            border: .25mm solid #bcc9d6;
            border-radius: 2mm;
            overflow: hidden;
        }
        .items thead { display: table-header-group; }
        .items th {
            padding: 2mm 1.4mm;
            border-right: .2mm solid #3e6670;
            color: #fff;
            background: var(--brand-deep);
            font-size: 7.25px;
            font-weight: 800;
            letter-spacing: .35px;
            line-height: 1.2;
            text-align: left;
            text-transform: uppercase;
        }
        .items td {
            padding: 1.8mm 1.4mm;
            border-top: .2mm solid var(--line);
            border-right: .2mm solid var(--line);
            vertical-align: top;
        }
        .items th:last-child,
        .items td:last-child { border-right: 0; }
        .items tbody tr:first-child td { border-top: 0; }
        .items tbody tr:nth-child(even) td { background: #f8fafb; }
        .items tr { break-inside: avoid; page-break-inside: avoid; }
        .items .center { text-align: center; }
        .items .right { text-align: right; }
        .items .nowrap { white-space: nowrap; }
        .item-name { display: block; font-size: 9px; font-weight: 800; }
        .item-code,
        .item-subline { display: block; margin-top: .5mm; color: var(--muted); font-size: 7.5px; }
        .discount-line { display: block; white-space: nowrap; }

        .after-table {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 74mm;
            align-items: start;
            gap: 5mm;
            margin-top: 3.5mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .notes-panel {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2mm;
        }
        .note-box {
            min-height: 24mm;
            padding: 3mm;
            border: .25mm solid var(--line);
            border-radius: 2mm;
            background: var(--soft);
        }
        .note-box__title {
            display: block;
            margin-bottom: 1.5mm;
            color: var(--brand-deep);
            font-size: 7px;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
        }
        .payment-row { display: flex; justify-content: space-between; gap: 4mm; margin-top: .8mm; }
        .payment-row span:first-child { color: var(--muted); }

        .summary {
            padding: 2.5mm 3mm;
            border: .35mm solid var(--brand-deep);
            border-radius: 2mm;
        }
        .summary-row { display: flex; justify-content: space-between; gap: 4mm; padding: .8mm 0; }
        .summary-row span:first-child { color: var(--muted); }
        .summary-row strong { white-space: nowrap; }
        .summary-row.is-deduction strong { color: var(--danger); }
        .summary-total {
            margin-top: 1mm;
            padding-top: 2mm;
            border-top: .35mm solid var(--brand-deep);
            font-size: 11px;
        }
        .summary-total span:first-child { color: var(--ink); font-weight: 800; }
        .summary-total strong { color: var(--brand-deep); font-size: 12px; }

        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5mm;
            margin-top: 6mm;
            text-align: center;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .signature-role { font-weight: 800; }
        .signature-space { height: 13mm; }
        .signature-name { padding-top: 1.5mm; border-top: .25mm solid #8190a3; font-weight: 700; }
        .signature-caption { margin-top: .5mm; color: var(--muted); font-size: 7.5px; }

        .document-footer {
            display: flex;
            justify-content: space-between;
            gap: 8mm;
            margin-top: 5mm;
            padding-top: 2mm;
            border-top: .25mm solid var(--line);
            color: var(--muted);
            font-size: 7px;
        }
        .document-footer strong { color: var(--ink); }

        .watermark {
            position: fixed;
            z-index: 0;
            top: 46%;
            left: 50%;
            display: none;
            color: rgba(180, 35, 24, .09);
            font-size: 56pt;
            font-weight: 900;
            letter-spacing: 4px;
            transform: translate(-50%, -50%) rotate(-18deg);
            pointer-events: none;
        }
        .watermark.is-visible { display: block; }
        .sheet > *:not(.watermark) { position: relative; z-index: 1; }

        @media print {
            body { background: #fff; }
            .print-toolbar { display: none !important; }
            .sheet { width: auto; min-height: 0; margin: 0; padding: 0; overflow: visible; box-shadow: none; }
            .sheet::before { top: -3mm; }
        }

        @media screen and (max-width: 1100px) {
            .sheet { margin: 0; transform-origin: top left; }
        }
    </style>
</head>
<body>
    @php
        $purchaseOrder = $penerimaan->purchaseOrder;
        $branch = $purchaseOrder?->branch;
        $profile = $branch?->apotekProfile;
        $supplier = $penerimaan->distributor;
        $facilityName = $profile?->name ?: ($branch?->name ?: config('app.name', 'Medcare'));
        $facilityAddress = $profile?->address ?: ($branch?->address ?: '-');
        $facilityLocation = collect([$profile?->village, $profile?->district, $profile?->city, $profile?->province, $profile?->postal_code])
            ->filter(fn ($value) => filled($value))
            ->implode(', ');
        $facilityContacts = collect([
            $profile?->phone ?: $branch?->phone,
            $profile?->email ?: $branch?->email,
            $profile?->website,
        ])->filter(fn ($value) => filled($value))->implode('  •  ');
        $initials = collect(preg_split('/\s+/', trim($facilityName)))
            ->filter()
            ->take(2)
            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
        $initials = $initials ?: 'MC';
        $statusMeta = [
            'draft' => ['label' => 'Draft', 'class' => 'is-draft'],
            'posted' => ['label' => 'Sudah Diposting', 'class' => 'is-posted'],
            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'is-cancelled'],
        ][$penerimaan->status] ?? ['label' => ucfirst((string) $penerimaan->status), 'class' => ''];
        $paymentLabels = [
            'belum_dibayar' => 'Belum Dibayar',
            'sebagian' => 'Dibayar Sebagian',
            'lunas' => 'Lunas',
        ];
        $formatDate = static fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '-';
        $formatDateTime = static fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y H:i') : '-';
        $formatNumber = static function ($value, $decimals = 2) {
            $formatted = number_format((float) $value, $decimals, ',', '.');
            return $decimals > 0 ? rtrim(rtrim($formatted, '0'), ',') : $formatted;
        };
        $formatMoney = static fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
        $totalFaktur = (float) ($penerimaan->total_faktur ?: $penerimaan->grand_total);
        $compensationDiscount = (float) ($penerimaan->supplier_compensation_discount ?? 0);
        $payableTotal = max(0, $totalFaktur - $compensationDiscount);
        $printedBy = auth()->user()?->name ?: '-';
    @endphp

    <div class="print-toolbar">
        <div class="print-toolbar__copy">
            <strong>Preview Penerimaan Barang</strong>
            <span>{{ $penerimaan->nomor_penerimaan }} · {{ $penerimaan->details->count() }} item</span>
        </div>
        <div class="print-toolbar__actions">
            <button type="button" onclick="window.close()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </button>
            <button type="button" class="primary" onclick="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
                Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <main class="sheet">
        <div class="watermark {{ $penerimaan->status === 'cancelled' ? 'is-visible' : '' }}">DIBATALKAN</div>

        <header class="document-header">
            <div class="brand">
                @if ($profile?->logo_url)
                    <img class="brand-logo" src="{{ $profile->logo_url }}" alt="Logo {{ $facilityName }}">
                @else
                    <span class="brand-fallback" aria-hidden="true">{{ $initials }}</span>
                @endif
                <div>
                    <h1 class="brand-name">{{ $facilityName }}</h1>
                    <div class="brand-address">{{ $facilityAddress }}@if ($facilityLocation) · {{ $facilityLocation }}@endif</div>
                    @if ($facilityContacts)
                        <div class="brand-contact">{{ $facilityContacts }}</div>
                    @endif
                </div>
            </div>
            <div class="document-identity">
                <div class="document-kicker">Dokumen Gudang &amp; Pembelian</div>
                <h2 class="document-title">PENERIMAAN BARANG</h2>
                <div class="document-number">{{ $penerimaan->nomor_penerimaan }}</div>
                <span class="status-pill {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
            </div>
        </header>

        <section class="meta-grid">
            <div class="meta-card">
                <div class="meta-card__title">Referensi Dokumen</div>
                <table class="meta-list">
                    <tr><td>Nomor PO</td><td>{{ $purchaseOrder?->no_po ?: '-' }}</td></tr>
                    <tr><td>Nomor Faktur</td><td>{{ $penerimaan->nomor_faktur ?: '-' }}</td></tr>
                    <tr><td>Surat Jalan</td><td>{{ $penerimaan->nomor_surat_jalan ?: '-' }}</td></tr>
                </table>
            </div>
            <div class="meta-card">
                <div class="meta-card__title">Supplier / Distributor</div>
                <h3 class="supplier-name">{{ $supplier?->nama ?: '-' }}</h3>
                <div class="supplier-detail">{{ $supplier?->alamat ?: 'Alamat supplier belum tersedia.' }}</div>
                @if ($supplier?->telepon || $supplier?->email)
                    <div class="supplier-detail">{{ collect([$supplier?->telepon, $supplier?->email])->filter()->implode('  •  ') }}</div>
                @endif
            </div>
            <div class="meta-card">
                <div class="meta-card__title">Tanggal &amp; Termin</div>
                <table class="meta-list">
                    <tr><td>Tanggal PO</td><td>{{ $formatDate($purchaseOrder?->tanggal_po) }}</td></tr>
                    <tr><td>Diterima</td><td>{{ $formatDate($penerimaan->tanggal_penerimaan) }}</td></tr>
                    <tr><td>Tanggal Faktur</td><td>{{ $formatDate($penerimaan->tanggal_faktur) }}</td></tr>
                    <tr><td>Jatuh Tempo</td><td>{{ $formatDate($penerimaan->tanggal_jatuh_tempo) }}</td></tr>
                </table>
            </div>
        </section>

        <section>
            <div class="section-heading">
                <h2>Rincian Barang Diterima</h2>
                <span>{{ $formatNumber($penerimaan->total_barang, 0) }} item · Total {{ $formatNumber($penerimaan->total_qty) }} satuan pembelian</span>
            </div>
            <table class="items">
                <colgroup>
                    <col style="width: 4%">
                    <col style="width: 23%">
                    <col style="width: 11%">
                    <col style="width: 6.5%">
                    <col style="width: 8.5%">
                    <col style="width: 7%">
                    <col style="width: 11%">
                    <col style="width: 8%">
                    <col style="width: 6%">
                    <col style="width: 15%">
                </colgroup>
                <thead>
                    <tr>
                        <th class="center">No</th>
                        <th>Nama Barang</th>
                        <th>Batch / ED</th>
                        <th class="center">Qty PO</th>
                        <th class="center">Qty Diterima</th>
                        <th class="center">Satuan</th>
                        <th class="right">Harga Beli</th>
                        <th class="center">Diskon</th>
                        <th class="center">PPN</th>
                        <th class="right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penerimaan->details as $detail)
                        @php
                            $purchaseUnit = $detail->satuan_beli
                                ?: ($detail->purchaseOrderDetail?->satuanKonversi?->satuan?->nama
                                    ?: ($detail->obat?->satuan?->nama ?: '-'));
                            $stockUnit = $detail->satuan_stok ?: ($detail->obat?->satuan?->nama ?: '-');
                            $conversion = max(1, (float) ($detail->konversi_satuan ?: 1));
                            $stockQty = (float) ($detail->qty_diterima_stok ?: ((float) $detail->qty_diterima * $conversion));
                        @endphp
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="item-name">{{ $detail->obat?->nama_obat ?: '-' }}</span>
                                <span class="item-code">{{ $detail->obat?->kode_obat ?: '-' }}</span>
                            </td>
                            <td>
                                <strong>{{ $detail->no_batch ?: '-' }}</strong>
                                <span class="item-subline">ED {{ $formatDate($detail->expired_date) }}</span>
                            </td>
                            <td class="center">{{ $formatNumber($detail->qty_po) }}</td>
                            <td class="center">
                                <strong>{{ $formatNumber($detail->qty_diterima) }}</strong>
                                @if ($conversion > 1)
                                    <span class="item-subline">{{ $formatNumber($stockQty) }} {{ $stockUnit }}</span>
                                @endif
                            </td>
                            <td class="center">
                                {{ $purchaseUnit }}
                                @if ($conversion > 1)
                                    <span class="item-subline">1 : {{ $formatNumber($conversion) }}</span>
                                @endif
                            </td>
                            <td class="right nowrap">{{ $formatMoney($detail->harga_beli) }}</td>
                            <td class="center">
                                <span class="discount-line">D1 {{ $formatNumber($detail->diskon_1) }}%</span>
                                <span class="discount-line">D2 {{ $formatNumber($detail->diskon_2) }}%</span>
                                <span class="discount-line">D3 {{ $formatNumber($detail->diskon_3) }}%</span>
                            </td>
                            <td class="center">{{ $formatNumber($detail->ppn) }}%</td>
                            <td class="right nowrap"><strong>{{ $formatMoney($detail->total) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="center">Tidak ada detail barang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="after-table">
            <div class="notes-panel">
                <div class="note-box">
                    <span class="note-box__title">Catatan Penerimaan</span>
                    {{ $penerimaan->catatan ?: 'Tidak ada catatan tambahan.' }}
                </div>
                <div class="note-box">
                    <span class="note-box__title">Informasi Pembayaran</span>
                    <div class="payment-row"><span>Status</span><strong>{{ $paymentLabels[$penerimaan->status_pembayaran] ?? ucfirst(str_replace('_', ' ', (string) $penerimaan->status_pembayaran)) }}</strong></div>
                    <div class="payment-row"><span>Sudah dibayar</span><strong>{{ $formatMoney($penerimaan->jumlah_dibayar) }}</strong></div>
                    <div class="payment-row"><span>Sisa hutang</span><strong>{{ $formatMoney($penerimaan->sisa_hutang) }}</strong></div>
                </div>
            </div>
            <div class="summary">
                <div class="summary-row"><span>Subtotal</span><strong>{{ $formatMoney($penerimaan->subtotal) }}</strong></div>
                <div class="summary-row is-deduction"><span>Diskon</span><strong>- {{ $formatMoney($penerimaan->diskon ?: $penerimaan->total_diskon) }}</strong></div>
                <div class="summary-row"><span>PPN</span><strong>{{ $formatMoney($penerimaan->pajak ?: $penerimaan->total_ppn) }}</strong></div>
                <div class="summary-row"><span>Biaya lain</span><strong>{{ $formatMoney($penerimaan->biaya_lain) }}</strong></div>
                @if ($compensationDiscount > 0)
                    <div class="summary-row is-deduction"><span>Potongan ganti rugi</span><strong>- {{ $formatMoney($compensationDiscount) }}</strong></div>
                @endif
                <div class="summary-row summary-total"><span>Total Tagihan</span><strong>{{ $formatMoney($payableTotal) }}</strong></div>
            </div>
        </section>

        <section class="signatures">
            <div>
                <div class="signature-role">Petugas Penerima</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $penerimaan->createdBy?->name ?: 'Nama / Tanda tangan' }}</div>
                <div class="signature-caption">Tanggal: {{ $formatDate($penerimaan->tanggal_penerimaan) }}</div>
            </div>
            <div>
                <div class="signature-role">Pemeriksa</div>
                <div class="signature-space"></div>
                <div class="signature-name">Nama / Tanda tangan</div>
                <div class="signature-caption">Verifikasi barang dan dokumen</div>
            </div>
            <div>
                <div class="signature-role">Apoteker / Penanggung Jawab</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $penerimaan->postedBy?->name ?: ($profile?->pharmacist_name ?: 'Nama / Tanda tangan') }}</div>
                <div class="signature-caption">
                    @if ($penerimaan->posted_at)
                        Diposting {{ $formatDateTime($penerimaan->posted_at) }}
                    @elseif ($profile?->pharmacist_license_number)
                        SIPA {{ $profile->pharmacist_license_number }}
                    @else
                        Validasi akhir penerimaan
                    @endif
                </div>
            </div>
        </section>

        <footer class="document-footer">
            <span><strong>{{ $penerimaan->nomor_penerimaan }}</strong> · Dokumen tercatat pada sistem Medcare</span>
            <span>Dicetak {{ now()->format('d/m/Y H:i') }} oleh {{ $printedBy }}</span>
        </footer>
    </main>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', function () {
                window.focus();
                window.setTimeout(function () { window.print(); }, 150);
            });
        </script>
    @endif
</body>
</html>
