<?php

namespace App\Exports\MasterData\MasterObat;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterObatExport implements WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function headings(): array
    {
        return [
            'kode_obat',
            'nama_obat',
            'category_id',
            'main_category_id',
            'sub_kategori_id',
            'golongan_id',
            'satuan_id',
            'sediaan_id',
            'pabrikan_id',
            'distributor_id',
            'rak_id',
            'komposisi',
            'indikasi',
            'dosis',
            'kemasan',
            'stok_minimum',
            'harga_beli',
            'is_generik',
            'is_active',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // kode_obat
            'B' => 30, // nama_obat
            'C' => 15, // kategori_id
            'D' => 15, // sub_kategori_id
            'E' => 15, // golongan_id
            'F' => 15, // satuan_id
            'G' => 15, // sediaan_id
            'H' => 15, // pabrikan_id
            'I' => 15, // distributor_id
            'J' => 15, // rak_id
            'K' => 30, // komposisi
            'L' => 40, // indikasi
            'M' => 30, // dosis
            'N' => 25, // kemasan
            'O' => 25, // kemasan
            'P' => 10, // stok_minimum
            'Q' => 15, // harga_beli
            'R' => 12, // is_generik
            'S' => 12, // is_active
        ];
    }

    public function title(): string
    {
        return 'Template Master Obat';
    }
}
