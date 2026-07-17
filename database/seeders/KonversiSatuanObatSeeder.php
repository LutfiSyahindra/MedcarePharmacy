<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KonversiSatuanObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $obatIds = DB::table('master_obats')->pluck('id', 'kode_obat')->all();
        $satuanIds = DB::table('satuans')->pluck('id', 'kode')->all();
        $seeded = 0;

        DB::transaction(function () use ($now, $obatIds, $satuanIds, &$seeded) {
            foreach ($this->catalog() as $kodeObat => $conversions) {
                $obatId = $obatIds[$kodeObat] ?? null;

                if (! $obatId) {
                    continue;
                }

                DB::table('obat_satuan_conversions')
                    ->where('obat_id', $obatId)
                    ->update([
                        'is_default' => false,
                        'updated_at' => $now,
                    ]);

                foreach ($conversions as $conversion) {
                    $satuanId = $satuanIds[$conversion['satuan']] ?? null;

                    if (! $satuanId) {
                        continue;
                    }

                    $attributes = [
                        'obat_id' => $obatId,
                        'satuan_id' => $satuanId,
                    ];

                    $values = [
                        'konversi' => $conversion['konversi'],
                        'is_default' => $conversion['default'] ?? false,
                        'updated_at' => $now,
                    ];

                    $query = DB::table('obat_satuan_conversions')->where($attributes);

                    if ($query->exists()) {
                        $query->update($values);
                    } else {
                        DB::table('obat_satuan_conversions')->insert($attributes + $values + [
                            'created_at' => $now,
                        ]);
                    }

                    $seeded++;
                }
            }
        });

        $this->command?->info("Seeded {$seeded} konversi satuan obat.");
    }

    private function catalog(): array
    {
        $catalog = [];

        foreach ($this->stripConversions() as $kodeObat => $isiStrip) {
            $catalog[$kodeObat] = $this->strip($isiStrip);
        }

        foreach ($this->singleUnitConversions() as $kodeObat => $satuan) {
            $catalog[$kodeObat] = $this->single($satuan);
        }

        foreach ($this->packagedConversions() as $kodeObat => [$satuan, $konversi]) {
            $catalog[$kodeObat] = $this->single($satuan, $konversi);
        }

        ksort($catalog);

        return $catalog;
    }

    private function stripConversions(): array
    {
        return [
            'OBT0001' => 10,
            'OBT0002' => 10,
            'OBT0003' => 10,
            'OBT0004' => 10,
            'OBT0005' => 10,
            'OBT0006' => 10,
            'OBT0007' => 10,
            'OBT0008' => 10,
            'OBT0009' => 6,
            'OBT0010' => 10,
            'OBT0011' => 10,
            'OBT0012' => 10,
            'OBT0013' => 10,
            'OBT0014' => 10,
            'OBT0015' => 6,
            'OBT0016' => 10,
            'OBT0017' => 12,
            'OBT0018' => 10,
            'OBT0019' => 10,
            'OBT0020' => 10,
            'OBT0021' => 10,
            'OBT0022' => 10,
            'OBT0024' => 10,
            'OBT0027' => 10,
            'OBT0028' => 10,
            'OBT0029' => 10,
            'OBT0032' => 10,
            'OBT0033' => 10,
            'OBT0034' => 10,
            'OBT0036' => 10,
            'OBT0038' => 10,
            'OBT0039' => 10,
            'OBT0040' => 10,
            'OBT0041' => 10,
            'OBT0042' => 10,
            'OBT0043' => 10,
            'OBT0045' => 10,
            'OBT0046' => 10,
            'OBT0047' => 10,
            'OBT0048' => 10,
            'OBT0049' => 10,
            'OBT0051' => 10,
            'OBT0053' => 10,
            'OBT0055' => 10,
            'OBT0056' => 10,
            'OBT0057' => 10,
            'OBT0058' => 10,
            'OBT0059' => 10,
            'OBT0060' => 10,
            'OBT0061' => 10,
            'OBT0062' => 10,
            'OBT0063' => 10,
            'OBT0064' => 10,
            'OBT0065' => 10,
            'OBT0066' => 10,
            'OBT0067' => 10,
            'OBT0069' => 10,
            'OBT0073' => 10,
            'OBT0081' => 10,
            'OBT0082' => 10,
            'OBT0083' => 1,
            'OBT0084' => 1,
        ];
    }

    private function singleUnitConversions(): array
    {
        return [
            'OBT0023' => 'BTL',
            'OBT0025' => 'INH',
            'OBT0030' => 'BTL',
            'OBT0031' => 'BTL',
            'OBT0035' => 'SACH',
            'OBT0037' => 'BTL',
            'OBT0050' => 'SP',
            'OBT0054' => 'BTL',
            'OBT0072' => 'AMP',
            'OBT0074' => 'BTL',
            'OBT0075' => 'BTL',
            'OBT0076' => 'TUB',
            'OBT0077' => 'TUB',
            'OBT0078' => 'TUB',
            'OBT0079' => 'TUB',
            'OBT0080' => 'BTL',
            'OBT0085' => 'BTL',
            'OBT0087' => 'BAG',
            'OBT0088' => 'BAG',
            'OBT0091' => 'BOX',
            'OBT0092' => 'BOX',
            'OBT0093' => 'BOX',
            'OBT0094' => 'RLL',
            'OBT0095' => 'BTL',
            'OBT0096' => 'BTL',
            'OBT0097' => 'KLG',
            'OBT0098' => 'KLG',
            'OBT0099' => 'BTL',
            'OBT0100' => 'TUB',
        ];
    }

    private function packagedConversions(): array
    {
        return [
            'OBT0026' => ['BOX', 5],
            'OBT0044' => ['BTL', 100],
            'OBT0052' => ['BTL', 30],
            'OBT0068' => ['BTL', 30],
            'OBT0070' => ['BTL', 100],
            'OBT0071' => ['BLT', 28],
            'OBT0086' => ['BOX', 100],
            'OBT0089' => ['BOX', 25],
            'OBT0090' => ['BOX', 20],
        ];
    }

    private function strip(int $isiStrip): array
    {
        return [
            [
                'satuan' => 'STR',
                'konversi' => $isiStrip,
                'default' => true,
            ],
            [
                'satuan' => 'BOX',
                'konversi' => $isiStrip * 10,
                'default' => false,
            ],
        ];
    }

    private function single(string $satuan, int $konversi = 1): array
    {
        return [
            [
                'satuan' => $satuan,
                'konversi' => $konversi,
                'default' => true,
            ],
        ];
    }
}
