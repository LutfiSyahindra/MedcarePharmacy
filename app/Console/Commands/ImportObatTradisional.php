<?php

namespace App\Console\Commands;

use App\Models\MasterObatModel;
use App\Services\Settings\Master\MasterObatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ImportObatTradisional extends Command
{
    protected $signature = 'medcare:import-obat-tradisional {--apply : Simpan data setelah validasi}';

    protected $description = 'Impor 95 produk dari daftar 11 Oktober 2026 ke golongan OBAT TRADISIONAL';

    public function handle(MasterObatService $service): int
    {
        $catalog = require database_path('data/obat_tradisional_2026_10_11.php');
        $items = $this->readItems($catalog);
        $group = DB::table('golongan_obats')->where('nama', $catalog['group'])->where('is_active', true)->sole();
        $categories = DB::table('categories')->pluck('id', 'code');
        $units = DB::table('satuans')->where('is_active', true)->pluck('id', 'kode');
        $forms = DB::table('sediaan_obats')->where('is_active', true)->pluck('id', 'kode');
        $newForms = ['OTL-PIL' => 'Pil', 'OTL-PRM' => 'Permen'];
        $existing = MasterObatModel::query()->get(['id', 'kode_obat', 'nama_obat'])
            ->groupBy(fn ($item) => $this->normalize($item->nama_obat));

        foreach ($items as &$item) {
            if (! isset($categories[$item['profile']['category']], $units[$item['unit_code']])) {
                throw new RuntimeException('Referensi kategori/satuan tidak ditemukan: '.$item['name']);
            }
            $form = $item['profile']['form'];
            if ($form && ! isset($forms[$form]) && ! isset($newForms[$form])) {
                throw new RuntimeException('Referensi sediaan tidak ditemukan: '.$form);
            }
            $matches = $existing->get($this->normalize($item['name']), collect());
            if ($matches->count() > 1) {
                throw new RuntimeException('Nama ambigu di database: '.$item['name']);
            }
            $item['existing_id'] = $matches->first()?->id;
            $item['kode_obat'] = $matches->first()?->kode_obat;
        }
        unset($item);

        $pending = count(array_filter($items, fn ($item) => $item['profile']['composition'] === null));
        $skipped = count(array_filter($items, fn ($item) => $item['existing_id'] !== null));
        $this->info(count($items).' produk; golongan '.$group->nama.'; '.$pending.' kandungan belum terverifikasi; '.$skipped.' nama sudah ada.');

        if (! $this->option('apply')) {
            $this->table(['Nama', 'Kategori', 'Verifikasi'], array_map(fn ($item) => [
                $item['name'], $item['profile']['category'], $item['profile']['status'],
            ], $items));
            $this->info('Pratinjau selesai. Gunakan --apply untuk menyimpan.');

            return self::SUCCESS;
        }

        $auditDirectory = storage_path('app/imports');
        File::ensureDirectoryExists($auditDirectory);
        $auditPath = $auditDirectory.'/obat-tradisional-'.now()->format('Ymd-His-u').'.json';
        $before = ['medicine_count' => MasterObatModel::count(), 'golongan_id' => $group->id];
        // Pastikan laporan dapat ditulis sebelum transaksi, tanpa menyertakan data pasien/kredensial.
        File::put($auditPath, json_encode(['status' => 'prepared', 'before' => $before, 'items' => $items], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        $result = DB::transaction(function () use ($items, $catalog, $categories, $units, $forms, $newForms, $group, $service) {
            // Mengunci golongan membuat pengulangan impor ini tidak berjalan bersamaan.
            DB::table('golongan_obats')->where('id', $group->id)->lockForUpdate()->first();
            $existing = MasterObatModel::query()->get(['id', 'kode_obat', 'nama_obat'])
                ->groupBy(fn ($medicine) => $this->normalize($medicine->nama_obat));
            $result = [];

            foreach ($items as $item) {
                $profile = $item['profile'];
                // Periksa ulang nama setelah lock; impor tidak menimpa produk yang sudah ada.
                $matches = $existing->get($this->normalize($item['name']), collect());
                if ($matches->count() > 1) {
                    throw new RuntimeException('Nama ambigu di database: '.$item['name']);
                }
                if ($medicine = $matches->first()) {
                    $result[] = [...$item, 'id' => $medicine->id, 'kode_obat' => $medicine->kode_obat, 'action' => 'skipped'];

                    continue;
                }

                $formId = null;
                if ($profile['form']) {
                    $formId = $forms[$profile['form']] ?? $this->reference('sediaan_obats', $newForms[$profile['form']], $profile['form']);
                }
                $manufacturer = $item['manufacturer'] ?: $profile['manufacturer'];
                $manufacturer = $catalog['manufacturer_aliases'][$manufacturer] ?? $manufacturer;
                $distributor = $item['distributors'][0] ?? null;
                $distributor = $catalog['distributor_aliases'][$distributor] ?? $distributor;

                $medicine = $service->createMasterObat([
                    'kode_prefix' => 'OTL',
                    'nama_obat' => $item['name'],
                    'golongan_id' => $group->id,
                    'main_golongan_id' => null,
                    'sub_golongan_id' => null,
                    'category_id' => $categories[$profile['category']],
                    'satuan_id' => $units[$item['unit_code']],
                    'sediaan_id' => $formId,
                    'pabrikan_id' => $manufacturer ? $this->reference('pabrikan', $manufacturer, 'OTL-P-'.strtoupper(substr(sha1($this->normalize($manufacturer)), 0, 10))) : null,
                    'distributor_id' => $distributor ? $this->reference('distributors', $distributor, 'OTL-D-'.strtoupper(substr(sha1($this->normalize($distributor)), 0, 10))) : null,
                    'komposisi' => $profile['composition'],
                    'indikasi' => $profile['indication'],
                    'kemasan' => $item['name'],
                    'is_generik' => false,
                    'is_active' => true,
                ]);
                $result[] = [...$item, 'id' => $medicine->id, 'kode_obat' => $medicine->kode_obat, 'action' => 'created'];
            }

            return $result;
        });

        $created = count(array_filter($result, fn ($item) => $item['action'] === 'created'));
        File::put($auditPath, json_encode([
            'status' => 'committed',
            'imported_at' => now()->toIso8601String(),
            'source' => 'database/data/obat_tradisional_2026_10_11.tsv',
            'group_rule' => 'OBAT TRADISIONAL sesuai arahan pengguna, bukan penetapan kelas registrasi BPOM.',
            'category_rule' => 'Pemetaan kategori Medcare berdasarkan fungsi produk; bukan kelas registrasi BPOM.',
            'before' => $before,
            'created' => $created,
            'skipped' => count($result) - $created,
            'pending_composition' => $pending,
            'items' => $result,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $this->info($created.' produk ditambahkan; '.(count($result) - $created).' dilewati. Laporan: '.$auditPath);

        return self::SUCCESS;
    }

    private function readItems(array $catalog): array
    {
        $handle = fopen(database_path('data/obat_tradisional_2026_10_11.tsv'), 'r');
        if ($handle === false) {
            throw new RuntimeException('File sumber tidak dapat dibaca.');
        }
        $items = [];
        $seen = [];
        fgetcsv($handle, 0, "\t", '"', ''); // Header distributor berulang diproses berdasarkan posisi kolom.
        try {
            while (($row = fgetcsv($handle, 0, "\t", '"', '')) !== false) {
                $name = trim($row[0] ?? '');
                if ($name === '') {
                    continue;
                }
                if (count($row) !== 12 || trim($row[10]) !== $catalog['group']) {
                    throw new RuntimeException('Struktur/golongan sumber tidak sesuai: '.$name);
                }
                $normalized = $this->normalize($name);
                if (isset($seen[$normalized])) {
                    throw new RuntimeException('Nama berulang dalam sumber: '.$name);
                }
                $seen[$normalized] = true;
                $profiles = array_values(array_filter($catalog['profiles'], fn ($profile) => preg_match($profile['pattern'], $name) === 1));
                if (count($profiles) !== 1) {
                    throw new RuntimeException('Produk harus memiliki tepat satu profil: '.$name);
                }
                $profile = $profiles[0];
                foreach (['composition', 'indication'] as $field) {
                    if ($profile[$field] !== null && mb_strlen($profile[$field]) > 255) {
                        throw new RuntimeException('Teks melebihi batas database: '.$name.' / '.$field);
                    }
                }
                if ($profile['composition'] !== null && $profile['sources'] === []) {
                    throw new RuntimeException('Kandungan tanpa sumber: '.$name);
                }
                $unit = $catalog['unit_codes'][trim($row[1])] ?? throw new RuntimeException('Satuan tidak dikenal: '.$name);
                $distributors = array_values(array_unique(array_filter(array_map('trim', array_slice($row, 6, 3)), fn ($value) => $value !== '' && strtoupper($value) !== 'OTC')));
                $items[] = [
                    'name' => $name,
                    'unit_code' => $unit,
                    'manufacturer' => trim($row[5]) ?: null,
                    'distributors' => $distributors,
                    'source_specific_content' => trim($row[2]) ?: null,
                    'profile' => $profile,
                ];
            }
        } finally {
            fclose($handle);
        }
        if (count($items) !== 95) {
            throw new RuntimeException('Jumlah sumber harus 95 produk; ditemukan '.count($items).'.');
        }

        return $items;
    }

    private function reference(string $table, string $name, string $code): int
    {
        $matches = DB::table($table)->get(['id', 'nama', 'is_active'])
            ->filter(fn ($row) => $this->normalize($row->nama) === $this->normalize($name));
        if ($matches->count() > 1) {
            throw new RuntimeException('Referensi nama ambigu: '.$table.' / '.$name);
        }
        if ($match = $matches->first()) {
            if (! $match->is_active) {
                throw new RuntimeException('Referensi tidak aktif: '.$name);
            }

            return (int) $match->id;
        }

        return DB::table($table)->insertGetId([
            'kode' => $code, 'nama' => $name, 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function normalize(string $name): string
    {
        return preg_replace('/[^\pL\pN]+/u', '', mb_strtoupper(trim($name)));
    }
}
