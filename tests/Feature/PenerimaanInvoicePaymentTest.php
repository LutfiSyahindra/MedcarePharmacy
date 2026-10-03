<?php

namespace Tests\Feature;

use App\Models\BranchModel;
use App\Models\DistributorModel;
use App\Models\MasterObatModel;
use App\Models\Menu\PembelianPenerimaan\PembelianModel;
use App\Models\Menu\PembelianPenerimaan\PenerimaanBarangModel;
use App\Models\SatuansModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PenerimaanInvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    private PembelianModel $purchaseOrder;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $user = User::factory()->create();
        $branch = BranchModel::create([
            'code' => 'CB-INVOICE', 'name' => 'Cabang Faktur', 'is_active' => true,
        ]);
        $user->branches()->attach($branch->id);
        $supplier = DistributorModel::create([
            'kode' => 'DST-INVOICE', 'nama' => 'Supplier Faktur', 'is_active' => true,
        ]);
        $unit = SatuansModel::create([
            'kode' => 'PCS-INVOICE', 'nama' => 'PCS', 'is_active' => true,
        ]);
        $medicine = MasterObatModel::create([
            'kode_obat' => 'OBT-INVOICE', 'nama_obat' => 'Obat Faktur',
            'satuan_id' => $unit->id, 'distributor_id' => $supplier->id, 'is_active' => true,
        ]);
        $this->purchaseOrder = PembelianModel::create([
            'no_po' => 'PO-INVOICE', 'distributor_id' => $supplier->id,
            'branch_id' => $branch->id, 'tanggal_po' => '2026-10-04',
            'status' => 'approved', 'created_by' => $user->id,
        ]);
        $this->purchaseOrder->details()->create([
            'obat_id' => $medicine->id, 'qty' => 10, 'harga_estimasi' => 100000,
            'diskon_1' => 10, 'diskon_2' => 5, 'diskon_3' => 2, 'ppn' => 11,
        ]);
        $this->actingAs($user);
    }

    public static function fullyPaidInvoices(): array
    {
        return [
            'tiered discounts with decimal tax' => [[2], 100000, 0, 200000, 32420, 18433.80, 186013.80],
            'decimal purchase price' => [[3], 1234.56, 0, 3703.68, 600.37, 341.36, 3444.67],
            'multiple batches with fractional quantities and allocated cost' => [
                [0.2, 0.3], 1234.56, 1, 617.28, 100.06, 56.90, 574.17,
            ],
        ];
    }

    #[DataProvider('fullyPaidInvoices')]
    public function test_full_invoice_payment_can_be_saved_and_updated_without_rounding_errors(
        array $quantities,
        float $price,
        float $additionalCost,
        float $subtotal,
        float $discount,
        float $tax,
        float $total
    ): void {
        $this->purchaseOrder->update(['biaya_pengiriman' => $additionalCost]);
        $payload = $this->invoicePayload($quantities, $price, $total);

        $this->postJson(route('penerimaan.store'), $payload)->assertOk();

        $receipt = PenerimaanBarangModel::firstOrFail();
        $this->assertEquals($subtotal, (float) $receipt->subtotal);
        $this->assertEquals($discount, (float) $receipt->total_diskon);
        $this->assertEquals($tax, (float) $receipt->total_ppn);
        $this->assertEquals($total, (float) $receipt->total_faktur);
        $this->assertEquals($total, (float) $receipt->jumlah_dibayar);
        $this->assertEquals(0, (float) $receipt->sisa_hutang);
        $this->assertSame('lunas', $receipt->status_pembayaran);

        $payload['jumlah_dibayar'] = round($total / 2, 2);
        $this->putJson(route('penerimaan.update', $receipt->id), $payload)->assertOk();
        $receipt->refresh();
        $this->assertEquals($payload['jumlah_dibayar'], (float) $receipt->jumlah_dibayar);
        $this->assertEquals(round($total - $payload['jumlah_dibayar'], 2), (float) $receipt->sisa_hutang);
        $this->assertSame('sebagian', $receipt->status_pembayaran);

        $payload['jumlah_dibayar'] = $total;
        $this->putJson(route('penerimaan.update', $receipt->id), $payload)->assertOk();
        $this->assertSame('lunas', $receipt->fresh()->status_pembayaran);
        $this->assertEquals(0, (float) $receipt->fresh()->sisa_hutang);
    }

    public function test_half_cent_discount_rounding_allows_full_payment(): void
    {
        $this->purchaseOrder->details()->update(['diskon_1' => 5, 'diskon_2' => 0, 'diskon_3' => 0]);
        $this->postJson(route('penerimaan.store'), $this->invoicePayload([0.2], 3.5, 0.74))->assertOk();

        $receipt = PenerimaanBarangModel::firstOrFail();
        $this->assertEquals(0.74, (float) $receipt->total_faktur);
        $this->assertEquals(0.74, (float) $receipt->jumlah_dibayar);
        $this->assertEquals(0, (float) $receipt->sisa_hutang);
        $this->assertSame('lunas', $receipt->status_pembayaran);
    }

    public function test_payment_exceeding_the_computed_invoice_by_one_cent_is_rejected(): void
    {
        $payload = $this->invoicePayload([3], 1234.56, 3444.68);
        $payload['total_faktur'] = 100000;

        $this->postJson(route('penerimaan.store'), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('jumlah_dibayar');
        $this->assertDatabaseCount('penerimaan_barang', 0);
        $this->assertDatabaseCount('penerimaan_barang_detail', 0);

        $payload['jumlah_dibayar'] = 3444.67;
        $this->postJson(route('penerimaan.store'), $payload)->assertOk();
        $receipt = PenerimaanBarangModel::firstOrFail();
        $payload['jumlah_dibayar'] = 3444.68;
        $this->putJson(route('penerimaan.update', $receipt->id), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('jumlah_dibayar');
        $this->assertEquals(3444.67, (float) $receipt->fresh()->jumlah_dibayar);
    }

    private function invoicePayload(array $quantities, float $price, float $payment): array
    {
        $detail = $this->purchaseOrder->details()->firstOrFail();
        $rowCount = count($quantities);

        return [
            'nomor_penerimaan' => 'PB-INVOICE',
            'purchase_order_id' => $this->purchaseOrder->id,
            'nomor_faktur' => 'INV-INVOICE',
            'tanggal_penerimaan' => '04-10-2026',
            'tanggal_faktur' => '04-10-2026',
            'jumlah_dibayar' => $payment,
            'purchase_order_detail_id' => array_fill(0, $rowCount, $detail->id),
            'obat_id' => array_fill(0, $rowCount, $detail->obat_id),
            'qty_diterima' => $quantities,
            'no_batch' => array_map(fn ($index) => 'BATCH-INVOICE-'.$index, array_keys($quantities)),
            'expired_date' => array_fill(0, $rowCount, '04-10-2027'),
            'harga_beli' => array_fill(0, $rowCount, $price),
            'ppn' => array_fill(0, $rowCount, 11),
        ];
    }
}
