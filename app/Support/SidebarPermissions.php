<?php

namespace App\Support;

final class SidebarPermissions
{
    public const DASHBOARD = 'MEDCARE.DASHBOARD';

    public const SETTINGS_PROFILE_APOTEK = 'MEDCARE.SETTINGS.PROFILE_APOTEK';

    public const SETTINGS_AUTH = 'MEDCARE.SETTINGS.AUTH';

    public const SETTINGS_ROLE_SETTING = 'MEDCARE.SETTINGS.ROLE_SETTING';

    public const SETTINGS_BRANCH = 'MEDCARE.SETTINGS.BRANCH';

    public const SETTINGS_MARGIN = 'MEDCARE.SETTINGS.MARGIN';

    public const SETTINGS_NOTIFIKASI = 'MEDCARE.SETTINGS.NOTIFIKASI';

    public const MASTER_DATA = 'MEDCARE.MASTER_DATA';

    public const NOTIFIKASI = 'MEDCARE.MENU.NOTIFIKASI';

    public const PENJUALAN = 'MEDCARE.MENU.PENJUALAN';

    public const PASIEN = 'MEDCARE.MENU.PASIEN';

    public const PEMBELIAN = 'MEDCARE.MENU.PEMBELIAN';

    public const DOKUMEN = 'MEDCARE.MENU.DOKUMEN';

    public const STOK = 'MEDCARE.MENU.STOK';

    public const ANALISIS_PERSEDIAAN = 'MEDCARE.MENU.ANALISIS_PERSEDIAAN';

    public const ANALISIS_PENJUALAN = 'MEDCARE.MENU.ANALISIS_PENJUALAN';

    public const ANALISIS_PROFITABILITAS = 'MEDCARE.MENU.ANALISIS_PROFITABILITAS';

    public const ANALISIS_PENGADAAN = 'MEDCARE.MENU.ANALISIS_PENGADAAN';

    public const LAPORAN = 'MEDCARE.MENU.LAPORAN';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::DASHBOARD,
            ...self::settings(),
            self::MASTER_DATA,
            ...self::operations(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function settings(): array
    {
        return [
            self::SETTINGS_PROFILE_APOTEK,
            self::SETTINGS_AUTH,
            self::SETTINGS_ROLE_SETTING,
            self::SETTINGS_BRANCH,
            self::SETTINGS_MARGIN,
            self::SETTINGS_NOTIFIKASI,
        ];
    }

    /**
     * @return list<string>
     */
    public static function operations(): array
    {
        return [
            self::NOTIFIKASI,
            self::PENJUALAN,
            self::PASIEN,
            self::PEMBELIAN,
            self::DOKUMEN,
            self::STOK,
            self::ANALISIS_PERSEDIAAN,
            self::ANALISIS_PENJUALAN,
            self::ANALISIS_PROFITABILITAS,
            self::ANALISIS_PENGADAAN,
            self::LAPORAN,
        ];
    }

    /**
     * Permission introduced together with complete sidebar authorization.
     *
     * @return list<string>
     */
    public static function additions(): array
    {
        return [
            self::SETTINGS_ROLE_SETTING,
            self::PASIEN,
            self::DOKUMEN,
            self::ANALISIS_PERSEDIAAN,
            self::ANALISIS_PENJUALAN,
            self::ANALISIS_PROFITABILITAS,
            self::ANALISIS_PENGADAAN,
            self::LAPORAN,
        ];
    }
}
