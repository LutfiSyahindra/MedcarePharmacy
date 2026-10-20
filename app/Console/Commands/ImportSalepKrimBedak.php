<?php

namespace App\Console\Commands;

use App\Models\MasterObatModel;
use App\Services\Settings\Master\MasterObatService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ImportSalepKrimBedak extends Command
{
    protected $signature = 'medcare:import-salep-krim-bedak {--apply : Simpan impor dan lengkapi main golongan yang kosong}';

    protected $description = 'Impor 146 produk 17 Oktober 2026 dan lengkapi hierarki SALEP, KRIM DAN BEDAK';

    public function handle(MasterObatService $service): int
    {
        $catalog = require database_path('data/salep_krim_bedak_2026_10_17.php');
        $group = DB::table('golongan_obats')->where('nama', $catalog['group'])->where('is_active', true)->sole();
        $main = DB::table('main_golongan_obats')->where('golongan_id', $group->id)->get()->keyBy('kode');
        foreach ($catalog['main_names'] as $code => $name) {
            if (($main[$code]->nama ?? null) !== $name) {
                throw new RuntimeException('Rujukan main golongan tidak sesuai: '.$code);
            }
        }
        $items = $this->readItems($catalog);
        $plan = $this->plan($items, $catalog, $group->id, $main);
        $created = count(array_filter($plan['items'], fn ($item) => $item['existing_id'] === null));
        $this->info(count($items).' produk sumber; '.$created.' baru; '.(count($items) - $created).' sudah ada; '.count($plan['backfill']).' main golongan lama akan dilengkapi; '.count($plan['reclassify']).' produk sumber dipindahkan ke golongan tujuan.');

        if (! $this->option('apply')) {
            $this->table(['Nama sumber', 'Main', 'Obat yang sudah ada'], array_map(fn ($item) => [
                $item['name'], $item['profile']['main'], $item['existing_name'] ?? '-',
            ], $plan['items']));
            $this->info('Pratinjau selesai tanpa perubahan database.');

            return self::SUCCESS;
        }

        File::ensureDirectoryExists(storage_path('app/imports'));
        $auditPath = storage_path('app/imports/salep-krim-bedak-'.now()->format('Ymd-His-u').'.json');
        $audit = [
            'status' => 'prepared',
            'source' => 'database/data/salep_krim_bedak_2026_10_17.tsv',
            'source_sha256' => hash_file('sha256', database_path('data/salep_krim_bedak_2026_10_17.tsv')),
            'group' => (array) $group,
            'main_references' => $main->values()->all(),
            'price_rule' => 'Kolom harga tanpa header disimpan pada raw_columns; harga_beli baru tetap 0 sampai basis harga/satuan dikonfirmasi.',
            'before_count' => MasterObatModel::count(),
            'plan' => $plan,
        ];
        $this->writeAudit($auditPath, $audit);

        $result = DB::transaction(function () use ($items, $catalog, $group, $main, $service) {
            DB::table('golongan_obats')->where('id', $group->id)->lockForUpdate()->sole();
            $plan = $this->plan($items, $catalog, $group->id, $main);
            $categories = DB::table('categories')->pluck('id', 'code');
            $units = DB::table('satuans')->where('is_active', true)->pluck('id', 'kode');
            $forms = DB::table('sediaan_obats')->where('is_active', true)->pluck('id', 'kode');
            $result = ['items' => [], 'backfill' => [], 'reclassify' => []];

            foreach ($plan['reclassify'] as $row) {
                $updated = DB::table('master_obats')->where('id', $row['before']['id'])
                    ->where('golongan_id', $row['before']['golongan_id'])
                    ->where('main_golongan_id', $row['before']['main_golongan_id'])
                    ->update(['golongan_id' => $group->id, 'main_golongan_id' => $row['main_golongan_id'], 'sub_golongan_id' => null, 'updated_at' => now()]);
                if ($updated !== 1) {
                    throw new RuntimeException('Golongan produk sumber berubah: '.$row['before']['nama_obat']);
                }
                $result['reclassify'][] = $row;
            }

            foreach ($plan['backfill'] as $row) {
                $updated = DB::table('master_obats')->where('id', $row['before']['id'])
                    ->where('golongan_id', $group->id)->whereNull('main_golongan_id')
                    ->update(['main_golongan_id' => $row['main_golongan_id'], 'updated_at' => now()]);
                if ($updated !== 1) {
                    throw new RuntimeException('Data berubah ketika melengkapi main: '.$row['before']['nama_obat']);
                }
                $result['backfill'][] = $row;
            }

            foreach ($plan['items'] as $item) {
                if ($item['existing_id'] !== null) {
                    $result['items'][] = [...$item, 'id' => $item['existing_id'], 'action' => 'existing'];

                    continue;
                }
                $profile = $item['profile'];
                [$unit, $form] = $this->presentation($item['name']);
                $manufacturer = $catalog['manufacturer_aliases'][$item['raw_columns'][9]] ?? throw new RuntimeException('Pabrikan belum dipetakan: '.$item['name']);
                $distributor = $catalog['distributor_aliases'][$item['raw_columns'][13]] ?? throw new RuntimeException('Distributor belum dipetakan: '.$item['name']);
                $medicine = $service->createMasterObat([
                    'kode_prefix' => 'SKB',
                    'nama_obat' => $item['name'],
                    'golongan_id' => $group->id,
                    'main_golongan_id' => $main[$profile['main']]->id,
                    'sub_golongan_id' => null,
                    'category_id' => $categories[$item['source_code'] === '13006552' ? 'HRM' : $profile['category']],
                    'satuan_id' => $units[$unit],
                    'sediaan_id' => $form ? $forms[$form] : null,
                    'pabrikan_id' => $this->reference('pabrikan', $manufacturer),
                    'distributor_id' => $this->reference('distributors', $distributor),
                    'kemasan' => $item['name'],
                    'is_generik' => $item['raw_columns'][3] === 'GENERIC',
                    'is_active' => true,
                ]);
                $result['items'][] = [...$item, 'id' => $medicine->id, 'kode_obat' => $medicine->kode_obat, 'action' => 'created'];
            }

            if (DB::table('master_obats')->where('golongan_id', $group->id)->whereNull('main_golongan_id')->exists()) {
                throw new RuntimeException('Masih ada main golongan kosong; transaksi dibatalkan.');
            }

            return $result;
        });

        $created = count(array_filter($result['items'], fn ($item) => $item['action'] === 'created'));
        $audit = [...$audit, 'status' => 'committed', 'imported_at' => now()->toIso8601String(),
            'created' => $created, 'existing' => count($items) - $created,
            'backfilled' => count($result['backfill']), 'reclassified' => count($result['reclassify']),
            'after_count' => MasterObatModel::count(), 'result' => $result];
        $this->writeAudit($auditPath, $audit);
        $this->info($created.' produk ditambahkan; '.(count($items) - $created).' sudah ada; '.count($result['backfill']).' main golongan dilengkapi; '.count($result['reclassify']).' golongan produk sumber disesuaikan. Laporan: '.$auditPath);

        return self::SUCCESS;
    }

    private function plan(array $items, array $catalog, int $groupId, Collection $main): array
    {
        $medicines = MasterObatModel::query()->get();
        $names = $medicines->groupBy(fn ($row) => $this->canonical($row->nama_obat));
        $backfill = [];
        $sub = DB::table('sub_golongan_obats')->pluck('main_golongan_id', 'id');
        foreach ($medicines->where('golongan_id', $groupId)->whereNull('main_golongan_id') as $medicine) {
            $profile = $this->profile($medicine->nama_obat, $catalog);
            $mainId = $main[$profile['main']]->id;
            if ($medicine->sub_golongan_id !== null && ($sub[$medicine->sub_golongan_id] ?? null) !== $mainId) {
                throw new RuntimeException('Sub golongan bertentangan: '.$medicine->nama_obat);
            }
            $backfill[] = ['before' => $medicine->getAttributes(), 'main_golongan_id' => $mainId, 'profile' => $profile];
        }

        $categories = DB::table('categories')->pluck('id', 'code');
        $units = DB::table('satuans')->where('is_active', true)->pluck('id', 'kode');
        $forms = DB::table('sediaan_obats')->where('is_active', true)->pluck('id', 'kode');
        $matched = [];
        $reclassify = [];
        foreach ($items as &$item) {
            $matches = $names->get($this->canonical($item['name']), collect());
            if ($matches->isEmpty() && isset($catalog['aliases'][$item['name']])) {
                $matches = $names->get($this->canonical($catalog['aliases'][$item['name']]), collect());
            }
            if ($matches->count() > 1) {
                throw new RuntimeException('Nama ambigu: '.$item['name']);
            }
            $existing = $matches->first();
            if ($existing && isset($matched[$existing->id])) {
                throw new RuntimeException('Dua produk sumber cocok dengan satu master: '.$item['name']);
            }
            if ($existing) {
                $matched[$existing->id] = true;
                if ($existing->golongan_id !== $groupId) {
                    $reclassify[] = ['before' => $existing->getAttributes(),
                        'main_golongan_id' => $main[$item['profile']['main']]->id, 'profile' => $item['profile']];
                }
            }
            $item['existing_id'] = $existing?->id;
            $item['existing_name'] = $existing?->nama_obat;
            $item['kode_obat'] = $existing?->kode_obat;
            [$unit, $form] = $this->presentation($item['name']);
            if (! isset($categories[$item['profile']['category']], $categories['HRM'], $units[$unit]) || ($form && ! isset($forms[$form]))) {
                throw new RuntimeException('Rujukan kategori/satuan/sediaan tidak lengkap: '.$item['name']);
            }
            foreach (['manufacturer_aliases' => 9, 'distributor_aliases' => 13] as $key => $index) {
                if (! isset($catalog[$key][$item['raw_columns'][$index]])) {
                    throw new RuntimeException('Rujukan sumber belum dipetakan: '.$item['raw_columns'][$index]);
                }
            }
        }
        unset($item);

        return ['items' => $items, 'backfill' => $backfill, 'reclassify' => $reclassify];
    }

    private function readItems(array $catalog): array
    {
        $lines = file(database_path('data/salep_krim_bedak_2026_10_17.tsv'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $items = [];
        $seen = [];
        foreach ($lines as $line) {
            $row = array_map('trim', explode("\t", $line));
            if (count($row) !== 23 || ! preg_match('/^\d{8}$/', $row[0]) || $row[1] === '' || isset($seen[$row[0]])) {
                throw new RuntimeException('Struktur/kode sumber tidak valid.');
            }
            $key = $this->canonical($row[1]);
            if (isset($seen[$key])) {
                throw new RuntimeException('Nama berulang dalam sumber: '.$row[1]);
            }
            $seen[$row[0]] = $seen[$key] = true;
            $items[] = ['source_code' => $row[0], 'name' => $row[1], 'raw_columns' => $row, 'profile' => $this->profile($row[1], $catalog)];
        }
        if (count($items) !== 146) {
            throw new RuntimeException('Jumlah sumber harus 146 produk.');
        }

        return $items;
    }

    private function profile(string $name, array $catalog): array
    {
        $name = preg_replace('/\s+/', ' ', mb_strtoupper(trim($name)));
        foreach ($catalog['rules'] as $rule) {
            if (preg_match($rule['pattern'], $name) === 1) {
                return $rule;
            }
        }

        throw new RuntimeException('Produk belum memiliki pemetaan main golongan: '.$name);
    }

    private function presentation(string $name): array
    {
        $name = mb_strtoupper($name);
        if (str_contains($name, 'MARCKS')) {
            return match (true) {
                str_contains($name, 'MASK') => ['SACH', null],
                str_contains($name, 'TONER'), str_contains($name, 'WATER') => ['BTL', 'SED-043'],
                str_contains($name, 'FACE WASH') => ['TUB', null],
                str_contains($name, 'SPOT GEL') => ['TUB', 'SED-041'],
                str_contains($name, 'MOISTURIZER') => ['TUB', 'SED-039'],
                str_contains($name, 'CAKE'), str_contains($name, 'COMPACT'), str_contains($name, 'TEENS CP') => ['PC', null],
                default => ['JAR', 'SED-045'], // CREME adalah warna bedak, bukan krim.
            };
        }

        return match (true) {
            str_contains($name, 'TAB@100') => ['BOX', 'SED-001'],
            str_contains($name, 'VAGINA TAB') => ['TBL', null],
            str_contains($name, 'BURNAZIN CREAM 500') => ['JAR', 'SED-039'],
            str_contains($name, 'SCALP') => ['BTL', 'SED-047'],
            str_contains($name, 'CARMED LOT') => ['TUB', 'SED-043'],
            preg_match('/\b(LOT|LOTION)\b/', $name) === 1 => ['BTL', 'SED-043'],
            preg_match('/\b(SOL|LIQ)\b/', $name) === 1 => ['BTL', 'SKB-S-CB090E25B3'],
            str_contains($name, 'CLEANSER'), str_contains($name, 'GLIKODERM') => ['BTL', null],
            preg_match('/\b(OINT|SALEP)\b/', $name) === 1 => ['TUB', 'SED-040'],
            str_contains($name, 'GEL') => ['TUB', 'SED-041'],
            str_contains($name, 'MELANOX ES') => ['TUB', 'SED-039'],
            str_contains($name, 'WOUND CARE') => ['TUB', null],
            default => ['TUB', 'SED-039'],
        };
    }

    private function reference(string $table, string $name): int
    {
        $matches = DB::table($table)->get(['id', 'nama', 'is_active'])
            ->filter(fn ($row) => $this->canonical($row->nama) === $this->canonical($name));
        if ($matches->count() > 1 || ($matches->first() && ! $matches->first()->is_active)) {
            throw new RuntimeException('Referensi ambigu/tidak aktif: '.$table.' / '.$name);
        }

        return $matches->first()?->id ?? DB::table($table)->insertGetId([
            'kode' => 'SKB-'.($table === 'pabrikan' ? 'P' : 'D').'-'.strtoupper(substr(sha1($name), 0, 10)),
            'nama' => $name, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function canonical(string $name): string
    {
        $name = mb_strtoupper(trim($name));
        $name = preg_replace('/(?<=\d)(?=[A-Z])|(?<=[A-Z])(?=\d)/', ' ', $name);
        $name = preg_replace('/\b(CR|CRM|CREAM|KRIM)\b/', 'KRIM', $name);
        $name = preg_replace('/\b(OINT|OINTMENT)\b/', 'SALEP', $name);
        $name = preg_replace('/\b(GR|GRAM)\b/', 'G', $name);

        return preg_replace('/[^\pL\pN.%]+/u', '', $name);
    }

    private function writeAudit(string $path, array $data): void
    {
        if (File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) {
            throw new RuntimeException('Laporan tidak dapat ditulis: '.$path);
        }
    }
}
