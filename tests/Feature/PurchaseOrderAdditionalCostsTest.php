<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\KonversiSatuanModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianDetailModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\Menu\Stok\StokBatchModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseOrderAdditionalCostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_with_fourteen_items_requires_complete_units_and_stores_additional_costs(): void
    {
        Notification::fake();

        [$user, $distributor, $unit] = $this->purchaseContext();
        $medicines = [];
        $conversions = [];

        foreach (range(1, 14) as $number) {
            [$medicine, $conversion] = $this->createMedicineWithConversion(
                'OBT-PO-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                'Obat PO '.$number,
                $unit,
                $distributor,
            );
            $medicines[] = $medicine;
            $conversions[] = $conversion;
        }

        $payload = $this->payload('PO-14-ITEM-001', $distributor, $medicines, $conversions);
        $payload['satuan_id'] = array_slice($payload['satuan_id'], 0, 13);

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['satuan_id', 'satuan_id.13']);

        $this->assertDatabaseMissing('purchase_orders', ['no_po' => 'PO-14-ITEM-001']);

        $payload = $this->payload('PO-14-ITEM-001', $distributor, $medicines, $conversions);

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), $payload)
            ->assertOk();

        $purchaseOrder = PembelianModel::where('no_po', 'PO-14-ITEM-001')->firstOrFail();

        $this->assertSame(14, PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)->count());
        $this->assertSame('1500.00', $purchaseOrder->biaya_asuransi);
        $this->assertSame('2500.00', $purchaseOrder->biaya_pengiriman);
        $this->assertSame('18000.00', $purchaseOrder->total_estimasi);

        $payload['biaya_asuransi'] = 2000;
        $payload['biaya_pengiriman'] = 3000;

        $this->actingAs($user)
            ->putJson(route('pembelian.update', $purchaseOrder->id), $payload)
            ->assertOk();

        $purchaseOrder->refresh();

        $this->assertSame(14, $purchaseOrder->details()->count());
        $this->assertSame('2000.00', $purchaseOrder->biaya_asuransi);
        $this->assertSame('3000.00', $purchaseOrder->biaya_pengiriman);
        $this->assertSame('19000.00', $purchaseOrder->total_estimasi);
    }

    public function test_purchase_can_use_base_unit_when_medicine_has_no_conversion_record(): void
    {
        Notification::fake();

        [$user, $distributor, $unit] = $this->purchaseContext();
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-BASE-UNIT',
            'nama_obat' => 'Obat Satuan Dasar',
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('pembelian.store'), [
                'no_po' => 'PO-BASE-UNIT-001',
                'distributor_id' => $distributor->id,
                'tanggal' => '26-09-2026',
                'catatan' => null,
                'total_estimasi' => 1000,
                'obat_id' => [$medicine->id],
                'qty' => [1],
                'harga_estimasi' => [1000],
                'diskon_1' => [0],
                'diskon_2' => [0],
                'diskon_3' => [0],
                'ppn' => [0],
                'subtotal' => [1000],
                'satuan_id' => [0],
            ])
            ->assertOk();

        $purchaseOrder = PembelianModel::where('no_po', 'PO-BASE-UNIT-001')->firstOrFail();
        $detail = PembelianDetailModel::where('purchase_order_id', $purchaseOrder->id)->firstOrFail();

        $this->assertNull($detail->satuan_konversi);
        $this->assertSame('1000.00', $purchaseOrder->total_estimasi);
    }

    public function test_receipt_automatically_allocates_po_costs_and_adds_them_to_selling_prices(): void
    {
        Notification::fake();

        [$user, $distributor, $unit] = $this->purchaseContext();
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole('Admin');

        [$firstMedicine, $firstConversion] = $this->createMedicineWithConversion(
            'OBT-RECEIPT-COST-01',
            'Obat Biaya Penerimaan 1',
            $unit,
            $distributor,
        );
        [$secondMedicine, $secondConversion] = $this->createMedicineWithConversion(
            'OBT-RECEIPT-COST-02',
            'Obat Biaya Penerimaan 2',
            $unit,
            $distributor,
        );
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-RECEIPT-COST-001',
            'distributor_id' => $distributor->id,
            'branch_id' => $user->branches()->firstOrFail()->id,
            'tanggal_po' => '2026-09-26',
            'total_estimasi' => 6400,
            'biaya_asuransi' => 120,
            'biaya_pengiriman' => 280,
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $firstDetail = PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $firstMedicine->id,
            'qty' => 2,
            'harga_estimasi' => 1000,
            'subtotal' => 2000,
            'satuan_konversi' => $firstConversion->id,
        ]);
        $secondDetail = PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $secondMedicine->id,
            'qty' => 2,
            'harga_estimasi' => 2000,
            'subtotal' => 4000,
            'satuan_konversi' => $secondConversion->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('penerimaan.purchaseOrderDetail', $purchaseOrder->id))
            ->assertOk()
            ->assertJsonPath('biaya_asuransi', 120)
            ->assertJsonPath('biaya_pengiriman', 280)
            ->assertJsonPath('total_biaya_tambahan', 400)
            ->assertJsonPath('total_qty_po', 4);

        $this->actingAs($user)
            ->postJson(route('penerimaan.store'), [
                'nomor_penerimaan' => 'PB-RECEIPT-COST-001',
                'purchase_order_id' => $purchaseOrder->id,
                'nomor_faktur' => 'INV-RECEIPT-COST-001',
                'tanggal_penerimaan' => '26-09-2026',
                'tanggal_faktur' => '26-09-2026',
                'biaya_lain' => 9999,
                'purchase_order_detail_id' => [$firstDetail->id, $secondDetail->id],
                'obat_id' => [$firstMedicine->id, $secondMedicine->id],
                'qty_diterima' => [2, 2],
                'stok_batch_id' => [null, null],
                'no_batch' => ['BATCH-COST-01', 'BATCH-COST-02'],
                'expired_date' => ['26-09-2027', '26-09-2027'],
                'harga_beli' => [1000, 2000],
                'ppn' => [0, 0],
            ])
            ->assertOk();

        $receipt = PenerimaanBarangModel::where('nomor_penerimaan', 'PB-RECEIPT-COST-001')->firstOrFail();

        $this->assertEquals(400, (float) $receipt->biaya_lain);
        $this->assertEquals(6400, (float) $receipt->total_faktur);

        $this->actingAs($user)
            ->getJson(route('penerimaan.hargaJualPreview', $receipt->id))
            ->assertOk()
            ->assertJsonPath('header.biaya_lain', 400)
            ->assertJsonPath('details.0.alokasi_biaya_lain', 200)
            ->assertJsonPath('details.0.biaya_lain_satuan_stok', 100)
            ->assertJsonPath('details.0.harga_beli_stok', 1000)
            ->assertJsonPath('details.0.harga_jual', 1100)
            ->assertJsonPath('details.1.alokasi_biaya_lain', 200)
            ->assertJsonPath('details.1.biaya_lain_satuan_stok', 100)
            ->assertJsonPath('details.1.harga_beli_stok', 2000)
            ->assertJsonPath('details.1.harga_jual', 2100);

        // SQLite keeps the original PO status CHECK constraint; skip the unrelated status sync in this pricing test.
        $purchaseOrder->update(['status' => 'waiting_approval']);

        $this->actingAs($user)
            ->putJson(route('penerimaan.post', $receipt->id), [
                'diskon_untuk' => 'pasien',
            ])
            ->assertOk()
            ->assertJsonPath('selling_prices.0.harga_jual', 1100)
            ->assertJsonPath('selling_prices.1.harga_jual', 2100);

        $firstBatch = StokBatchModel::where('no_batch', 'BATCH-COST-01')->firstOrFail();
        $secondBatch = StokBatchModel::where('no_batch', 'BATCH-COST-02')->firstOrFail();

        $this->assertSame(1000.0, (float) $firstBatch->harga_beli);
        $this->assertSame(100.0, (float) $firstBatch->biaya_lain);
        $this->assertSame(2000.0, (float) $firstBatch->qty * (float) $firstBatch->harga_beli);
        $this->assertSame(1100.0, (float) $firstBatch->harga_jual);
        $this->assertSame(2000.0, (float) $secondBatch->harga_beli);
        $this->assertSame(100.0, (float) $secondBatch->biaya_lain);
        $this->assertSame(4000.0, (float) $secondBatch->qty * (float) $secondBatch->harga_beli);
        $this->assertSame(2100.0, (float) $secondBatch->harga_jual);

        $receiptDetails = $receipt->details()->orderBy('id')->get();
        $this->assertSame(1000.0, (float) $receiptDetails[0]->harga_beli_stok);
        $this->assertSame(200.0, (float) $receiptDetails[0]->alokasi_biaya_lain);
        $this->assertSame(100.0, (float) $receiptDetails[0]->biaya_lain_stok);
        $this->assertSame(2000.0, (float) $receiptDetails[1]->harga_beli_stok);
        $this->assertSame(200.0, (float) $receiptDetails[1]->alokasi_biaya_lain);
        $this->assertSame(100.0, (float) $receiptDetails[1]->biaya_lain_stok);
    }

    public function test_same_physical_batch_with_different_purchase_prices_creates_separate_cost_layers(): void
    {
        Notification::fake();

        [$user, $distributor, $unit] = $this->purchaseContext();
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole('Admin');
        [$medicine, $conversion] = $this->createMedicineWithConversion(
            'OBT-COST-LAYER-01',
            'Obat Cost Layer',
            $unit,
            $distributor,
        );
        $purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-COST-LAYER-001',
            'distributor_id' => $distributor->id,
            'branch_id' => $user->branches()->firstOrFail()->id,
            'tanggal_po' => '2026-09-30',
            'total_estimasi' => 2200,
            'status' => 'approved',
            'created_by' => $user->id,
        ]);
        $purchaseDetail = PembelianDetailModel::create([
            'purchase_order_id' => $purchaseOrder->id,
            'obat_id' => $medicine->id,
            'qty' => 2,
            'harga_estimasi' => 1100,
            'subtotal' => 2200,
            'satuan_konversi' => $conversion->id,
        ]);

        foreach ([
            ['suffix' => '01', 'price' => 1000],
            ['suffix' => '02', 'price' => 1200],
        ] as $receiptData) {
            $purchaseOrder->forceFill(['status' => 'approved'])->save();

            $this->actingAs($user)
                ->postJson(route('penerimaan.store'), [
                    'nomor_penerimaan' => 'PB-COST-LAYER-'.$receiptData['suffix'],
                    'purchase_order_id' => $purchaseOrder->id,
                    'nomor_faktur' => 'INV-COST-LAYER-'.$receiptData['suffix'],
                    'tanggal_penerimaan' => '30-09-2026',
                    'tanggal_faktur' => '30-09-2026',
                    'purchase_order_detail_id' => [$purchaseDetail->id],
                    'obat_id' => [$medicine->id],
                    'qty_diterima' => [1],
                    'stok_batch_id' => [null],
                    'no_batch' => ['BATCH-SAMA-001'],
                    'expired_date' => ['30-09-2027'],
                    'harga_beli' => [$receiptData['price']],
                    'ppn' => [0],
                ])
                ->assertOk();

            $receipt = PenerimaanBarangModel::where(
                'nomor_penerimaan',
                'PB-COST-LAYER-'.$receiptData['suffix']
            )->firstOrFail();

            // Avoid the legacy SQLite PO status constraint; this test targets stock layers.
            $purchaseOrder->forceFill(['status' => 'waiting_approval'])->save();

            $this->actingAs($user)
                ->putJson(route('penerimaan.post', $receipt->id), [
                    'diskon_untuk' => 'pasien',
                ])
                ->assertOk();
        }

        $layers = StokBatchModel::where('obat_id', $medicine->id)
            ->where('no_batch', 'BATCH-SAMA-001')
            ->orderBy('harga_beli')
            ->get();

        $this->assertCount(2, $layers);
        $this->assertSame([1000.0, 1200.0], $layers->map(fn ($layer) => (float) $layer->harga_beli)->all());
        $this->assertSame([1.0, 1.0], $layers->map(fn ($layer) => (float) $layer->qty)->all());
        $this->assertNotSame($layers[0]->id, $layers[1]->id);
    }

    /**
     * @return array{0: User, 1: DistributorModel, 2: SatuansModel}
     */
    private function purchaseContext(): array
    {
        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-PO-COST',
            'name' => 'Cabang PO Cost',
            'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);
        $unit = SatuansModel::create([
            'kode' => 'PCS-PO-COST',
            'nama' => 'PCS',
            'is_active' => true,
        ]);
        $distributor = DistributorModel::create([
            'kode' => 'DST-PO-COST',
            'nama' => 'Distributor PO Cost',
            'is_active' => true,
        ]);

        return [$user, $distributor, $unit];
    }

    /**
     * @return array{0: MasterObatModel, 1: KonversiSatuanModel}
     */
    private function createMedicineWithConversion(
        string $code,
        string $name,
        SatuansModel $unit,
        DistributorModel $distributor,
    ): array {
        $medicine = MasterObatModel::create([
            'kode_obat' => $code,
            'nama_obat' => $name,
            'satuan_id' => $unit->id,
            'distributor_id' => $distributor->id,
            'is_active' => true,
        ]);
        $conversion = KonversiSatuanModel::create([
            'obat_id' => $medicine->id,
            'satuan_id' => $unit->id,
            'konversi' => 1,
            'is_default' => true,
        ]);

        return [$medicine, $conversion];
    }

    /**
     * @param  array<int, MasterObatModel>  $medicines
     * @param  array<int, KonversiSatuanModel>  $conversions
     * @return array<string, mixed>
     */
    private function payload(
        string $purchaseOrderNumber,
        DistributorModel $distributor,
        array $medicines,
        array $conversions,
    ): array {
        $itemCount = count($medicines);

        return [
            'no_po' => $purchaseOrderNumber,
            'distributor_id' => $distributor->id,
            'tanggal' => '26-09-2026',
            'catatan' => 'PO dengan empat belas item.',
            'total_estimasi' => 1,
            'biaya_asuransi' => 1500,
            'biaya_pengiriman' => 2500,
            'obat_id' => array_map(fn (MasterObatModel $medicine) => $medicine->id, $medicines),
            'qty' => array_fill(0, $itemCount, 1),
            'harga_estimasi' => array_fill(0, $itemCount, 1000),
            'diskon_1' => array_fill(0, $itemCount, 0),
            'diskon_2' => array_fill(0, $itemCount, 0),
            'diskon_3' => array_fill(0, $itemCount, 0),
            'ppn' => array_fill(0, $itemCount, 0),
            'subtotal' => array_fill(0, $itemCount, 1000),
            'satuan_id' => array_map(fn (KonversiSatuanModel $conversion) => $conversion->id, $conversions),
        ];
    }
}
