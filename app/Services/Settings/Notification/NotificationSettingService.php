<?php

namespace App\Services\Settings\Notification;

use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\PembelianPenerimaan\ReturPembelianModel;
use App\Models\NotificationSetting;
use App\Models\User;
use Illuminate\Support\Arr;

class NotificationSettingService
{
    public const KEY = 'transaction_notifications';

    public const MODULES = [
        'pembelian' => 'Pembelian',
        'penerimaan' => 'Penerimaan',
        'retur_pembelian' => 'Retur Pembelian',
    ];

    public function defaults(): array
    {
        return [
            'modules' => [
                'pembelian' => ['enabled' => true],
                'penerimaan' => ['enabled' => true],
                'retur_pembelian' => ['enabled' => true],
            ],
            'roles' => [
                'admin' => true,
                'apoteker' => true,
            ],
            'notify_creator' => true,
            'broadcast' => true,
            'sound' => true,
            'same_branch_only' => true,
            'navbar_limit' => 8,
        ];
    }

    public function settings(): array
    {
        $row = NotificationSetting::firstOrCreate(
            ['key' => self::KEY],
            ['value' => $this->defaults()]
        );

        return array_replace_recursive($this->defaults(), $row->value ?: []);
    }

    public function update(array $payload): array
    {
        $settings = $this->sanitize($payload);

        NotificationSetting::updateOrCreate(
            ['key' => self::KEY],
            ['value' => $settings]
        );

        return $settings;
    }

    public function moduleEnabled(string $module): bool
    {
        return (bool) Arr::get($this->settings(), "modules.$module.enabled", false);
    }

    public function approverRoleKeys(): array
    {
        $roles = Arr::get($this->settings(), 'roles', []);

        return collect($roles)
            ->filter(fn ($enabled) => (bool) $enabled)
            ->keys()
            ->map(fn ($role) => strtolower((string) $role))
            ->values()
            ->all();
    }

    public function approverRoleNames(): array
    {
        return collect($this->approverRoleKeys())
            ->flatMap(fn ($role) => [
                strtolower($role),
                ucfirst(strtolower($role)),
            ])
            ->unique()
            ->values()
            ->all();
    }

    public function notifyCreatorEnabled(): bool
    {
        return (bool) Arr::get($this->settings(), 'notify_creator', true);
    }

    public function broadcastEnabled(): bool
    {
        return (bool) Arr::get($this->settings(), 'broadcast', true);
    }

    public function soundEnabled(): bool
    {
        return (bool) Arr::get($this->settings(), 'sound', true);
    }

    public function sameBranchOnly(): bool
    {
        return (bool) Arr::get($this->settings(), 'same_branch_only', true);
    }

    public function navbarLimit(): int
    {
        return min(20, max(3, (int) Arr::get($this->settings(), 'navbar_limit', 8)));
    }

    public function userCanManage(?User $user): bool
    {
        return $this->userIsApprover($user);
    }

    public function userIsApprover(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $roleNames = $this->approverRoleNames();

        if (empty($roleNames)) {
            return false;
        }

        return $user->hasAnyRole($roleNames);
    }

    public function stats(): array
    {
        return [
            'pending' => [
                'pembelian' => PembelianModel::whereIn('status', ['draft', 'waiting_approval'])->count(),
                'penerimaan' => PenerimaanBarangModel::where('status', 'draft')->count(),
                'retur_pembelian' => ReturPembelianModel::where('status', 'draft')->count(),
            ],
        ];
    }

    private function sanitize(array $payload): array
    {
        $settings = $this->defaults();

        foreach (array_keys(self::MODULES) as $module) {
            $settings['modules'][$module]['enabled'] = (bool) Arr::get($payload, "modules.$module.enabled", false);
        }

        $settings['roles']['admin'] = (bool) Arr::get($payload, 'roles.admin', false);
        $settings['roles']['apoteker'] = (bool) Arr::get($payload, 'roles.apoteker', false);
        $settings['notify_creator'] = (bool) Arr::get($payload, 'notify_creator', false);
        $settings['broadcast'] = (bool) Arr::get($payload, 'broadcast', false);
        $settings['sound'] = (bool) Arr::get($payload, 'sound', false);
        $settings['same_branch_only'] = (bool) Arr::get($payload, 'same_branch_only', false);
        $settings['navbar_limit'] = min(20, max(3, (int) Arr::get($payload, 'navbar_limit', 8)));

        return $settings;
    }
}
