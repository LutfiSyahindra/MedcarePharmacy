<?php

namespace App\Exports\MasterData\golongan;

use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubGolonganObatExport implements WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function headings(): array
    {
        return [
            'Kode Main Golongan',
            'Kode Sub Golongan',
            'Nama Sub Golongan',
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
                    'vertical' => 'center',
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
            'A' => 22,
            'B' => 22,
            'C' => 40,
            'D' => 40,
        ];
    }

    public function title(): string
    {
        return 'Template Sub Golongan Obat';
    }
}
