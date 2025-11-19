<?php

namespace App\Exports\MasterData\Rak;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RakObatExport implements WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function headings(): array
    {
        return [
            'Kode', // kolom pertama sesuai field di database
            'Nama', // kolom kedua sesuai field di database
            'Lokasi',
            'Keterangan',
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
            'A' => 20, // kolom "Kode"
            'B' => 40, // kolom "Nama"
            'C' => 40, // kolom "Alamat"
            'D' => 20, // kolom "Telepon"
        ];
    }

    public function title(): string
    {
        return 'Template Rak Penyimpanan Obat';
    }
}
