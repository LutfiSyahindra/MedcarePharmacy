<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Analisis Penjualan</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #17324d; font-size: 9px; }
        h1 { margin: 0 0 4px; color: #0f766e; font-size: 21px; }
        h2 { margin: 18px 0 7px; color: #17324d; font-size: 12px; }
        p { margin: 0; color: #60758a; }
        .header { border-bottom: 2px solid #14b8a6; padding-bottom: 12px; }
        .meta { margin-top: 4px; }
        .kpis { width: 100%; border-collapse: separate; border-spacing: 6px; margin: 10px -6px 0; }
        .kpis td { width: 25%; padding: 9px; border: 1px solid #dce9e7; border-radius: 6px; background: #f5fbfa; }
        .kpis small { display: block; color: #60758a; margin-bottom: 3px; }
        .kpis strong { color: #0f766e; font-size: 13px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0f766e; color: white; padding: 6px; text-align: left; }
        table.data td { padding: 5px 6px; border-bottom: 1px solid #e5edf1; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .right { text-align: right; }
        .grid { width: 100%; }
        .grid td { vertical-align: top; width: 50%; padding-right: 10px; }
        .badge { display: inline-block; padding: 3px 7px; border-radius: 8px; background: #dff7f2; color: #0f766e; }
        .page-break { page-break-before: always; }
        .footer { margin-top: 15px; color: #8496a7; font-size: 8px; }
    </style>
</head>
<body>
    @php
        $s = $analysis['summary'];
        $money = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
        $number = fn ($value) => number_format((float) $value, 2, ',', '.');
    @endphp
    <div class="header">
        <h1>Analisis Penjualan</h1>
        <p class="meta">{{ $analysis['meta']['branch_label'] }} · {{ $analysis['meta']['period_label'] }}</p>
        <p>Dibuat {{ $analysis['meta']['generated_at'] }} · Transaksi completed dikurangi retur posted.</p>
    </div>

    <table class="kpis">
        <tr>
            <td><small>Total omzet</small><strong>{{ $money($s['total_revenue']) }}</strong></td>
            <td><small>Omzet bersih</small><strong>{{ $money($s['net_revenue']) }}</strong></td>
            <td><small>Total transaksi</small><strong>{{ number_format($s['transactions'], 0, ',', '.') }}</strong></td>
            <td><small>Qty terjual</small><strong>{{ $number($s['qty_sold']) }}</strong></td>
        </tr>
        <tr>
            <td><small>Rata-rata transaksi</small><strong>{{ $money($s['average_transaction']) }}</strong></td>
            <td><small>Total diskon</small><strong>{{ $money($s['total_discount']) }}</strong></td>
            <td><small>Total retur</small><strong>{{ $money($s['return_value']) }}</strong></td>
            <td><small>Pertumbuhan</small><strong>{{ $number($s['growth_percent']) }}%</strong></td>
        </tr>
    </table>

    <h2>Tren Omzet</h2>
    <table class="data">
        <thead><tr><th>Periode</th><th class="right">Omzet Bersih</th><th class="right">Periode Sebelumnya</th><th class="right">Transaksi</th><th class="right">Retur</th></tr></thead>
        <tbody>
            @foreach ($analysis['trend']['labels'] as $index => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="right">{{ $money($analysis['trend']['current_revenue'][$index] ?? 0) }}</td>
                    <td class="right">{{ $money($analysis['trend']['previous_revenue'][$index] ?? 0) }}</td>
                    <td class="right">{{ number_format($analysis['trend']['transactions'][$index] ?? 0, 0, ',', '.') }}</td>
                    <td class="right">{{ $money($analysis['trend']['returns'][$index] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Produk Terlaris</h2>
    <table class="data">
        <thead><tr><th>#</th><th>Obat</th><th>Kategori</th><th class="right">Qty</th><th class="right">Transaksi</th><th class="right">Omzet</th><th class="right">Kontribusi</th><th class="right">Growth</th></tr></thead>
        <tbody>
            @forelse ($analysis['products'] as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td><td>{{ $row['name'] }}<br><small>{{ $row['code'] }}</small></td><td>{{ $row['category'] }}</td>
                    <td class="right">{{ $number($row['net_qty']) }}</td><td class="right">{{ $row['transactions'] }}</td>
                    <td class="right">{{ $money($row['revenue']) }}</td><td class="right">{{ $number($row['contribution_percent']) }}%</td>
                    <td class="right">{{ $number($row['growth_percent']) }}%</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data produk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Fast Moving</h2>
    <table class="data">
        <thead><tr><th>#</th><th>Obat</th><th class="right">Qty Bersih</th><th class="right">Transaksi</th><th class="right">Hari Aktif</th><th class="right">Rata-rata / Hari</th><th class="right">Penetrasi</th><th class="right">Growth Qty</th></tr></thead>
        <tbody>
            @forelse ($analysis['fast_moving']['rows'] as $row)
                <tr>
                    <td>{{ $row['rank'] }}</td><td>{{ $row['name'] }}<br><small>{{ $row['code'] }}</small></td>
                    <td class="right">{{ $number($row['net_qty']) }}</td><td class="right">{{ $row['transactions'] }}</td>
                    <td class="right">{{ $row['sales_days'] }}</td><td class="right">{{ $number($row['average_daily_qty']) }}</td>
                    <td class="right">{{ $number($row['transaction_penetration_percent']) }}%</td><td class="right">{{ $number($row['qty_growth_percent']) }}%</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data fast moving.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <h2>Market Basket</h2>
    <table class="data">
        <thead><tr><th>Produk A</th><th>Produk B</th><th class="right">Bersama</th><th class="right">Support</th><th class="right">Confidence A → B</th><th class="right">Confidence B → A</th><th class="right">Lift</th><th>Kekuatan</th></tr></thead>
        <tbody>
            @forelse ($analysis['market_basket']['rows'] as $row)
                <tr>
                    <td>{{ $row['product_a']['name'] }}</td><td>{{ $row['product_b']['name'] }}</td>
                    <td class="right">{{ $row['pair_transactions'] }}</td><td class="right">{{ $number($row['support_percent']) }}%</td>
                    <td class="right">{{ $number($row['confidence_a_to_b_percent']) }}%</td><td class="right">{{ $number($row['confidence_b_to_a_percent']) }}%</td>
                    <td class="right">{{ $number($row['lift']) }}</td><td>{{ $row['strength'] }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada pasangan produk.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>
    <table class="grid"><tr><td>
        <h2>Omzet per Kategori</h2>
        <table class="data"><thead><tr><th>Kategori</th><th class="right">Qty</th><th class="right">Omzet</th><th class="right">%</th></tr></thead><tbody>
            @foreach ($analysis['categories'] as $row)<tr><td>{{ $row['label'] }}</td><td class="right">{{ $number($row['qty']) }}</td><td class="right">{{ $money($row['revenue']) }}</td><td class="right">{{ $number($row['contribution_percent']) }}%</td></tr>@endforeach
        </tbody></table>
    </td><td>
        <h2>Kontribusi per Kasir</h2>
        <table class="data"><thead><tr><th>Kasir</th><th class="right">Trx</th><th class="right">Qty</th><th class="right">Omzet</th><th class="right">%</th></tr></thead><tbody>
            @foreach ($analysis['cashiers'] as $row)<tr><td>{{ $row['name'] }}</td><td class="right">{{ $row['transactions'] }}</td><td class="right">{{ $number($row['qty']) }}</td><td class="right">{{ $money($row['revenue']) }}</td><td class="right">{{ $number($row['contribution_percent']) }}%</td></tr>@endforeach
        </tbody></table>
    </td></tr></table>

    <table class="grid"><tr><td>
        <h2>Jenis Penjualan</h2>
        <table class="data"><thead><tr><th>Jenis</th><th class="right">Trx</th><th class="right">Omzet</th><th class="right">%</th></tr></thead><tbody>
            @foreach ($analysis['sale_types'] as $row)<tr><td>{{ $row['label'] }}</td><td class="right">{{ $row['transactions'] }}</td><td class="right">{{ $money($row['revenue']) }}</td><td class="right">{{ $number($row['contribution_percent']) }}%</td></tr>@endforeach
        </tbody></table>
    </td><td>
        <h2>Metode Pembayaran</h2>
        <table class="data"><thead><tr><th>Metode</th><th class="right">Trx</th><th class="right">Omzet</th><th class="right">%</th></tr></thead><tbody>
            @foreach ($analysis['payments'] as $row)<tr><td>{{ $row['label'] }}</td><td class="right">{{ $row['transactions'] }}</td><td class="right">{{ $money($row['revenue']) }}</td><td class="right">{{ $number($row['contribution_percent']) }}%</td></tr>@endforeach
        </tbody></table>
    </td></tr></table>

    <h2>Target vs Realisasi</h2>
    <p><span class="badge">{{ $analysis['target']['status'] }}</span> Target {{ $money($analysis['target']['amount']) }} · Realisasi {{ $money($analysis['target']['realization']) }} · Pencapaian {{ $number($analysis['target']['achievement_percent']) }}% · Sisa {{ $money($analysis['target']['remaining']) }}</p>

    <h2>Insight Otomatis</h2>
    <table class="data"><tbody>
        @foreach ($analysis['insights'] as $insight)<tr><td><strong>{{ $insight['title'] }}</strong></td><td>{{ $insight['text'] }}</td></tr>@endforeach
    </tbody></table>
    <p class="footer">Dokumen ini mengikuti filter aktif dan tidak memasukkan transaksi draft, void, atau cancelled.</p>
</body>
</html>
