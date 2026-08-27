<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\Penjualan\PenjualanTransactionDetailModel;
use App\Models\Menu\Penjualan\PenjualanTransactionModel;
use App\Models\Menu\Stok\KartuStokModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InventoryAnalysisTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private BranchModel $branch;

    private MasterObatModel $fastMedicine;

    private MasterObatModel $deadMedicine;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-26 10:00:00');

        $this->user = User::factory()->create();
        $this->branch = BranchModel::create([
            'code' => 'INV-01',
            'name' => 'Cabang Inventory',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($this->branch->id);

        $this->fastMedicine = MasterObatModel::create([
            'kode_obat' => 'INV-FAST',
            'nama_obat' => 'Obat Aktif Analisis',
            'stok_minimum' => 5,
            'harga_beli' => 10000,
            'is_active' => true,
        ]);
        $this->deadMedicine = MasterObatModel::create([
            'kode_obat' => 'INV-DEAD',
            'nama_obat' => 'Obat Mati Analisis',
            'stok_minimum' => 2,
            'harga_beli' => 20000,
            'is_active' => true,
        ]);

        $fastBatch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $this->fastMedicine->id,
            'no_batch' => 'INV-B-FAST',
            'expired_date' => today()->addYear(),
            'qty' => 3,
            'harga_beli' => 10000,
            'harga_jual' => 25000,
            'created_at' => now()->subDays(120),
            'updated_at' => now()->subDays(40),
            'last_movement_at' => now()->subDays(40),
        ]);
        StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $this->deadMedicine->id,
            'no_batch' => 'INV-B-DEAD',
            'expired_date' => today()->addMonths(6),
            'qty' => 10,
            'harga_beli' => 20000,
            'harga_jual' => 35000,
            'created_at' => now()->subDays(120),
            'updated_at' => now()->subDays(120),
        ]);

        KartuStokModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $this->fastMedicine->id,
            'stok_batch_id' => $fastBatch->id,
            'tanggal_mutasi' => now()->subDays(40),
            'jenis_mutasi' => 'penjualan',
            'qty_masuk' => 0,
            'qty_keluar' => 8,
            'saldo_batch' => 3,
            'saldo_total' => 3,
            'no_batch' => $fastBatch->no_batch,
            'harga_beli' => 10000,
            'created_by' => $this->user->id,
        ]);

        $this->storeSale($this->fastMedicine, 8, 200000, 'INV-SALE-FAST');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_inventory_analysis_menu_requires_authentication_and_renders_all_features(): void
    {
        $this->get(route('analisisPersediaan.index', ['analysis' => 'pergerakan-stok']))
            ->assertRedirect(route('login'));

        $this->actingAs($this->user)
            ->get(route('analisisPersediaan.index', ['analysis' => 'pergerakan-stok']))
            ->assertOk()
            ->assertSee('Analisis Persediaan')
            ->assertSee('Pergerakan Stok')
            ->assertSee('Pareto ABC')
            ->assertSee('Stok Hampir Habis')
            ->assertSee('Slow Moving')
            ->assertSee('Dead Stock')
            ->assertSee('Saran Pembelian')
            ->assertSee('Parameter aktif')
            ->assertSee('Insight prioritas')
            ->assertSee('iaSummaryGrid', false)
            ->assertSee('iaDistributionDonut', false)
            ->assertSee('iaFilterToggle', false)
            ->assertSee('iaClearSort', false)
            ->assertSee('iaTableBody', false);

        $this->get(route('analisisPersediaan.index', ['analysis' => 'saran-pembelian']))
            ->assertOk()
            ->assertSee('iaCreatePo', false)
            ->assertSee('iaCreatePoModal', false)
            ->assertSee('ia-po-line-distributor', false)
            ->assertSee('ia-po-estimated-price', false)
            ->assertSee('Pilih distributor, sesuaikan harga estimasi, dan isi diskon setiap produk sebelum PO dibuat.')
            ->assertSee(route('analisisPersediaan.purchaseOrders.store'), false);
    }

    public function test_analysis_endpoints_calculate_movement_pareto_stock_health_and_purchase_advice(): void
    {
        $query = ['date_start' => '2026-05-29', 'date_end' => '2026-08-26'];

        $movement = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', ['analysis' => 'pergerakan-stok', ...$query]));
        $movement->assertOk()
            ->assertJsonPath('analysis.meta.branch_label', $this->branch->name)
            ->assertJsonPath('analysis.rows.0.name', $this->fastMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.qty_out', 8)
            ->assertJsonPath('analysis.rows.0.opening_stock', 11)
            ->assertJsonPath('analysis.rows.0.ending_stock', 3)
            ->assertJsonPath('analysis.rows.0.average_stock', 7)
            ->assertJsonPath('analysis.rows.0.stock_turnover', 1.14);

        $pareto = $this->getJson(route('analisisPersediaan.data', ['analysis' => 'pareto-abc', ...$query]));
        $pareto->assertOk()
            ->assertJsonPath('analysis.rows.0.name', $this->fastMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.abc_class', 'A')
            ->assertJsonPath('analysis.rows.0.revenue', 200000);

        $lowStock = $this->getJson(route('analisisPersediaan.data', ['analysis' => 'stok-hampir-habis', ...$query]));
        $lowStock->assertOk()
            ->assertJsonPath('analysis.rows.0.name', $this->fastMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.shortage', 2);

        $slow = $this->getJson(route('analisisPersediaan.data', ['analysis' => 'slow-moving', ...$query, 'slow_days' => 30, 'dead_days' => 90]));
        $slow->assertOk()
            ->assertJsonPath('analysis.rows.0.name', $this->fastMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.days_since_out', 40);

        $dead = $this->getJson(route('analisisPersediaan.data', ['analysis' => 'dead-stock', ...$query, 'slow_days' => 30, 'dead_days' => 90]));
        $dead->assertOk()
            ->assertJsonPath('analysis.rows.0.name', $this->deadMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.stock_value', 200000);

        $purchase = $this->getJson(route('analisisPersediaan.data', ['analysis' => 'saran-pembelian', ...$query, 'cover_days' => 30]));
        $purchase->assertOk()
            ->assertJsonPath('analysis.rows.0.name', $this->fastMedicine->nama_obat)
            ->assertJsonPath('analysis.rows.0.suggested_qty', 5)
            ->assertJsonPath('analysis.rows.0.estimated_purchase', 50000)
            ->assertJsonMissingPath('analysis.rows.0.supplier')
            ->assertJsonMissingPath('analysis.rows.0.distributor_id');
    }

    public function test_purchase_suggestions_can_create_a_purchase_order_and_recalculate_before_a_repeat_request(): void
    {
        $distributor = DistributorModel::create([
            'kode' => 'DIST-SUGGESTION',
            'nama' => 'Distributor Saran Pembelian',
            'is_active' => true,
        ]);
        $purchaseUnit = SatuansModel::create([
            'kode' => 'STR-SUGGESTION',
            'nama' => 'Strip Saran',
            'is_active' => true,
        ]);
        $this->fastMedicine->update([
            'satuan_id' => $purchaseUnit->id,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $this->fastMedicine->id,
            'satuan_id' => $purchaseUnit->id,
            'konversi' => 2,
            'is_default' => true,
        ]);
        $payload = [
            'branch_id' => $this->branch->id,
            'date_start' => '2026-05-29',
            'date_end' => '2026-08-26',
            'cover_days' => 30,
            'lead_days' => 7,
            'medicine_ids' => [$this->fastMedicine->id],
            'items' => [[
                'medicine_id' => $this->fastMedicine->id,
                'distributor_id' => $distributor->id,
                'harga_estimasi' => 25000,
                'diskon_1' => 10,
                'diskon_2' => 5,
                'diskon_3' => 0,
            ]],
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('analisisPersediaan.purchaseOrders.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('orders.0.item_count', 1)
            ->assertJsonPath('orders.0.distributor_id', $distributor->id)
            ->assertJsonPath('skipped_count', 0)
            ->assertJsonPath('redirect_url', route('pembelian.pembelian'));

        $purchaseOrder = PembelianModel::query()->sole();
        $this->assertSame($this->branch->id, $purchaseOrder->branch_id);
        $this->assertSame($distributor->id, $purchaseOrder->distributor_id);
        $this->assertSame('waiting_approval', $purchaseOrder->status);
        $this->assertNull($purchaseOrder->approved_by);
        $this->assertStringContainsString('Saran Pembelian', (string) $purchaseOrder->catatan);

        $detail = PembelianDetailModel::query()->sole();
        $this->assertSame($this->fastMedicine->id, $detail->obat_id);
        $this->assertSame($conversion->id, $detail->satuan_konversi);
        $this->assertSame('3.00', $detail->qty);
        $this->assertSame('25000.00', $detail->harga_estimasi);
        $this->assertSame('10.00', $detail->diskon_1);
        $this->assertSame('5.00', $detail->diskon_2);
        $this->assertSame('0.00', $detail->diskon_3);
        $this->assertSame('64125.00', $detail->subtotal);
        $this->assertSame(64125.0, (float) $purchaseOrder->total_estimasi);
        $this->assertFalse($detail->is_oot);

        $this->postJson(route('analisisPersediaan.purchaseOrders.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('medicine_ids');

        $this->assertDatabaseCount('purchase_orders', 1);
    }

    public function test_purchase_suggestions_create_separate_orders_for_distributors_selected_per_medicine(): void
    {
        $firstDistributor = DistributorModel::create([
            'kode' => 'DIST-MANUAL-A',
            'nama' => 'Distributor Manual A',
            'is_active' => true,
        ]);
        $secondDistributor = DistributorModel::create([
            'kode' => 'DIST-MANUAL-B',
            'nama' => 'Distributor Manual B',
            'is_active' => true,
        ]);
        $purchaseUnit = SatuansModel::create([
            'kode' => 'BOX-MANUAL',
            'nama' => 'Box Manual',
            'is_active' => true,
        ]);
        $secondMedicine = MasterObatModel::create([
            'kode_obat' => 'INV-MANUAL-DIST',
            'nama_obat' => 'Obat Distributor Berbeda',
            'satuan_id' => $purchaseUnit->id,
            'stok_minimum' => 5,
            'harga_beli' => 5000,
            'is_active' => true,
        ]);
        $this->fastMedicine->update(['satuan_id' => $purchaseUnit->id]);

        foreach ([$this->fastMedicine, $secondMedicine] as $medicine) {
            KonversiSatuanModel::create([
                'obat_id' => $medicine->id,
                'satuan_id' => $purchaseUnit->id,
                'konversi' => 1,
                'is_default' => true,
            ]);
        }
        $this->storeSale($secondMedicine, 8, 80000, 'INV-SALE-MANUAL-DIST');

        $response = $this->actingAs($this->user)->postJson(
            route('analisisPersediaan.purchaseOrders.store'),
            [
                'branch_id' => $this->branch->id,
                'date_start' => '2026-05-29',
                'date_end' => '2026-08-26',
                'cover_days' => 30,
                'lead_days' => 7,
                'medicine_ids' => [$this->fastMedicine->id, $secondMedicine->id],
                'items' => [
                    [
                        'medicine_id' => $this->fastMedicine->id,
                        'distributor_id' => $firstDistributor->id,
                        'harga_estimasi' => 10000,
                        'diskon_1' => 0,
                        'diskon_2' => 0,
                        'diskon_3' => 0,
                    ],
                    [
                        'medicine_id' => $secondMedicine->id,
                        'distributor_id' => $secondDistributor->id,
                        'harga_estimasi' => 5000,
                        'diskon_1' => 0,
                        'diskon_2' => 0,
                        'diskon_3' => 0,
                    ],
                ],
            ]
        );

        $response->assertCreated()
            ->assertJsonCount(2, 'orders')
            ->assertJsonPath('skipped_count', 0);

        $orders = PembelianModel::query()->with('details')->get()->keyBy('distributor_id');
        $this->assertCount(2, $orders);
        $this->assertSame(
            [$this->fastMedicine->id],
            $orders->get($firstDistributor->id)->details->pluck('obat_id')->all()
        );
        $this->assertSame(
            [$secondMedicine->id],
            $orders->get($secondDistributor->id)->details->pluck('obat_id')->all()
        );
    }

    public function test_creating_purchase_orders_from_an_aggregate_requires_one_selected_branch(): void
    {
        $distributor = DistributorModel::create([
            'kode' => 'DIST-AGGREGATE',
            'nama' => 'Distributor Aggregate',
            'is_active' => true,
        ]);
        $otherBranch = BranchModel::create([
            'code' => 'INV-02',
            'name' => 'Cabang Inventory Dua',
            'is_active' => true,
        ]);
        $this->user->branches()->attach($otherBranch->id);

        $this->actingAs($this->user)
            ->postJson(route('analisisPersediaan.purchaseOrders.store'), [
                'date_start' => '2026-05-29',
                'date_end' => '2026-08-26',
                'cover_days' => 30,
                'lead_days' => 7,
                'medicine_ids' => [$this->fastMedicine->id],
                'items' => [[
                    'medicine_id' => $this->fastMedicine->id,
                    'distributor_id' => $distributor->id,
                    'harga_estimasi' => 10000,
                    'diskon_1' => 0,
                    'diskon_2' => 0,
                    'diskon_3' => 0,
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');

        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_user_cannot_analyze_an_unassigned_branch(): void
    {
        $foreignBranch = BranchModel::create([
            'code' => 'INV-FOREIGN',
            'name' => 'Cabang Asing',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson(route('analisisPersediaan.data', [
                'analysis' => 'pergerakan-stok',
                'branch_id' => $foreignBranch->id,
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('branch_id');
    }

    public function test_pareto_abc_uses_the_current_cumulative_percentage_for_class_boundaries(): void
    {
        foreach ([
            ['code' => 'ABC-30', 'name' => 'Kontributor 30 Persen', 'revenue' => 150000],
            ['code' => 'ABC-20', 'name' => 'Kontributor 20 Persen', 'revenue' => 100000],
            ['code' => 'ABC-10', 'name' => 'Kontributor 10 Persen', 'revenue' => 50000],
        ] as $item) {
            $medicine = MasterObatModel::create([
                'kode_obat' => $item['code'],
                'nama_obat' => $item['name'],
                'stok_minimum' => 0,
                'harga_beli' => 10000,
                'is_active' => true,
            ]);

            $this->storeSale($medicine, 1, $item['revenue'], 'SALE-'.$item['code']);
        }

        $response = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', [
            'analysis' => 'pareto-abc',
            'date_start' => '2026-05-29',
            'date_end' => '2026-08-26',
        ]));

        $response->assertOk()
            ->assertJsonPath('analysis.rows.0.revenue', 200000)
            ->assertJsonPath('analysis.rows.0.cumulative', 40)
            ->assertJsonPath('analysis.rows.0.abc_class', 'A')
            ->assertJsonPath('analysis.rows.1.revenue', 150000)
            ->assertJsonPath('analysis.rows.1.cumulative', 70)
            ->assertJsonPath('analysis.rows.1.abc_class', 'A')
            ->assertJsonPath('analysis.rows.2.revenue', 100000)
            ->assertJsonPath('analysis.rows.2.cumulative', 90)
            ->assertJsonPath('analysis.rows.2.abc_class', 'B')
            ->assertJsonPath('analysis.rows.3.revenue', 50000)
            ->assertJsonPath('analysis.rows.3.cumulative', 100)
            ->assertJsonPath('analysis.rows.3.abc_class', 'C')
            ->assertJsonPath('analysis.distribution.0.value', 350000)
            ->assertJsonPath('analysis.distribution.1.value', 100000)
            ->assertJsonPath('analysis.distribution.2.value', 50000);
    }

    public function test_stock_turnover_uses_average_period_stock_when_ending_stock_is_zero(): void
    {
        $medicine = MasterObatModel::create([
            'kode_obat' => 'IBU-200',
            'nama_obat' => 'Ibuprofen 200 mg',
            'stok_minimum' => 0,
            'harga_beli' => 500,
            'is_active' => true,
        ]);
        $batch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'IBU-200-01',
            'expired_date' => today()->addYear(),
            'qty' => 0,
            'harga_beli' => 500,
            'harga_jual' => 1000,
            'last_movement_at' => now()->subDay(),
        ]);
        KartuStokModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'stok_batch_id' => $batch->id,
            'tanggal_mutasi' => now()->subDay(),
            'jenis_mutasi' => 'penjualan',
            'qty_masuk' => 0,
            'qty_keluar' => 300,
            'saldo_batch' => 0,
            'saldo_total' => 0,
            'no_batch' => $batch->no_batch,
            'harga_beli' => 500,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', [
            'analysis' => 'pergerakan-stok',
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-26',
            'search' => 'IBU-200',
        ]));

        $response->assertOk()
            ->assertJsonPath('analysis.rows.0.opening_stock', 300)
            ->assertJsonPath('analysis.rows.0.ending_stock', 0)
            ->assertJsonPath('analysis.rows.0.average_stock', 150)
            ->assertJsonPath('analysis.rows.0.stock_turnover', 2);
    }

    public function test_stock_turnover_is_null_when_average_period_stock_is_zero(): void
    {
        $medicine = MasterObatModel::create([
            'kode_obat' => 'TURN-NULL',
            'nama_obat' => 'Obat Habis Dalam Periode',
            'stok_minimum' => 0,
            'harga_beli' => 500,
            'is_active' => true,
        ]);
        $batch = StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'TURN-NULL-01',
            'expired_date' => today()->addYear(),
            'qty' => 0,
            'harga_beli' => 500,
            'harga_jual' => 1000,
            'last_movement_at' => now()->subDay(),
        ]);

        foreach ([
            ['jenis_mutasi' => 'penerimaan', 'qty_masuk' => 10, 'qty_keluar' => 0, 'saldo_total' => 10],
            ['jenis_mutasi' => 'penjualan', 'qty_masuk' => 0, 'qty_keluar' => 10, 'saldo_total' => 0],
        ] as $index => $movement) {
            KartuStokModel::create([
                'branch_id' => $this->branch->id,
                'obat_id' => $medicine->id,
                'stok_batch_id' => $batch->id,
                'tanggal_mutasi' => now()->subDays(2 - $index),
                'saldo_batch' => $movement['saldo_total'],
                'no_batch' => $batch->no_batch,
                'harga_beli' => 500,
                'created_by' => $this->user->id,
                ...$movement,
            ]);
        }

        $response = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', [
            'analysis' => 'pergerakan-stok',
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-26',
            'search' => 'TURN-NULL',
        ]));

        $response->assertOk()
            ->assertJsonPath('analysis.rows.0.opening_stock', 0)
            ->assertJsonPath('analysis.rows.0.ending_stock', 0)
            ->assertJsonPath('analysis.rows.0.average_stock', 0)
            ->assertJsonPath('analysis.rows.0.stock_turnover', null);
    }

    public function test_purchase_suggestions_prioritize_stockout_risk_and_demand_instead_of_purchase_value(): void
    {
        $urgentFast = MasterObatModel::create([
            'kode_obat' => 'PRIORITY-FAST',
            'nama_obat' => 'Priority Fast Demand',
            'stok_minimum' => 5,
            'harga_beli' => 1000,
            'is_active' => true,
        ]);
        $urgentExpensive = MasterObatModel::create([
            'kode_obat' => 'PRIORITY-EXPENSIVE',
            'nama_obat' => 'Priority Expensive Slow Demand',
            'stok_minimum' => 5,
            'harga_beli' => 100000,
            'is_active' => true,
        ]);
        $planned = MasterObatModel::create([
            'kode_obat' => 'PRIORITY-PLANNED',
            'nama_obat' => 'Priority Planned Refill',
            'stok_minimum' => 5,
            'harga_beli' => 2000,
            'is_active' => true,
        ]);
        $noDemand = MasterObatModel::create([
            'kode_obat' => 'PRIORITY-NO-DEMAND',
            'nama_obat' => 'Priority No Demand',
            'stok_minimum' => 50,
            'harga_beli' => 250000,
            'is_active' => true,
        ]);

        StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $planned->id,
            'no_batch' => 'PRIORITY-PLANNED-BATCH',
            'expired_date' => today()->addYear(),
            'qty' => 15,
            'harga_beli' => 2000,
            'harga_jual' => 4000,
        ]);

        $this->storeSale($urgentFast, 90, 180000, 'PRIORITY-SALE-FAST');
        $this->storeSale($urgentExpensive, 45, 9000000, 'PRIORITY-SALE-EXPENSIVE');
        $this->storeSale($planned, 45, 180000, 'PRIORITY-SALE-PLANNED');

        $response = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', [
            'analysis' => 'saran-pembelian',
            'date_start' => '2026-05-29',
            'date_end' => '2026-08-26',
            'cover_days' => 30,
            'lead_days' => 7,
            'search' => 'PRIORITY-',
        ]));

        $response->assertOk()
            ->assertJsonCount(3, 'analysis.rows')
            ->assertJsonPath('analysis.rows.0.name', $urgentFast->nama_obat)
            ->assertJsonPath('analysis.rows.0.priority', 'Tinggi')
            ->assertJsonPath('analysis.rows.0.average_daily_demand', 1)
            ->assertJsonPath('analysis.rows.1.name', $urgentExpensive->nama_obat)
            ->assertJsonPath('analysis.rows.1.priority', 'Tinggi')
            ->assertJsonPath('analysis.rows.2.name', $planned->nama_obat)
            ->assertJsonPath('analysis.rows.2.priority', 'Terencana')
            ->assertJsonMissing(['name' => $noDemand->nama_obat]);
    }

    public function test_purchase_suggestion_uses_usable_stock_and_subtracts_open_purchase_orders(): void
    {
        $medicine = MasterObatModel::create([
            'kode_obat' => 'PURCHASE-NET-NEED',
            'nama_obat' => 'Purchase Net Need Medicine',
            'stok_minimum' => 5,
            'harga_beli' => 1000,
            'is_active' => true,
        ]);
        StokBatchModel::create([
            'branch_id' => $this->branch->id,
            'obat_id' => $medicine->id,
            'no_batch' => 'PURCHASE-EXPIRED-BATCH',
            'expired_date' => today()->subDay(),
            'qty' => 50,
            'harga_beli' => 1000,
            'harga_jual' => 2000,
        ]);
        $this->storeSale($medicine, 90, 180000, 'PURCHASE-NET-SALE');

        $distributor = DistributorModel::create([
            'kode' => 'DIST-NET',
            'nama' => 'Distributor Net Need',
            'is_active' => true,
        ]);
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-PURCHASE-NET',
            'distributor_id' => $distributor->id,
            'branch_id' => $this->branch->id,
            'tanggal_po' => today(),
            'total_estimasi' => 10000,
            'status' => 'approved',
            'created_by' => $this->user->id,
            'approved_by' => $this->user->id,
        ]);
        PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $medicine->id,
            'qty' => 10,
            'harga_estimasi' => 1000,
            'subtotal' => 10000,
        ]);

        $response = $this->actingAs($this->user)->getJson(route('analisisPersediaan.data', [
            'analysis' => 'saran-pembelian',
            'date_start' => '2026-05-29',
            'date_end' => '2026-08-26',
            'cover_days' => 30,
            'lead_days' => 7,
            'search' => 'PURCHASE-NET-NEED',
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'analysis.rows')
            ->assertJsonPath('analysis.rows.0.physical_stock', 50)
            ->assertJsonPath('analysis.rows.0.current_stock', 0)
            ->assertJsonPath('analysis.rows.0.expired_stock', 50)
            ->assertJsonPath('analysis.rows.0.pending_order_qty', 10)
            ->assertJsonPath('analysis.rows.0.target_stock', 35)
            ->assertJsonPath('analysis.rows.0.suggested_qty', 25)
            ->assertJsonPath('analysis.rows.0.priority', 'Tinggi');
    }

    private function storeSale(MasterObatModel $medicine, float $qty, float $revenue, string $number): void
    {
        $sale = PenjualanTransactionModel::create([
            'branch_id' => $this->branch->id,
            'nomor_transaksi' => $number,
            'tanggal_transaksi' => now()->subDays(40),
            'jenis_transaksi' => 'penjualan_bebas',
            'status' => 'completed',
            'payment_status' => 'paid',
            'subtotal_gross' => $revenue,
            'subtotal_net' => $revenue,
            'grand_total' => $revenue,
            'total_bayar' => $revenue,
            'created_by' => $this->user->id,
            'completed_by' => $this->user->id,
            'completed_at' => now()->subDays(40),
        ]);
        PenjualanTransactionDetailModel::create([
            'penjualan_transaction_id' => $sale->id,
            'obat_id' => $medicine->id,
            'kode_obat' => $medicine->kode_obat,
            'nama_obat' => $medicine->nama_obat,
            'satuan_jual' => 'unit',
            'satuan_stok' => 'unit',
            'qty_jual' => $qty,
            'qty_stok' => $qty,
            'harga_jual' => $revenue / $qty,
            'subtotal_gross' => $revenue,
            'subtotal_net' => $revenue,
            'total_line' => $revenue,
        ]);
    }
}
