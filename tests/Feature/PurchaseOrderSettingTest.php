<?php

namespace Tests\Feature;

use App\Models\DistributorModel;
use App\Models\User;
use App\Support\SidebarPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_choose_distributors_with_manual_po_numbers(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(SidebarPermissions::SETTINGS_PURCHASE_ORDER);
        $manualDistributor = $this->distributor('MAN', 'Distributor Manual');
        $automaticDistributor = $this->distributor('AUT', 'Distributor Otomatis');

        $this->actingAs($user)
            ->get(route('settings.purchase-order.index'))
            ->assertOk()
            ->assertSee('Setting PO')
            ->assertSee('Distributor Manual')
            ->assertSee('Distributor Otomatis');

        $this->actingAs($user)
            ->putJson(route('settings.purchase-order.update'), [
                'manual_distributor_ids' => [$manualDistributor->id],
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('summary.manual', 1)
            ->assertJsonPath('summary.automatic', 1);

        $this->assertDatabaseHas('distributors', [
            'id' => $manualDistributor->id,
            'uses_manual_po_number' => true,
        ]);
        $this->assertDatabaseHas('distributors', [
            'id' => $automaticDistributor->id,
            'uses_manual_po_number' => false,
        ]);
    }

    public function test_saving_an_empty_selection_returns_all_distributors_to_automatic(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(SidebarPermissions::SETTINGS_PURCHASE_ORDER);
        $distributor = $this->distributor('RST', 'Distributor Reset', true);

        $this->actingAs($user)
            ->putJson(route('settings.purchase-order.update'), [
                'manual_distributor_ids' => [],
            ])
            ->assertOk()
            ->assertJsonPath('summary.manual', 0);

        $this->assertDatabaseHas('distributors', [
            'id' => $distributor->id,
            'uses_manual_po_number' => false,
        ]);
    }

    public function test_purchase_order_distributor_endpoint_exposes_numbering_mode(): void
    {
        $user = User::factory()->create();
        $distributor = $this->distributor('API', 'Distributor API', true);

        $this->actingAs($user)
            ->getJson(route('pembelian.getDistributor'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $distributor->id,
                'uses_manual_po_number' => true,
            ]);
    }

    public function test_user_without_setting_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('settings.purchase-order.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->putJson(route('settings.purchase-order.update'), ['manual_distributor_ids' => []])
            ->assertForbidden();
    }

    private function distributor(string $code, string $name, bool $manual = false): DistributorModel
    {
        return DistributorModel::query()->create([
            'kode' => $code,
            'nama' => $name,
            'is_active' => true,
            'uses_manual_po_number' => $manual,
        ]);
    }
}
