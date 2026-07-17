<?php

namespace App\Services\Settings\Margins;

use App\Models\CategoryModel;
use App\Models\GolonganModel;
use App\Models\MainGolonganModel;
use App\Models\MarginsModel;
use App\Models\MarginSetting;
use App\Models\MasterObatModel;
use App\Models\SubGolonganModel;
use App\Repositories\Settings\Margins\MarginsRepository;

class MarginsService
{
    public const PRIORITY_KEY = 'margin_priority';

    public const DEFAULT_PRIORITY = [
        'sub_golongan',
        'main_golongan',
        'golongan',
    ];

    public const PRIORITY_OPTIONS = [
        'sub_golongan' => 'Sub Golongan',
        'main_golongan' => 'Main Golongan',
        'golongan' => 'Golongan',
    ];

    /**
     * Create a new class instance.
     */
     protected $MarginsRepository;

    public function __construct(MarginsRepository $MarginsRepository)
    {
        $this->MarginsRepository = $MarginsRepository;
    }
    /**
     * Create a new class instance.
     */
    public function getMargins()
    {
        $dataMargins = $this->MarginsRepository->getMargins();
        return $dataMargins;
    }

    public function getMarginsTable()
    {
        $Margins = $this->MarginsRepository->getMargins();

        $dataMargins = [];
        foreach ($Margins as $r) {
            $reference = $r->getReference();
            $persentase = ($r->faktor_jual - 1) * 100;
            $dataMargins[] = [
                'id'            => $r->id,
                'reference_id'  => $reference?->name ?? $reference?->nama ?? $reference?->nama_obat ?? '-',
                'faktor_jual'   => $r->faktor_jual,
                'persentase'    => number_format($persentase) . '%',
                'tingkat'       => $r->tingkat,
                'is_active'     => $r->is_active,
            ];
        }

        return $dataMargins;
    }

    public static function priorityOptions(): array
    {
        return self::PRIORITY_OPTIONS;
    }

    public function marginPriority(): array
    {
        $setting = MarginSetting::firstOrCreate(
            ['key' => self::PRIORITY_KEY],
            ['value' => ['priority' => self::DEFAULT_PRIORITY]]
        );

        return $this->sanitizePriority($setting->value['priority'] ?? []);
    }

    public function updateMarginPriority(array $priority): array
    {
        $priority = $this->sanitizePriority($priority);

        MarginSetting::updateOrCreate(
            ['key' => self::PRIORITY_KEY],
            ['value' => ['priority' => $priority]]
        );

        return $priority;
    }

    public function activeMarginForObat(MasterObatModel $obat): ?MarginsModel
    {
        foreach ($this->marginPriority() as $tingkat) {
            $referenceId = $this->referenceIdForObat($obat, $tingkat);

            if (! $referenceId) {
                continue;
            }

            $margin = MarginsModel::where('tingkat', $tingkat)
                ->where('reference_id', $referenceId)
                ->where('is_active', true)
                ->latest('id')
                ->first();

            if ($margin) {
                return $margin;
            }
        }

        return null;
    }

    public function marginReferenceLabelForObat(MasterObatModel $obat, ?MarginsModel $margin): ?string
    {
        if (! $margin) {
            return null;
        }

        $reference = $margin->getReference();

        return match ($margin->tingkat) {
            'sub_golongan' => $obat->subGolongan->nama ?? null,
            'main_golongan' => $obat->mainGolongan->nama ?? null,
            'golongan' => $obat->golongan->nama ?? null,
            default => $reference?->nama ?? $reference?->name ?? $reference?->nama_obat ?? null,
        };
    }


    public function createMargins(array $data)
    {
        $dataMargins = $this->MarginsRepository->createMargins($data);
        return $dataMargins;
    }

    public function findByIdMargins($id)
    {
        $Margins = $this->MarginsRepository->findByIdMargins($id);
        return $Margins;
    }

    public function updateMargins($id, array $data)
    {
        $Margins = $this->MarginsRepository->findByIdMargins($id);
        unset($Margins->reference_text);
        $Margins->update($data);
        return $Margins;
    }

    public function deleteMargins($id)
    {
        return $this->MarginsRepository->findByIdMargins($id)->delete();
    }

    public function getReferences($tingkat)
    {
        switch ($tingkat) {
            case 'kategori':
                $data = CategoryModel::select('id', 'name')->get();
                break;
            case 'golongan':
                $data = GolonganModel::select('id', 'nama')->get();
                break;
            case 'main_golongan':
                $data = MainGolonganModel::select('id', 'nama')->get();
                break;
            case 'sub_golongan':
                $data = SubGolonganModel::select('id', 'nama')->get();
                break;
            case 'obat':
                $data = MasterObatModel::select('id', 'nama_obat as nama')->get();
                break;
            default:
                $data = collect();
                break;
        }
        return $data;
    }

    public function updateStatus($id, $status)
    {
        return $this->MarginsRepository->updateStatus($id, $status);
    }

    private function sanitizePriority(array $priority): array
    {
        $allowed = array_keys(self::PRIORITY_OPTIONS);
        $cleanPriority = collect($priority)
            ->map(fn ($level) => (string) $level)
            ->filter(fn ($level) => in_array($level, $allowed, true))
            ->unique()
            ->values()
            ->all();

        foreach (self::DEFAULT_PRIORITY as $level) {
            if (! in_array($level, $cleanPriority, true)) {
                $cleanPriority[] = $level;
            }
        }

        return array_slice($cleanPriority, 0, count(self::DEFAULT_PRIORITY));
    }

    private function referenceIdForObat(MasterObatModel $obat, string $tingkat): ?int
    {
        $referenceId = match ($tingkat) {
            'sub_golongan' => $obat->sub_golongan_id,
            'main_golongan' => $obat->main_golongan_id,
            'golongan' => $obat->golongan_id,
            default => null,
        };

        return $referenceId ? (int) $referenceId : null;
    }

}
