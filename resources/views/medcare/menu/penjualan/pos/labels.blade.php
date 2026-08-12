<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $transaction->jenis_transaksi === 'penjualan_racikan' ? 'Etiket Racikan' : 'Etiket Non Racikan' }} {{ $transaction->nomor_transaksi }}</title>
    <style>
        :root {
            color-scheme: light;
            --navy: #17324d;
            --teal: #0b8067;
            --teal-soft: #e9f6f2;
            --ink: #182632;
            --muted: #63727e;
            --line: #cbd8df;
        }

        * { box-sizing: border-box; }

        html, body { margin: 0; min-height: 100%; }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            padding: 20px 0;
            color: var(--ink);
            background: #eef1f5;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .print-button {
            position: fixed;
            z-index: 10;
            top: 18px;
            right: 18px;
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            background: #172033;
            color: #fff;
            font: 700 13px Arial, sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(16, 24, 40, .18);
        }

        .label-sheet {
            position: relative;
            width: 80mm;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid #b9c8cf;
            border-top: 1mm solid var(--teal);
            border-radius: 2.2mm;
            background: #fff;
            box-shadow: 0 9px 28px rgba(24, 43, 58, .14);
        }

        .label-sheet + .label-sheet { margin-top: 20px; }

        .label-header {
            display: grid;
            min-height: 11.5mm;
            grid-template-columns: 7.4mm minmax(0, 1fr) auto;
            align-items: center;
            gap: 1.6mm;
            padding: 1.6mm 4mm;
            border-bottom: .3mm solid #dce5e8;
            background: #fff;
        }

        .label-logo-shell {
            display: grid;
            width: 7.4mm;
            height: 7.4mm;
            place-items: center;
            overflow: hidden;
            border: .25mm solid #dce6e8;
            border-radius: 1.5mm;
            background: #f8fbfb;
        }

        .label-logo { display: block; width: 100%; height: 100%; padding: .45mm; object-fit: contain; }
        .pharmacy-copy { min-width: 0; }
        .pharmacy-copy strong { display: block; overflow: hidden; color: var(--navy); font-size: 7.8pt; line-height: 1.12; white-space: nowrap; text-overflow: ellipsis; }
        .pharmacy-copy span { display: block; overflow: hidden; margin-top: .45mm; color: var(--muted); font-size: 4.6pt; line-height: 1.2; white-space: nowrap; text-overflow: ellipsis; }
        .label-kind { max-width: 12mm; color: var(--teal); font-size: 4.4pt; font-weight: 800; letter-spacing: .08em; line-height: 1.2; text-align: right; }

        .label-body { padding: 1.7mm 4mm 2.5mm; }

        .patient-line {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 13.5mm;
            gap: 1.2mm;
            align-items: end;
            padding-bottom: 1.1mm;
            border-bottom: .25mm solid var(--line);
        }

        .patient-line small, .rx-identity small { display: block; margin-bottom: .35mm; color: var(--muted); font-size: 4.7pt; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
        .patient-line strong { display: block; overflow: hidden; color: var(--navy); font-size: 8.7pt; line-height: 1.08; white-space: nowrap; text-overflow: ellipsis; }
        .rx-identity { min-width: 0; text-align: right; }
        .rx-identity strong { display: block; overflow: hidden; color: #344e5f; font-size: 5.7pt; line-height: 1.1; white-space: nowrap; text-overflow: ellipsis; }

        .compound-line { display: flex; margin-top: 1mm; align-items: center; justify-content: space-between; gap: 1.5mm; }
        .compound-line strong { color: var(--teal); font-size: 6.1pt; }
        .compound-line span { color: #50616d; font-size: 5.5pt; font-weight: 700; }

        .directions {
            display: grid;
            min-height: 10.2mm;
            margin-top: 1.1mm;
            align-content: center;
            padding: 1.3mm 1.7mm;
            border-left: .8mm solid var(--teal);
            border-radius: .9mm;
            background: var(--teal-soft);
        }

        .directions small { color: #347061; font-size: 4.7pt; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
        .directions strong { display: block; margin-top: .45mm; color: #093f34; font-size: 8.8pt; line-height: 1.1; }

        .clinical-row { display: grid; grid-template-columns: 1.25fr .7fr 1fr; margin-top: 1.1mm; border-top: .25mm solid var(--line); border-bottom: .25mm solid var(--line); }
        .clinical-chip { min-width: 0; padding: .9mm 1mm .85mm; overflow: hidden; }
        .clinical-chip + .clinical-chip { border-left: .25mm solid var(--line); }
        .clinical-chip:first-child { padding-left: 0; }
        .clinical-chip:last-child { padding-right: 0; }
        .clinical-chip small { display: block; color: #74838c; font-size: 4.2pt; font-weight: 800; letter-spacing: .035em; text-transform: uppercase; }
        .clinical-chip strong { display: block; overflow: hidden; margin-top: .25mm; color: #263d4a; font-size: 5.4pt; line-height: 1.1; white-space: nowrap; text-overflow: ellipsis; }

        .label-footer { display: grid; margin-top: .9mm; gap: .35mm; color: #687985; font-size: 4.2pt; }
        .label-note { overflow: hidden; color: #9b4f24; font-weight: 800; white-space: nowrap; text-overflow: ellipsis; }
        .document-ref { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }

        body.is-embedded {
            min-height: 100vh;
            padding: 14px 0 24px;
            background: #f5f6f8;
        }

        body.is-embedded .label-sheet { box-shadow: 0 6px 24px rgba(16, 24, 40, .1); }

        @media print {
            html,
            body {
                width: 80mm;
                background: #fff;
            }

            body {
                min-height: 0;
                padding: 0;
            }

            .print-button { display: none; }

            .label-sheet {
                width: 80mm;
                margin: 0;
                border-right: 0;
                border-bottom: 0;
                border-left: 0;
                border-radius: 0;
                box-shadow: none;
            }

            .label-sheet + .label-sheet {
                margin-top: 0;
                break-before: page;
                page-break-before: always;
            }

            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="{{ $embedded ? 'is-embedded' : '' }}">
    @php
        $number = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
        $isCompoundPrescription = $transaction->jenis_transaksi === 'penjualan_racikan';
        $labelItems = $isCompoundPrescription
            ? $transaction->details
                ->groupBy(fn ($detail) => trim((string) $detail->racikan_group) ?: 'R/ -')
                ->sortKeysUsing(fn ($left, $right) => strnatcasecmp((string) $left, (string) $right))
            : $transaction->details->values();
        $pharmacyName = $apotekProfile?->name ?: ($transaction->branch?->name ?: 'Medcare Pharmacy');
        $pharmacyLogo = $apotekProfile?->logo_url ?: asset('assets/apotek/LogoResmi.png');
        $pharmacyPhone = $apotekProfile?->phone ?: $transaction->branch?->phone;
        $consumptionLabels = [
            'sebelum_makan' => 'Sebelum makan',
            'sesudah_makan' => 'Sesudah makan',
            'bersama_makan' => 'Bersama makan',
            'tidak_terkait_makan' => 'Tidak terkait makan',
            'sesuai_instruksi' => 'Sesuai instruksi dokter',
        ];
    @endphp

    @unless ($embedded)
        <button type="button" class="print-button" onclick="window.print()">Cetak Etiket</button>
    @endunless

    @foreach ($labelItems as $itemKey => $item)
        @php
            $reference = $isCompoundPrescription ? $item->first() : $item;
            $groupCode = $isCompoundPrescription ? $itemKey : null;
            $groupNumber = $isCompoundPrescription && preg_match('/(\d+)/', (string) $groupCode, $matches)
                ? $matches[1]
                : $loop->iteration;
            $form = $isCompoundPrescription
                ? ($reference->bentuk_racikan ?: 'Racikan')
                : ($reference->satuan_jual ?: 'obat');
            $takeQuantity = $isCompoundPrescription
                ? $number($reference->jumlah_ambil_resep ?: $reference->jumlah_racikan)
                : $number($reference->qty_jual);
            $directions = $isCompoundPrescription
                ? ($reference->aturan_pakai ?: trim(($reference->signa_1 ?: '').' × sehari '.($reference->signa_2 ?: '').' '.strtolower($form)))
                : $reference->aturan_pakai;
            $isLiquid = preg_match(
                '/sirup|suspensi|emulsi|cair|larutan/i',
                (string) ($isCompoundPrescription ? $form : $reference->nama_obat)
            );
            $note = trim(collect([$isLiquid ? 'Kocok dahulu' : null, $reference->keterangan])->filter()->implode(' · '));
            $labelTitle = $isCompoundPrescription
                ? 'Racikan '.$groupNumber.' · '.$form
                : ($reference->nama_obat ?: 'Obat resep');
            $labelQuantity = $takeQuantity.' '.strtolower($form);
        @endphp

        <article class="label-sheet" aria-label="{{ $isCompoundPrescription ? 'Etiket racikan '.$groupNumber : 'Etiket '.$labelTitle }} untuk {{ $transaction->customer_name }}">
            <header class="label-header">
                <span class="label-logo-shell">
                    <img class="label-logo" src="{{ $pharmacyLogo }}" alt="Logo branch {{ $pharmacyName }}">
                </span>
                <span class="pharmacy-copy">
                    <strong>{{ $pharmacyName }}</strong>
                    <span>{{ $pharmacyPhone ?: 'Layanan farmasi terpercaya' }}</span>
                </span>
                <span class="label-kind">{{ $isCompoundPrescription ? 'OBAT RACIKAN' : 'OBAT RESEP' }}</span>
            </header>

            <div class="label-body">
                <div class="patient-line">
                    <span>
                        <small>Nama pasien</small>
                        <strong>{{ $transaction->customer_name ?: 'Umum' }}</strong>
                    </span>
                    <span class="rx-identity">
                        <small>No. resep</small>
                        <strong>{{ $transaction->nomor_resep ?: '-' }}</strong>
                    </span>
                </div>

                <div class="compound-line">
                    <strong>{{ $labelTitle }}</strong>
                    <span>{{ $labelQuantity }}</span>
                </div>

                <div class="directions">
                    <small>Aturan pakai</small>
                    <strong>{{ $directions ?: '-' }}</strong>
                </div>

                <div class="clinical-row">
                    <span class="clinical-chip">
                        <small>Waktu konsumsi</small>
                        <strong>{{ $consumptionLabels[$reference->waktu_konsumsi] ?? ($reference->waktu_konsumsi ?: 'Sesuai petunjuk') }}</strong>
                    </span>
                    <span class="clinical-chip">
                        <small>Durasi</small>
                        <strong>{{ $reference->durasi_hari ? $reference->durasi_hari.' hari' : '-' }}</strong>
                    </span>
                    <span class="clinical-chip">
                        <small>Dokter</small>
                        <strong>{{ $transaction->dokter_name ?: '-' }}</strong>
                    </span>
                </div>

                <footer class="label-footer">
                    <span class="label-note">{{ $note ?: 'Gunakan sesuai petunjuk. Jauhkan dari jangkauan anak.' }}</span>
                    <span class="document-ref">{{ optional($transaction->tanggal_transaksi)->format('d/m/Y') }} · {{ $transaction->nomor_transaksi }} · {{ $loop->iteration }}/{{ $labelItems->count() }}</span>
                </footer>
            </div>
        </article>
    @endforeach

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
