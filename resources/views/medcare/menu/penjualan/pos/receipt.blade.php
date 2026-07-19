<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->nomor_transaksi }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            color: #111827;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .receipt {
            width: 80mm;
            min-height: 100vh;
            margin: 0 auto;
            background: #fff;
            padding: 14px;
        }

        .center {
            text-align: center;
        }

        h1 {
            margin: 0;
            font-size: 18px;
            letter-spacing: .5px;
        }

        .muted {
            color: #6b7280;
        }

        .line {
            border-top: 1px dashed #9ca3af;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 3px 0;
        }

        .item {
            margin-bottom: 8px;
        }

        .item strong {
            display: block;
        }

        .total {
            font-size: 15px;
            font-weight: 700;
        }

        @media print {
            body {
                background: #fff;
            }

            .receipt {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    @php
        $currency = fn($value) => "Rp " . number_format((float) $value, 0, ",", ".");
        $number = fn($value) => number_format((float) $value, 2, ",", ".");
        $isPrescription = in_array($transaction->jenis_transaksi, ["penjualan_resep", "penjualan_racikan"], true);
        $isCompoundPrescription = $transaction->jenis_transaksi === "penjualan_racikan";
        $consumptionLabels = [
            "sebelum_makan" => "sebelum makan",
            "sesudah_makan" => "sesudah makan",
            "bersama_makan" => "bersama makan",
            "tidak_terkait_makan" => "tidak terkait makan",
            "sesuai_instruksi" => "sesuai instruksi dokter",
        ];
    @endphp
    <main class="receipt">
        <div class="center">
            <h1>Medcare Pharmacy</h1>
            <div>{{ $transaction->branch->name ?? "Branch" }}</div>
            <div class="muted">{{ $transaction->branch->address ?? "" }}</div>
        </div>

        <div class="line"></div>

        <div class="row"><span>No</span><strong>{{ $transaction->nomor_transaksi }}</strong></div>
        <div class="row"><span>Tanggal</span><span>{{ optional($transaction->tanggal_transaksi)->format("d-m-Y H:i") }}</span></div>
        <div class="row"><span>Kasir</span><span>{{ $transaction->createdBy->name ?? "-" }}</span></div>
        <div class="row"><span>Jenis</span><span>{{ $transactionTypes[$transaction->jenis_transaksi] ?? $transaction->jenis_transaksi }}</span></div>
        <div class="row"><span>Pelanggan</span><span>{{ $transaction->customer_name ?: "Umum" }}</span></div>
        @if ($isPrescription)
            <div class="row"><span>No. Resep</span><strong>{{ $transaction->nomor_resep ?: "-" }}</strong></div>
            <div class="row"><span>Tgl. Resep</span><span>{{ optional($transaction->tanggal_resep)->format("d-m-Y") ?: "-" }}</span></div>
            <div class="row"><span>Dokter</span><span>{{ $transaction->dokter_name ?: "-" }}</span></div>
            @if ($transaction->asal_resep)
                <div class="row"><span>Asal Resep</span><span>{{ $transaction->asal_resep }}</span></div>
            @endif
        @endif

        <div class="line"></div>

        @foreach ($transaction->details as $detail)
            <div class="item">
                <strong>{{ $detail->nama_obat }}</strong>
                <div class="row">
                    <span>{{ $number($detail->qty_jual) }} {{ $detail->satuan_jual }} x {{ $currency($detail->harga_jual) }}</span>
                    <span>{{ $currency($detail->subtotal_gross) }}</span>
                </div>
                @if ((float) $detail->diskon_nominal > 0)
                    <div class="row muted">
                        <span>Diskon item</span>
                        <span>-{{ $currency($detail->diskon_nominal) }}</span>
                    </div>
                @endif
                @if ($isPrescription)
                    <div class="muted">
                        @if ($isCompoundPrescription)
                            <strong>{{ $detail->racikan_group ?: "R/ -" }} · {{ $detail->dosis_komponen ?: "Dosis belum dicatat" }}</strong>
                        @endif
                        <span>S: {{ $detail->aturan_pakai ?: "-" }}</span>
                        @if ($detail->waktu_konsumsi || $detail->durasi_hari)
                            <span>({{ $consumptionLabels[$detail->waktu_konsumsi] ?? $detail->waktu_konsumsi }}{{ $detail->waktu_konsumsi && $detail->durasi_hari ? ", " : "" }}{{ $detail->durasi_hari ? $detail->durasi_hari . " hari" : "" }})</span>
                        @endif
                        @if ($detail->keterangan)
                            <span> · {{ $detail->keterangan }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        <div class="line"></div>

        <div class="row"><span>Subtotal</span><span>{{ $currency($transaction->subtotal_gross) }}</span></div>
        <div class="row"><span>Diskon item</span><span>-{{ $currency($transaction->diskon_item_total) }}</span></div>
        <div class="row"><span>Diskon transaksi</span><span>-{{ $currency($transaction->diskon_transaksi_nominal) }}</span></div>
        <div class="row"><span>Pajak</span><span>{{ $currency($transaction->pajak_total) }}</span></div>
        @if ($isCompoundPrescription)
            <div class="row"><span>Embalase racikan</span><span>{{ $currency($transaction->embalase) }}</span></div>
        @endif
        <div class="row total"><span>Total</span><span>{{ $currency($transaction->grand_total) }}</span></div>
        <div class="row"><span>Bayar</span><span>{{ $currency($transaction->total_bayar) }}</span></div>
        <div class="row"><span>{{ (float) $transaction->sisa_tagihan > 0 ? "Sisa Tagihan" : "Kembalian" }}</span><span>{{ $currency((float) $transaction->sisa_tagihan > 0 ? $transaction->sisa_tagihan : $transaction->kembalian) }}</span></div>

        @if ($transaction->payments->isNotEmpty())
            <div class="line"></div>
            @foreach ($transaction->payments as $payment)
                <div class="row">
                    <span>{{ $paymentMethods[$payment->metode] ?? $payment->metode }}</span>
                    <span>{{ $currency($payment->amount) }}</span>
                </div>
            @endforeach
        @endif

        <div class="line"></div>
        <div class="center muted">
            Terima kasih. Semoga lekas sehat.
        </div>
    </main>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
