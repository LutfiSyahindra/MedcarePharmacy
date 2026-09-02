<?php

namespace App\Exports\Menu\AnalisisOmzet;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RevenueAnalysisExport implements WithMultipleSheets
{
    public function __construct(private readonly array $analysis) {}

    public function sheets(): array
    {
        $a = $this->analysis;
        $summary = $a['summary'];
        $target = $a['target'];

        return [
            new RevenueAnalysisSheet('Ringkasan', ['Indikator', 'Nilai'], [
                ['Periode', $a['meta']['period_label']],
                ['Cabang', $a['meta']['branch_label']],
                ['Total omzet', $summary['total_revenue']],
                ['Omzet bersih', $summary['net_revenue']],
                ['Total transaksi', $summary['transactions']],
                ['Total qty terjual', $summary['qty_sold']],
                ['Rata-rata nilai transaksi', $summary['average_transaction']],
                ['Total diskon', $summary['total_discount']],
                ['Total retur', $summary['return_value']],
                ['Pertumbuhan (%)', $summary['growth_percent']],
                ['Target omzet', $target['amount']],
                ['Pencapaian target (%)', $target['achievement_percent']],
                ['Status target', $target['status']],
            ]),
            new RevenueAnalysisSheet('Tren', ['Periode', 'Omzet Bersih', 'Periode Sebelumnya', 'Omzet Sebelumnya', 'Transaksi', 'Retur'],
                collect($a['trend']['labels'])->map(fn ($label, $index) => [
                    $label,
                    $a['trend']['current_revenue'][$index] ?? 0,
                    $a['trend']['previous_labels'][$index] ?? '-',
                    $a['trend']['previous_revenue'][$index] ?? 0,
                    $a['trend']['transactions'][$index] ?? 0,
                    $a['trend']['returns'][$index] ?? 0,
                ])->all()),
            new RevenueAnalysisSheet('Produk', ['Kode', 'Nama Obat', 'Kategori', 'Qty Bersih', 'Qty Retur', 'Transaksi', 'Omzet Bersih', 'Kontribusi (%)', 'Growth (%)'],
                collect($a['products'])->map(fn ($row) => [
                    $row['code'], $row['name'], $row['category'], $row['net_qty'], $row['return_qty'], $row['transactions'],
                    $row['revenue'], $row['contribution_percent'], $row['growth_percent'],
                ])->all()),
            new RevenueAnalysisSheet('Fast Moving', ['Rank', 'Kode', 'Nama Obat', 'Kategori', 'Qty Bersih', 'Transaksi', 'Hari Aktif', 'Rata-rata per Hari', 'Penetrasi (%)', 'Growth Qty (%)'],
                collect($a['fast_moving']['rows'])->map(fn ($row) => [
                    $row['rank'], $row['code'], $row['name'], $row['category'], $row['net_qty'], $row['transactions'],
                    $row['sales_days'], $row['average_daily_qty'], $row['transaction_penetration_percent'], $row['qty_growth_percent'],
                ])->all()),
            new RevenueAnalysisSheet('Market Basket', ['Produk A', 'Produk B', 'Transaksi Bersama', 'Support (%)', 'Confidence A ke B (%)', 'Confidence B ke A (%)', 'Lift', 'Kekuatan'],
                collect($a['market_basket']['rows'])->map(fn ($row) => [
                    $row['product_a']['name'], $row['product_b']['name'], $row['pair_transactions'], $row['support_percent'],
                    $row['confidence_a_to_b_percent'], $row['confidence_b_to_a_percent'], $row['lift'], $row['strength'],
                ])->all()),
            new RevenueAnalysisSheet('Kategori', ['Kategori', 'Qty Terjual', 'Qty Retur', 'Omzet Bersih', 'Kontribusi (%)'],
                collect($a['categories'])->map(fn ($row) => [
                    $row['label'], $row['qty'], $row['return_qty'], $row['revenue'], $row['contribution_percent'],
                ])->all()),
            new RevenueAnalysisSheet('Jenis Penjualan', ['Jenis Penjualan', 'Transaksi', 'Omzet Bersih', 'Kontribusi (%)'],
                collect($a['sale_types'])->map(fn ($row) => [$row['label'], $row['transactions'], $row['revenue'], $row['contribution_percent']])->all()),
            new RevenueAnalysisSheet('Metode Pembayaran', ['Metode Pembayaran', 'Transaksi', 'Omzet Bersih', 'Kontribusi (%)'],
                collect($a['payments'])->map(fn ($row) => [$row['label'], $row['transactions'], $row['revenue'], $row['contribution_percent']])->all()),
            new RevenueAnalysisSheet('Per Jam', ['Jam', 'Transaksi', 'Omzet Bersih'],
                collect($a['hourly']['rows'])->map(fn ($row) => [$row['label'], $row['transactions'], $row['revenue']])->all()),
            new RevenueAnalysisSheet('Per Hari', ['Hari', 'Transaksi', 'Omzet Bersih', 'Rata-rata Omzet'],
                collect($a['weekdays']['rows'])->map(fn ($row) => [$row['label'], $row['transactions'], $row['revenue'], $row['average_revenue']])->all()),
            new RevenueAnalysisSheet('Kasir', ['Nama Kasir', 'Transaksi', 'Qty Terjual', 'Omzet Bersih', 'Rata-rata Transaksi', 'Kontribusi (%)'],
                collect($a['cashiers'])->map(fn ($row) => [
                    $row['name'], $row['transactions'], $row['qty'], $row['revenue'], $row['average_transaction'], $row['contribution_percent'],
                ])->all()),
            new RevenueAnalysisSheet('Insight', ['Insight', 'Keterangan'],
                collect($a['insights'])->map(fn ($row) => [$row['title'], $row['text']])->all()),
        ];
    }
}
