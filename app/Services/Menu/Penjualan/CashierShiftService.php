<?php

namespace App\Services\Menu\Penjualan;

use App\Models\BranchModel;
use App\Models\Menu\Penjualan\CashierCashMovementModel;
use App\Models\Menu\Penjualan\CashierShiftModel;
use App\Models\Menu\Penjualan\PenjualanPaymentModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\User;
use App\Services\Settings\Auth\RoleSettingService;
use Carbon\CarbonInterface;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashierShiftService
{
    public const DEFAULT_TIMEZONE = 'Asia/Jakarta';

    private const DAY_LABELS = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];

    public function __construct(private readonly RoleSettingService $roleSettings) {}

    public function accessibleBranches(?User $user = null)
    {
        $user = $user ?: Auth::user();

        return BranchModel::query()
            ->whereIn('id', $this->roleSettings->posBranchIds($user, true))
            ->where('is_active', true)
            ->with('apotekProfile:id,branch_id,operational_hours')
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
                'operational_timezone',
            ]);
    }

    public function accessibleBranch(int $branchId, ?User $user = null, bool $activeOnly = true): BranchModel
    {
        $user = $user ?: Auth::user();

        $branch = BranchModel::query()
            ->whereKey($branchId)
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->whereIn('id', $this->roleSettings->posBranchIds($user, $activeOnly))
            ->with('apotekProfile:id,branch_id,operational_hours')
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch_id' => 'Cabang kasir tidak aktif atau tidak dapat diakses.',
            ]);
        }

        return $branch;
    }

    public function operationalState(BranchModel $branch, ?CarbonInterface $at = null): array
    {
        $timezone = $this->timezone($branch->operational_timezone);
        $localNow = $at
            ? Carbon::instance($at)->setTimezone($timezone)
            : now($timezone);
        $branch->loadMissing('apotekProfile:id,branch_id,operational_hours');

        $hours = $branch->apotekProfile?->operational_hours;
        $isConfigured = is_array($hours) && $hours !== [];
        $today = strtolower($localNow->format('l'));
        $yesterday = strtolower($localNow->copy()->subDay()->format('l'));
        $todaySchedule = $this->scheduleForDay($hours, $today);
        $yesterdaySchedule = $this->scheduleForDay($hours, $yesterday);
        $nowSeconds = $this->timeToSeconds($localNow->format('H:i:s'));

        $activeSchedule = null;
        $activeDay = $today;

        // Jadwal yang melewati tengah malam tetap mengikuti hari saat kasir dibuka.
        if ($this->continuesAfterMidnight($yesterdaySchedule)
            && $nowSeconds < $this->timeToSeconds($yesterdaySchedule['close'])) {
            $activeSchedule = $yesterdaySchedule;
            $activeDay = $yesterday;
        } elseif ($this->scheduleIsOpen($todaySchedule, $nowSeconds)) {
            $activeSchedule = $todaySchedule;
        }

        $displaySchedule = $activeSchedule ?: $todaySchedule;
        $isOpen = $activeSchedule !== null;
        $isTwentyFourHours = $isOpen && $activeSchedule['open'] === $activeSchedule['close'];
        $isClosedDay = ! $isOpen && $isConfigured && ($todaySchedule === null || ! $todaySchedule['enabled']);
        $start = $displaySchedule['open'] ?? '00:00:00';
        $end = $displaySchedule['close'] ?? '00:00:00';
        $label = match (true) {
            ! $isConfigured => 'Belum diatur di Profile Apotek',
            ! $displaySchedule || ! $displaySchedule['enabled'] => (self::DAY_LABELS[$today] ?? ucfirst($today)).' tutup',
            $isTwentyFourHours => '24 jam',
            default => substr($start, 0, 5).'–'.substr($end, 0, 5),
        };

        return [
            'is_open' => $isOpen,
            'is_configured' => $isConfigured,
            'is_24_hours' => $isTwentyFourHours,
            'is_closed_day' => $isClosedDay,
            'day' => $today,
            'day_label' => self::DAY_LABELS[$today] ?? ucfirst($today),
            'schedule_day' => $isOpen ? $activeDay : $today,
            'schedule_day_label' => self::DAY_LABELS[$isOpen ? $activeDay : $today] ?? ucfirst($isOpen ? $activeDay : $today),
            'start' => substr($start, 0, 5),
            'end' => substr($end, 0, 5),
            'timezone' => $timezone,
            'source' => 'profile_apotek',
            'label' => $label,
            'local_now' => $localNow->format('Y-m-d H:i:s'),
        ];
    }

    public function operationalClosureMessage(BranchModel $branch, ?array $state = null): string
    {
        $state = $state ?: $this->operationalState($branch);

        if (! $state['is_configured']) {
            return 'Kasir/POS '.$branch->name.' tidak dapat dibuka karena jam operasional belum diatur di Profile Apotek.';
        }

        if ($state['is_closed_day']) {
            return 'Kasir/POS '.$branch->name.' tidak dapat dibuka karena Profile Apotek menetapkan '
                .$state['day_label'].' sebagai hari tutup.';
        }

        return 'Kasir/POS '.$branch->name.' hanya dapat dibuka sesuai jam operasional Profile Apotek: '
            .$state['schedule_day_label'].' '.$state['label'].' ('.$state['timezone'].').';
    }

    public function assertOperational(BranchModel|int $branch, ?User $user = null): BranchModel
    {
        $branch = $branch instanceof BranchModel ? $branch : $this->accessibleBranch($branch, $user);
        $state = $this->operationalState($branch);

        if (! $state['is_open']) {
            throw ValidationException::withMessages([
                'operational_hours' => $this->operationalClosureMessage($branch, $state),
            ]);
        }

        return $branch;
    }

    public function currentShift(?User $user, int $branchId): ?CashierShiftModel
    {
        if (! $user) {
            return null;
        }

        return CashierShiftModel::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    public function requireOpenShift(int $branchId, ?User $user = null): CashierShiftModel
    {
        $user = $user ?: Auth::user();
        $branch = $this->assertOperational($branchId, $user);
        $shift = $this->currentShift($user, $branch->id);

        if (! $shift) {
            throw ValidationException::withMessages([
                'cashier_shift' => 'Buka kasir dan isi modal awal sebelum memulai transaksi POS.',
            ]);
        }

        return $shift;
    }

    public function openShift(int $branchId, float $openingAmount, ?string $notes = null, ?User $user = null): CashierShiftModel
    {
        $user = $user ?: Auth::user();
        $branch = $this->assertOperational($branchId, $user);

        return DB::transaction(function () use ($branch, $openingAmount, $notes, $user) {
            $existing = CashierShiftModel::query()
                ->where('branch_id', $branch->id)
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'cashier_shift' => 'Kasir Anda pada cabang ini sudah terbuka.',
                ]);
            }

            $shift = CashierShiftModel::create([
                'branch_id' => $branch->id,
                'user_id' => $user->id,
                'shift_number' => $this->nextShiftNumber($branch),
                'status' => 'open',
                'opening_amount' => round(max(0, $openingAmount), 2),
                'expected_cash' => round(max(0, $openingAmount), 2),
                'opening_notes' => $this->nullableText($notes),
                'opened_at' => now(),
            ]);

            return $shift->fresh(['branch', 'user']);
        });
    }

    public function addMovement(
        int $branchId,
        string $type,
        float $amount,
        string $description,
        ?User $user = null
    ): CashierShiftModel {
        $user = $user ?: Auth::user();
        $this->assertOperational($branchId, $user);

        if (! in_array($type, ['cash_in', 'cash_out'], true) || $amount <= 0 || trim($description) === '') {
            throw ValidationException::withMessages([
                'cash_movement' => 'Jenis, nominal, dan keterangan mutasi kas tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($branchId, $type, $amount, $description, $user) {
            $shift = $this->lockedOpenShift($branchId, $user);

            if ($type === 'cash_out' && round($amount, 2) > $this->summary($shift)['expected_cash']) {
                throw ValidationException::withMessages([
                    'amount' => 'Kas keluar melebihi kas yang seharusnya tersedia pada shift ini.',
                ]);
            }

            CashierCashMovementModel::create([
                'cashier_shift_id' => $shift->id,
                'type' => $type,
                'amount' => round($amount, 2),
                'description' => trim($description),
                'created_by' => $user->id,
                'occurred_at' => now(),
            ]);

            return $shift->fresh(['branch', 'user']);
        });
    }

    public function closeShift(int $branchId, float $actualCash, ?string $notes = null, ?User $user = null): CashierShiftModel
    {
        $user = $user ?: Auth::user();
        $this->accessibleBranch($branchId, $user, false);

        return DB::transaction(function () use ($branchId, $actualCash, $notes, $user) {
            $shift = $this->lockedOpenShift($branchId, $user);
            $summary = $this->summary($shift);
            $actualCash = round(max(0, $actualCash), 2);

            $shift->forceFill([
                'status' => 'closed',
                'cash_sales' => $summary['cash_sales'],
                'cash_in_total' => $summary['cash_in_total'],
                'cash_out_total' => $summary['cash_out_total'],
                'expected_cash' => $summary['expected_cash'],
                'actual_cash' => $actualCash,
                'cash_difference' => round($actualCash - $summary['expected_cash'], 2),
                'closing_notes' => $this->nullableText($notes),
                'closed_at' => now(),
            ])->save();

            return $shift->fresh(['branch', 'user']);
        });
    }

    public function summary(CashierShiftModel $shift): array
    {
        if ($shift->status === 'closed') {
            return [
                'opening_amount' => (float) $shift->opening_amount,
                'cash_sales' => (float) $shift->cash_sales,
                'cash_in_total' => (float) $shift->cash_in_total,
                'cash_out_total' => (float) $shift->cash_out_total,
                'expected_cash' => (float) $shift->expected_cash,
                'actual_cash' => $shift->actual_cash !== null ? (float) $shift->actual_cash : null,
                'cash_difference' => $shift->cash_difference !== null ? (float) $shift->cash_difference : null,
                'transaction_count' => $shift->transactions()->where('status', 'completed')->count(),
            ];
        }

        $cashTendered = (float) PenjualanPaymentModel::query()
            ->where('metode', 'tunai')
            ->whereHas('transaction', fn (Builder $query) => $query
                ->where('cashier_shift_id', $shift->id)
                ->whereIn('status', ['completed', 'cancelled']))
            ->sum('amount');
        $change = (float) PenjualanTransactionModel::query()
            ->where('cashier_shift_id', $shift->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->sum('kembalian');
        $cashSales = round(max(0, $cashTendered - $change), 2);
        $cashIn = (float) $shift->movements()->where('type', 'cash_in')->sum('amount');
        $cashOut = (float) $shift->movements()->where('type', 'cash_out')->sum('amount');
        $expected = round((float) $shift->opening_amount + $cashSales + $cashIn - $cashOut, 2);

        return [
            'opening_amount' => (float) $shift->opening_amount,
            'cash_sales' => $cashSales,
            'cash_in_total' => round($cashIn, 2),
            'cash_out_total' => round($cashOut, 2),
            'expected_cash' => $expected,
            'actual_cash' => null,
            'cash_difference' => null,
            'transaction_count' => $shift->transactions()->where('status', 'completed')->count(),
        ];
    }

    public function payload(?CashierShiftModel $shift): ?array
    {
        if (! $shift) {
            return null;
        }

        $shift->loadMissing(['branch', 'user']);
        $summary = $this->summary($shift);
        $timezone = $this->timezone($shift->branch?->operational_timezone);

        return [
            'id' => $shift->id,
            'shift_number' => $shift->shift_number,
            'status' => $shift->status,
            'branch_id' => $shift->branch_id,
            'branch_name' => $shift->branch?->name ?? '-',
            'user_id' => $shift->user_id,
            'cashier_name' => $shift->user?->name ?? '-',
            'opened_at' => $shift->opened_at?->copy()->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'closed_at' => $shift->closed_at?->copy()->setTimezone($timezone)->format('Y-m-d H:i:s'),
            'opening_notes' => $shift->opening_notes,
            'closing_notes' => $shift->closing_notes,
            ...$summary,
        ];
    }

    public function historyQuery(?User $user = null): Builder
    {
        $user = $user ?: Auth::user();

        return CashierShiftModel::query()
            ->whereIn('branch_id', $this->roleSettings->posBranchIds($user))
            ->with(['branch', 'user']);
    }

    private function lockedOpenShift(int $branchId, User $user): CashierShiftModel
    {
        $shift = CashierShiftModel::query()
            ->where('branch_id', $branchId)
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->lockForUpdate()
            ->first();

        if (! $shift) {
            throw ValidationException::withMessages([
                'cashier_shift' => 'Tidak ada shift kasir aktif pada cabang ini.',
            ]);
        }

        return $shift;
    }

    private function nextShiftNumber(BranchModel $branch): string
    {
        $date = now($this->timezone($branch->operational_timezone))->format('Ymd');
        $prefix = 'KS-'.$date.'-'.str_pad((string) $branch->id, 3, '0', STR_PAD_LEFT).'-';
        $sequence = CashierShiftModel::query()->where('shift_number', 'like', $prefix.'%')->count() + 1;

        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (CashierShiftModel::query()->where('shift_number', $number)->exists());

        return $number;
    }

    private function scheduleForDay(mixed $hours, string $day): ?array
    {
        if (! is_array($hours) || ! isset($hours[$day]) || ! is_array($hours[$day])) {
            return null;
        }

        $schedule = $hours[$day];
        $open = $this->normalizeScheduleTime($schedule['open'] ?? null);
        $close = $this->normalizeScheduleTime($schedule['close'] ?? null);

        if ($open === null || $close === null) {
            return null;
        }

        return [
            'enabled' => filter_var($schedule['enabled'] ?? false, FILTER_VALIDATE_BOOL),
            'open' => $open,
            'close' => $close,
        ];
    }

    private function scheduleIsOpen(?array $schedule, int $nowSeconds): bool
    {
        if (! $schedule || ! $schedule['enabled']) {
            return false;
        }

        $startSeconds = $this->timeToSeconds($schedule['open']);
        $endSeconds = $this->timeToSeconds($schedule['close']);

        if ($startSeconds === $endSeconds) {
            return true;
        }

        if ($startSeconds < $endSeconds) {
            return $nowSeconds >= $startSeconds && $nowSeconds < $endSeconds;
        }

        return $nowSeconds >= $startSeconds;
    }

    private function continuesAfterMidnight(?array $schedule): bool
    {
        return $schedule
            && $schedule['enabled']
            && $this->timeToSeconds($schedule['open']) > $this->timeToSeconds($schedule['close']);
    }

    private function normalizeScheduleTime(mixed $time): ?string
    {
        $time = trim((string) $time);

        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time)
            ? substr($time.':00', 0, 8)
            : null;
    }

    private function timeToSeconds(string $time): int
    {
        [$hours, $minutes, $seconds] = array_map('intval', explode(':', $time));

        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    private function timezone(?string $timezone): string
    {
        $timezone = $timezone ?: self::DEFAULT_TIMEZONE;

        try {
            new DateTimeZone($timezone);

            return $timezone;
        } catch (\Throwable) {
            return self::DEFAULT_TIMEZONE;
        }
    }

    private function nullableText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
