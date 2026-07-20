<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->nomor_transaksi }}</title>
    <style>
        :root {
            --ink: #172033;
            --muted: #667085;
            --line: #cfd4dc;
            --brand: #00a9e9;
            --paper: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 20px 0;
            background: #eef1f5;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        .receipt {
            width: 80mm;
            margin: 0 auto;
            padding: 5mm 4mm 6mm;
            overflow: hidden;
            background: var(--paper);
            box-shadow: 0 12px 32px rgba(16, 24, 40, 0.14);
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-logo {
            display: block;
            width: 48mm;
            max-width: 100%;
            max-height: 17mm;
            margin: 0 auto 2.5mm;
            object-fit: contain;
        }

        .branch-name {
            margin: 0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .branch-slogan {
            margin: 0.5mm auto 0;
            color: var(--muted);
            font-size: 8.5px;
            font-style: italic;
        }

        .branch-detail {
            max-width: 66mm;
            margin: 0.6mm auto 0;
            color: var(--muted);
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        .branch-contact span + span::before {
            content: "  |  ";
            color: var(--line);
        }

        .document-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 3mm;
            margin: 4mm 0 3mm;
            padding: 2mm 0;
            border-top: 1px solid var(--ink);
            border-bottom: 1px solid var(--ink);
        }

        .document-title {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }

        .status-badge {
            flex: 0 0 auto;
            padding: 0.7mm 1.8mm;
            border: 1px solid currentColor;
            border-radius: 10mm;
            color: #027a48;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1;
            text-transform: uppercase;
        }

        .status-badge.credit,
        .status-badge.unpaid {
            color: #b54708;
        }

        .status-badge.void {
            color: #b42318;
        }

        .meta {
            display: grid;
            grid-template-columns: 19mm 2mm minmax(0, 1fr);
            row-gap: 0.8mm;
        }

        .meta-row {
            display: contents;
        }

        .meta-label,
        .meta-value {
            min-width: 0;
        }

        .meta-label {
            color: var(--muted);
        }

        .meta .separator {
            color: var(--muted);
            text-align: center;
        }

        .meta-value {
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .divider {
            height: 0;
            margin: 3mm 0;
            border: 0;
            border-top: 1px dashed var(--line);
        }

        .section-label {
            margin-bottom: 2mm;
            color: var(--muted);
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .item {
            break-inside: avoid;
            margin-bottom: 2.8mm;
        }

        .item:last-child {
            margin-bottom: 0;
        }

        .item-primary {
            display: grid;
            grid-template-columns: 4.5mm minmax(0, 1fr) auto;
            gap: 1mm;
            align-items: start;
        }

        .item-number {
            color: var(--muted);
        }

        .item-name {
            min-width: 0;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .item-total {
            padding-left: 1.5mm;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .item-secondary,
        .item-discount,
        .prescription-note {
            margin-top: 0.5mm;
            margin-left: 5.5mm;
            color: var(--muted);
            font-size: 9px;
        }

        .item-discount {
            display: flex;
            justify-content: space-between;
            gap: 3mm;
        }

        .prescription-note {
            padding-left: 1.8mm;
            border-left: 1.5px solid var(--brand);
            line-height: 1.45;
        }

        .prescription-note strong {
            color: var(--ink);
        }

        .summary-row,
        .payment-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 4mm;
            margin: 1mm 0;
        }

        .summary-row span:last-child,
        .payment-amount {
            flex: 0 0 auto;
            text-align: right;
            white-space: nowrap;
        }

        .summary-row.discount {
            color: var(--muted);
        }

        .grand-total {
            margin: 2mm 0 1.5mm;
            padding: 2mm 0;
            border-top: 1.5px solid var(--ink);
            border-bottom: 1.5px solid var(--ink);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .payment-info {
            min-width: 0;
        }

        .payment-method {
            font-weight: 700;
        }

        .payment-reference {
            color: var(--muted);
            font-size: 8.5px;
            overflow-wrap: anywhere;
        }

        .transaction-note {
            margin-top: 2mm;
            padding: 2mm;
            border: 1px solid var(--line);
            border-radius: 1.5mm;
            color: var(--muted);
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        .receipt-footer {
            margin-top: 4mm;
            text-align: center;
        }

        .thank-you {
            margin: 0;
            font-size: 11px;
            font-weight: 800;
        }

        .footer-note {
            margin: 1mm auto 0;
            color: var(--muted);
            font-size: 8.5px;
            white-space: pre-line;
        }

        .pharmacy-legal {
            margin: 2mm auto 0;
            color: var(--muted);
            font-size: 8px;
        }

        .footer-mark {
            width: 13mm;
            margin: 3mm auto 0;
            border-top: 2px solid var(--brand);
        }

        .print-button {
            position: fixed;
            top: 18px;
            right: 18px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #172033;
            color: #fff;
            font: 700 13px Arial, sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(16, 24, 40, 0.18);
        }

        body.is-embedded {
            min-height: 100vh;
            padding: 14px 0 24px;
            background: #f5f6f8;
        }

        body.is-embedded .receipt {
            box-shadow: 0 6px 24px rgba(16, 24, 40, 0.1);
        }

        @media print {
            html,
            body {
                width: 80mm;
                background: #fff;
            }

            body {
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .receipt {
                width: 80mm;
                margin: 0;
                padding: 4mm 4mm 5mm;
                box-shadow: none;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>

<body class="{{ $embedded ? 'is-embedded' : '' }}">
    @php
        $currency = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $number = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
        $isPrescription = in_array($transaction->jenis_transaksi, ['penjualan_resep', 'penjualan_racikan'], true);
        $isCompoundPrescription = $transaction->jenis_transaksi === 'penjualan_racikan';
        $cashier = $transaction->completedBy->name ?? $transaction->createdBy->name ?? '-';
        $pharmacyName = $apotekProfile
            ? $apotekProfile->name
            : ($transaction->branch?->name ?: 'Medcare Pharmacy');
        $pharmacyLogo = $apotekProfile
            ? $apotekProfile->logo_url
            : asset('assets/apotek/LogoResmi.png');
        $pharmacyAddress = $apotekProfile?->address ?: $transaction->branch?->address;
        $pharmacyPhone = $apotekProfile ? $apotekProfile->phone : $transaction->branch?->phone;
        $pharmacyEmail = $apotekProfile ? $apotekProfile->email : $transaction->branch?->email;
        $paymentStatusLabels = [
            'paid' => 'Lunas',
            'credit' => 'Kredit',
            'unpaid' => 'Belum Lunas',
            'void' => 'Dibatalkan',
        ];
        $consumptionLabels = [
            'sebelum_makan' => 'sebelum makan',
            'sesudah_makan' => 'sesudah makan',
            'bersama_makan' => 'bersama makan',
            'tidak_terkait_makan' => 'tidak terkait makan',
            'sesuai_instruksi' => 'sesuai instruksi dokter',
        ];
    @endphp

    @unless ($embedded)
        <button type="button" class="print-button" onclick="window.print()">Cetak Struk</button>
    @endunless

    <main class="receipt">
        <header class="receipt-header">
            @if ($pharmacyLogo)
                <img
                    src="{{ $pharmacyLogo }}"
                    alt="Logo {{ $pharmacyName }}"
                    class="receipt-logo"
                >
            @endif
            <p class="branch-name">{{ $pharmacyName }}</p>
            @if ($apotekProfile?->slogan)
                <p class="branch-slogan">{{ $apotekProfile->slogan }}</p>
            @endif
            @if ($pharmacyAddress)
                <p class="branch-detail">{{ $pharmacyAddress }}</p>
            @endif
            @if ($pharmacyPhone || $apotekProfile?->whatsapp || $pharmacyEmail)
                <p class="branch-detail branch-contact">
                    @if ($pharmacyPhone)
                        <span>Tel. {{ $pharmacyPhone }}</span>
                    @endif
                    @if ($apotekProfile?->whatsapp)
                        <span>WA {{ $apotekProfile->whatsapp }}</span>
                    @endif
                    @if ($pharmacyEmail)
                        <span>{{ $pharmacyEmail }}</span>
                    @endif
                </p>
            @endif
            @if ($apotekProfile?->website || $apotekProfile?->instagram)
                <p class="branch-detail branch-contact">
                    @if ($apotekProfile?->website)
                        <span>{{ $apotekProfile->website }}</span>
                    @endif
                    @if ($apotekProfile?->instagram)
                        <span>{{ $apotekProfile->instagram }}</span>
                    @endif
                </p>
            @endif
        </header>

        <section class="document-heading" aria-label="Status transaksi">
            <span class="document-title">Bukti Pembayaran</span>
            <span class="status-badge {{ $transaction->payment_status }}">
                {{ $paymentStatusLabels[$transaction->payment_status] ?? $transaction->payment_status }}
            </span>
        </section>

        <div class="meta">
            <div class="meta-row"><span class="meta-label">No. Transaksi</span><span class="separator">:</span><span class="meta-value">{{ $transaction->nomor_transaksi }}</span></div>
            <div class="meta-row"><span class="meta-label">Tanggal</span><span class="separator">:</span><span class="meta-value">{{ optional($transaction->tanggal_transaksi)->format('d/m/Y H:i') }}</span></div>
            <div class="meta-row"><span class="meta-label">Kasir</span><span class="separator">:</span><span class="meta-value">{{ $cashier }}</span></div>
            <div class="meta-row"><span class="meta-label">Jenis</span><span class="separator">:</span><span class="meta-value">{{ $transactionTypes[$transaction->jenis_transaksi] ?? $transaction->jenis_transaksi }}</span></div>
            <div class="meta-row"><span class="meta-label">Pelanggan</span><span class="separator">:</span><span class="meta-value">{{ $transaction->customer_name ?: 'Umum' }}</span></div>
            @if ($transaction->customer_phone)
                <div class="meta-row"><span class="meta-label">No. Telepon</span><span class="separator">:</span><span class="meta-value">{{ $transaction->customer_phone }}</span></div>
            @endif
            @if ($isPrescription)
                <div class="meta-row"><span class="meta-label">No. Resep</span><span class="separator">:</span><span class="meta-value">{{ $transaction->nomor_resep ?: '-' }}</span></div>
                <div class="meta-row"><span class="meta-label">Tgl. Resep</span><span class="separator">:</span><span class="meta-value">{{ optional($transaction->tanggal_resep)->format('d/m/Y') ?: '-' }}</span></div>
                <div class="meta-row"><span class="meta-label">Dokter</span><span class="separator">:</span><span class="meta-value">{{ $transaction->dokter_name ?: '-' }}</span></div>
                @if ($transaction->asal_resep)
                    <div class="meta-row"><span class="meta-label">Asal Resep</span><span class="separator">:</span><span class="meta-value">{{ $transaction->asal_resep }}</span></div>
                @endif
            @endif
        </div>

        <hr class="divider">

        <section aria-label="Daftar produk">
            <div class="section-label">Rincian Produk</div>
            @foreach ($transaction->details as $detail)
                <article class="item">
                    <div class="item-primary">
                        <span class="item-number">{{ $loop->iteration }}.</span>
                        <span class="item-name">{{ $detail->nama_obat }}</span>
                        <span class="item-total">{{ $currency($detail->subtotal_gross) }}</span>
                    </div>
                    <div class="item-secondary">
                        {{ $number($detail->qty_jual) }} {{ $detail->satuan_jual }} &times; {{ $currency($detail->harga_jual) }}
                    </div>
                    @if ((float) $detail->diskon_nominal > 0)
                        <div class="item-discount">
                            <span>Diskon item</span>
                            <span>-{{ $currency($detail->diskon_nominal) }}</span>
                        </div>
                    @endif
                    @if ($isPrescription)
                        <div class="prescription-note">
                            @if ($isCompoundPrescription)
                                <div><strong>{{ $detail->racikan_group ?: 'R/ -' }}</strong> &middot; {{ $detail->dosis_komponen ?: 'Dosis belum dicatat' }}</div>
                            @endif
                            <div>
                                <strong>Aturan pakai:</strong> {{ $detail->aturan_pakai ?: '-' }}
                                @if ($detail->waktu_konsumsi || $detail->durasi_hari)
                                    ({{ $consumptionLabels[$detail->waktu_konsumsi] ?? $detail->waktu_konsumsi }}{{ $detail->waktu_konsumsi && $detail->durasi_hari ? ', ' : '' }}{{ $detail->durasi_hari ? $detail->durasi_hari . ' hari' : '' }})
                                @endif
                            </div>
                            @if ($detail->keterangan)
                                <div>{{ $detail->keterangan }}</div>
                            @endif
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        <hr class="divider">

        <section aria-label="Ringkasan pembayaran">
            <div class="summary-row"><span>Subtotal</span><span>{{ $currency($transaction->subtotal_gross) }}</span></div>
            @if ((float) $transaction->diskon_item_total > 0)
                <div class="summary-row discount"><span>Diskon Item</span><span>-{{ $currency($transaction->diskon_item_total) }}</span></div>
            @endif
            @if ((float) $transaction->diskon_transaksi_nominal > 0)
                <div class="summary-row discount"><span>Diskon Transaksi</span><span>-{{ $currency($transaction->diskon_transaksi_nominal) }}</span></div>
            @endif
            @if ((float) $transaction->pajak_total > 0)
                <div class="summary-row"><span>Pajak</span><span>{{ $currency($transaction->pajak_total) }}</span></div>
            @endif
            @if ($isCompoundPrescription && (float) $transaction->embalase > 0)
                <div class="summary-row"><span>Embalase Racikan</span><span>{{ $currency($transaction->embalase) }}</span></div>
            @endif
            <div class="summary-row grand-total"><span>Total</span><span>{{ $currency($transaction->grand_total) }}</span></div>
            <div class="summary-row"><span>Bayar</span><span>{{ $currency($transaction->total_bayar) }}</span></div>
            <div class="summary-row">
                <span>{{ (float) $transaction->sisa_tagihan > 0 ? 'Sisa Tagihan' : 'Kembalian' }}</span>
                <span>{{ $currency((float) $transaction->sisa_tagihan > 0 ? $transaction->sisa_tagihan : $transaction->kembalian) }}</span>
            </div>
        </section>

        @if ($transaction->payments->isNotEmpty())
            <hr class="divider">
            <section aria-label="Metode pembayaran">
                <div class="section-label">Metode Pembayaran</div>
                @foreach ($transaction->payments as $payment)
                    <div class="payment-row">
                        <div class="payment-info">
                            <div class="payment-method">{{ $paymentMethods[$payment->metode] ?? $payment->metode }}</div>
                            @if ($payment->reference_no)
                                <div class="payment-reference">Ref: {{ $payment->reference_no }}</div>
                            @endif
                        </div>
                        <span class="payment-amount">{{ $currency($payment->amount) }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        @if ($transaction->catatan)
            <div class="transaction-note"><strong>Catatan:</strong> {{ $transaction->catatan }}</div>
        @endif

        <footer class="receipt-footer">
            <p class="thank-you">Terima kasih</p>
            <p class="footer-note">{{ $apotekProfile?->receipt_footer ?: 'Semoga lekas sehat. Simpan struk ini sebagai bukti transaksi.' }}</p>
            @if ($apotekProfile?->pharmacist_name || $apotekProfile?->pharmacist_license_number || $apotekProfile?->pharmacy_license_number || $apotekProfile?->tax_id)
                <p class="pharmacy-legal">
                    @if ($apotekProfile?->pharmacist_name)
                        Apoteker PJ: {{ $apotekProfile->pharmacist_name }}
                    @endif
                    @if ($apotekProfile?->pharmacist_license_number)
                        <br>SIPA: {{ $apotekProfile->pharmacist_license_number }}
                    @endif
                    @if ($apotekProfile?->pharmacy_license_number)
                        <br>SIA: {{ $apotekProfile->pharmacy_license_number }}
                    @endif
                    @if ($apotekProfile?->tax_id)
                        <br>NPWP: {{ $apotekProfile->tax_id }}
                    @endif
                </p>
            @endif
            <div class="footer-mark"></div>
        </footer>
    </main>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    window.focus();
                    window.print();
                }, {{ $embedded ? 450 : 100 }});
            });
        </script>
    @endif
</body>

</html>
