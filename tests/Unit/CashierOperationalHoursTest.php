<?php

namespace Tests\Unit;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Services\Menu\Penjualan\CashierShiftService;
use App\Services\Settings\Auth\RoleSettingService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CashierOperationalHoursTest extends TestCase
{
    private CashierShiftService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CashierShiftService(new RoleSettingService);
    }

    public function test_cashier_uses_profile_apotek_schedule_instead_of_branch_hours(): void
    {
        $branch = $this->branch($this->hours([
            'thursday' => ['enabled' => false],
        ]));

        $state = $this->service->operationalState(
            $branch,
            Carbon::create(2026, 8, 27, 3, 0, 0, 'UTC')
        );

        $this->assertFalse($state['is_open']);
        $this->assertTrue($state['is_closed_day']);
        $this->assertSame('profile_apotek', $state['source']);
        $this->assertStringContainsString('Profile Apotek menetapkan Kamis sebagai hari tutup',
            $this->service->operationalClosureMessage($branch, $state));
    }

    public function test_cashier_is_open_only_between_profile_start_and_end_times(): void
    {
        $branch = $this->branch($this->hours());

        $this->assertTrue($this->stateAt($branch, 3)['is_open']); // 10:00 WIB
        $this->assertFalse($this->stateAt($branch, 13)['is_open']); // 20:00 WIB, batas tutup
    }

    public function test_profile_schedule_can_continue_after_midnight(): void
    {
        $branch = $this->branch($this->hours([
            'thursday' => ['enabled' => true, 'open' => '20:00', 'close' => '02:00'],
        ]));

        $lateThursday = $this->service->operationalState(
            $branch,
            Carbon::create(2026, 8, 27, 16, 0, 0, 'UTC')
        );
        $earlyFriday = $this->service->operationalState(
            $branch,
            Carbon::create(2026, 8, 27, 18, 0, 0, 'UTC')
        );
        $afterClose = $this->service->operationalState(
            $branch,
            Carbon::create(2026, 8, 27, 20, 0, 0, 'UTC')
        );

        $this->assertTrue($lateThursday['is_open']);
        $this->assertTrue($earlyFriday['is_open']);
        $this->assertSame('thursday', $earlyFriday['schedule_day']);
        $this->assertFalse($afterClose['is_open']);
    }

    public function test_cashier_is_closed_when_profile_hours_are_missing(): void
    {
        $branch = $this->branch(null);

        $state = $this->stateAt($branch, 3);

        $this->assertFalse($state['is_open']);
        $this->assertFalse($state['is_configured']);
        $this->assertSame('Belum diatur di Profile Apotek', $state['label']);
    }

    private function stateAt(BranchModel $branch, int $utcHour): array
    {
        return $this->service->operationalState(
            $branch,
            Carbon::create(2026, 8, 27, $utcHour, 0, 0, 'UTC')
        );
    }

    private function branch(?array $hours): BranchModel
    {
        $branch = new BranchModel([
            'name' => 'Cabang Shift',
            'operational_timezone' => 'Asia/Jakarta',
        ]);

        $profile = $hours === null ? null : new ApotekProfile(['operational_hours' => $hours]);
        $branch->setRelation('apotekProfile', $profile);

        return $branch;
    }

    private function hours(array $overrides = []): array
    {
        $hours = array_fill_keys(
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            ['enabled' => true, 'open' => '08:00', 'close' => '20:00']
        );

        foreach ($overrides as $day => $schedule) {
            $hours[$day] = array_merge($hours[$day], $schedule);
        }

        return $hours;
    }
}
