<?php

namespace App\Imports\MasterData\Satuan;


use App\Models\SatuansModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class SatuanObatImport implements ToModel, WithHeadingRow
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
        $existing = SatuansModel::whereRaw('UPPER(kode) = ?', [$kode])->first();

        if ($existing) {
            // Jika sudah ada, abaikan
            return null;
        }

        // Jika belum ada, tambahkan data baru
        return new SatuansModel([
            'kode' => $kode,
            'nama' => $nama,
        ]);
    }
}
