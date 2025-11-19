<?php

namespace App\Imports\MasterData\Sediaan;


use App\Models\SediaanModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SediaanObatImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Abaikan baris kosong atau tanpa data penting
        if (empty($row['kode']) || empty($row['nama'])) {
            return null;
        }

        // Normalisasi nilai (trim spasi & jadikan huruf besar jika perlu)
        $kode = Str::upper(trim($row['kode']));
        $nama = trim($row['nama']);

        // Cek apakah sudah ada berdasarkan kode
        $existing = SediaanModel::whereRaw('UPPER(kode) = ?', [$kode])->first();

        if ($existing) {
            // Jika sudah ada, abaikan
            return null;
        }

        // Jika belum ada, tambahkan data baru
        return new SediaanModel([
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }
}
