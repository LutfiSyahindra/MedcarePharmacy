<?php

namespace App\Services\Menu\Stok;

use App\Models\ApotekProfile;
use App\Models\Menu\Stok\StockOpnameModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class StockOpnameReportPdf
{
    public function render(StockOpnameModel $opname, array $summary): string
    {
        $generatedAt = now()->timezone('Asia/Jakarta');
        $html = view('medcare.menu.stok.stockOpname.reportPdf', [
            'opname' => $opname,
            'summary' => $summary,
            'generatedAt' => $generatedAt,
            'logoDataUri' => $this->logoDataUri($opname->branch?->apotekProfile),
            'statusLabels' => StockOpnameModel::statusLabels(),
            'actionLabels' => $this->actionLabels(),
            'documentFingerprint' => strtoupper(substr(hash('sha256', implode('|', [
                $opname->id,
                $opname->nomor,
                optional($opname->updated_at)->format('c'),
            ])), 0, 16)),
        ])->render();

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'chroot' => [public_path(), storage_path('app/public')],
        ]);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->add_info('Title', 'Laporan Lengkap Stock Opname '.$opname->nomor);
        $dompdf->add_info('Author', $opname->branch?->apotekProfile?->name ?: 'Medcare');
        $dompdf->add_info('Subject', 'Rekonsiliasi, penilaian selisih, mutasi, dan jejak audit stock opname');

        return $dompdf->output(['compress' => 1]);
    }

    private function logoDataUri(?ApotekProfile $profile): ?string
    {
        $paths = [];

        if ($profile?->logo_path) {
            $paths[] = storage_path('app/public/'.ltrim($profile->logo_path, '/\\'));
        }

        $paths[] = public_path('assets/apotek/LogoResmi.png');

        foreach ($paths as $path) {
            if (! is_file($path) || ! is_readable($path)) {
                continue;
            }

            $mime = function_exists('mime_content_type') ? mime_content_type($path) : null;

            if (! in_array($mime, ['image/png', 'image/jpeg'], true)) {
                continue;
            }

            $contents = file_get_contents($path);

            if ($contents !== false) {
                return 'data:'.$mime.';base64,'.base64_encode($contents);
            }
        }

        return null;
    }

    private function actionLabels(): array
    {
        return [
            'create' => 'Draft dibuat',
            'update' => 'Draft diperbarui',
            'start_counting' => 'Penghitungan dimulai',
            'save_counts' => 'Hasil hitung disimpan',
            'submit_verification' => 'Stok fisik disubmit',
            'save_reasons' => 'Alasan selisih disimpan',
            'verify' => 'Divalidasi dan direkonsiliasi',
            'approve' => 'Disetujui',
            'reject' => 'Dikembalikan',
            'post_adjustment' => 'Penyesuaian diposting',
        ];
    }
}
