<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $isCompound = $type === "racikan";
    @endphp
    <title>Template Etiket {{ $isCompound ? "Racikan" : "Non Racikan" }}</title>
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
        html, body { min-height: 100%; margin: 0; }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 28px 12px;
            color: var(--ink);
            background: #eef1f5;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .template-toolbar {
            position: fixed;
            z-index: 10;
            top: 18px;
            right: 18px;
            display: flex;
            gap: 8px;
        }

        .template-toolbar button {
            padding: 10px 16px;
            border: 0;
            border-radius: 8px;
            color: #fff;
            background: #172033;
            box-shadow: 0 6px 16px rgba(16, 24, 40, .18);
            cursor: pointer;
            font: 700 13px Arial, sans-serif;
        }

        .label-sheet {
            position: relative;
            width: 80mm;
            overflow: hidden;
            border: 1px solid #b9c8cf;
            border-top: 1mm solid var(--teal);
            border-radius: 2.2mm;
            background: #fff;
            box-shadow: 0 9px 28px rgba(24, 43, 58, .14);
        }

        .template-ribbon {
            padding: 1.1mm 4mm;
            color: #55716a;
            background: #f0f8f6;
            font-size: 5pt;
            font-weight: 800;
            letter-spacing: .08em;
            text-align: center;
            text-transform: uppercase;
        }

        .label-header {
            display: grid;
            min-height: 12mm;
            grid-template-columns: 8mm minmax(0, 1fr) auto;
            align-items: center;
            gap: 1.7mm;
            padding: 1.7mm 4mm;
            border-bottom: .3mm solid #dce5e8;
        }

        .label-logo-shell {
            display: grid;
            width: 8mm;
            height: 8mm;
            overflow: hidden;
            place-items: center;
            border: .25mm solid #dce6e8;
            border-radius: 1.5mm;
            background: #f8fbfb;
        }

        .label-logo { display: block; width: 100%; height: 100%; padding: .45mm; object-fit: contain; }
        .pharmacy-copy { min-width: 0; }
        .pharmacy-copy strong { display: block; overflow: hidden; color: var(--navy); font-size: 8.5pt; line-height: 1.12; white-space: nowrap; text-overflow: ellipsis; }
        .pharmacy-copy span { display: block; overflow: hidden; margin-top: .45mm; color: var(--muted); font-size: 5.2pt; line-height: 1.2; white-space: nowrap; text-overflow: ellipsis; }
        .label-kind { max-width: 14mm; color: var(--teal); font-size: 4.8pt; font-weight: 800; letter-spacing: .07em; line-height: 1.2; text-align: right; }

        .label-body { padding: 2mm 4mm 2.7mm; }
        .field-row { display: grid; grid-template-columns: minmax(0, 1fr) 20mm; gap: 2mm; padding-bottom: 1.3mm; border-bottom: .25mm solid var(--line); }
        .field small { display: block; margin-bottom: .5mm; color: var(--muted); font-size: 5.1pt; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .field strong { display: block; min-height: 3.2mm; overflow: hidden; color: var(--navy); font-size: 8.7pt; line-height: 1.15; white-space: nowrap; }
        .field.is-right { text-align: right; }

        .medicine-line { display: grid; grid-template-columns: minmax(0, 1fr) 20mm; gap: 2mm; margin-top: 1.5mm; align-items: end; }
        .medicine-line .field strong { color: var(--teal); font-size: 7.2pt; }

        .directions {
            display: grid;
            min-height: 12mm;
            margin-top: 1.5mm;
            align-content: center;
            padding: 1.5mm 1.8mm;
            border-left: .8mm solid var(--teal);
            border-radius: .9mm;
            background: var(--teal-soft);
        }

        .directions small { color: #347061; font-size: 5.2pt; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
        .directions strong { display: block; margin-top: .7mm; color: #093f34; font-size: 9.5pt; line-height: 1.1; }
        .blank { color: #73858a !important; font-weight: 500 !important; letter-spacing: .04em; }

        .clinical-row { display: grid; grid-template-columns: 1.25fr .7fr 1fr; margin-top: 1.3mm; border-top: .25mm solid var(--line); border-bottom: .25mm solid var(--line); }
        .clinical-chip { min-width: 0; padding: 1mm; overflow: hidden; }
        .clinical-chip + .clinical-chip { border-left: .25mm solid var(--line); }
        .clinical-chip:first-child { padding-left: 0; }
        .clinical-chip:last-child { padding-right: 0; }
        .clinical-chip small { display: block; color: #74838c; font-size: 4.6pt; font-weight: 800; letter-spacing: .035em; text-transform: uppercase; }
        .clinical-chip strong { display: block; overflow: hidden; margin-top: .4mm; color: #263d4a; font-size: 5.8pt; line-height: 1.15; white-space: nowrap; text-overflow: ellipsis; }

        .label-footer { display: grid; margin-top: 1.1mm; gap: .45mm; color: #687985; font-size: 4.8pt; }
        .label-note { color: #9b4f24; font-weight: 800; }
        .document-ref { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }

        @media print {
            html, body { width: 80mm; background: #fff; }
            body { display: block; min-height: 0; padding: 0; }
            .template-toolbar { display: none; }
            .label-sheet { width: 80mm; border-right: 0; border-bottom: 0; border-left: 0; border-radius: 0; box-shadow: none; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @php
        $profile = $branch->apotekProfile;
        $pharmacyName = $profile?->name ?: $branch->name;
        $pharmacyLogo = $profile?->logo_url ?: asset("assets/apotek/LogoResmi.png");
        $pharmacyPhone = $profile?->phone ?: $branch->phone;
    @endphp

    <div class="template-toolbar">
        <button type="button" onclick="window.print()">Cetak Template</button>
    </div>

    <article class="label-sheet" aria-label="Template etiket {{ $isCompound ? "racikan" : "non racikan" }}">
        <div class="template-ribbon">Template Etiket Kosong &middot; Thermal 80 mm</div>
        <header class="label-header">
            <span class="label-logo-shell">
                <img class="label-logo" src="{{ $pharmacyLogo }}" alt="Logo {{ $pharmacyName }}">
            </span>
            <span class="pharmacy-copy">
                <strong>{{ $pharmacyName }}</strong>
                <span>{{ $pharmacyPhone ?: "Layanan farmasi terpercaya" }}</span>
            </span>
            <span class="label-kind">{{ $isCompound ? "OBAT RACIKAN" : "OBAT RESEP" }}</span>
        </header>

        <div class="label-body">
            <div class="field-row">
                <span class="field">
                    <small>Nama pasien</small>
                    <strong class="blank">....................................</strong>
                </span>
                <span class="field is-right">
                    <small>No. resep</small>
                    <strong class="blank">................</strong>
                </span>
            </div>

            <div class="medicine-line">
                <span class="field">
                    <small>{{ $isCompound ? "Nama / bentuk racikan" : "Nama obat" }}</small>
                    <strong class="blank">....................................</strong>
                </span>
                <span class="field is-right">
                    <small>Jumlah</small>
                    <strong class="blank">..............</strong>
                </span>
            </div>

            <div class="directions">
                <small>Aturan pakai</small>
                <strong class="blank">........................................................</strong>
            </div>

            <div class="clinical-row">
                <span class="clinical-chip">
                    <small>Waktu konsumsi</small>
                    <strong class="blank">....................</strong>
                </span>
                <span class="clinical-chip">
                    <small>Durasi</small>
                    <strong class="blank">..........</strong>
                </span>
                <span class="clinical-chip">
                    <small>Dokter</small>
                    <strong class="blank">................</strong>
                </span>
            </div>

            <footer class="label-footer">
                <span class="label-note">Gunakan sesuai petunjuk. Jauhkan dari jangkauan anak.</span>
                <span class="document-ref">Tanggal: ............ &middot; No. transaksi: ........................</span>
            </footer>
        </div>
    </article>

    @if ($autoPrint)
        <script>
            window.addEventListener('load', function () {
                window.setTimeout(function () {
                    window.focus();
                    window.print();
                }, 100);
            });
        </script>
    @endif
</body>
</html>
