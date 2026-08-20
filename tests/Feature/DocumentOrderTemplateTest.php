<?php

namespace Tests\Feature;

use App\Models\ApotekProfile;
use App\Models\BranchModel;
use App\Services\Menu\Dokumen\DocumentArchiveService;
use Tests\TestCase;

class DocumentOrderTemplateTest extends TestCase
{
    public function test_each_order_template_contains_pharmacy_identity_without_transaction_or_medicine_data(): void
    {
        $branch = (new BranchModel)->forceFill([
            'name' => 'Cabang Pusat',
            'address' => 'Alamat cabang cadangan',
        ]);
        $branch->setRelation('apotekProfile', (new ApotekProfile)->forceFill([
            'name' => 'Apotek Medcare Pusat',
            'address' => 'Jl. Sehat No. 1',
            'city' => 'Jakarta',
            'pharmacist_name' => 'apt. Siti Sehat, S.Farm.',
            'pharmacist_license_number' => 'SIPA-001',
        ]));
        $service = app(DocumentArchiveService::class);

        $expectedTitles = [
            'narkotika' => 'SURAT PESANAN NARKOTIKA',
            'psikotropika' => 'SURAT PESANAN PSIKOTROPIKA',
            'prekursor' => 'SURAT PESANAN OBAT/BAHAN OBAT/PREKURSOR FARMASI*',
        ];

        foreach ($expectedTitles as $type => $title) {
            $html = view('medcare.menu.dokumen.template-surat-pesanan', [
                'branch' => $branch,
                'type' => $type,
                'meta' => $service->typeMetadata($type),
                'autoPrint' => false,
            ])->render();

            $this->assertStringContainsString($title, $html);
            $this->assertStringContainsString('Apotek Medcare Pusat', $html);
            $this->assertStringContainsString('Jl. Sehat No. 1', $html);
            $this->assertStringContainsString('apt. Siti Sehat, S.Farm.', $html);
            $this->assertStringContainsString('SIPA-001', $html);
            $this->assertStringContainsString('Template kosong', $html);
            $this->assertStringNotContainsString('Morphine 10 mg', $html);
            $this->assertStringNotContainsString('PT Distributor Sehat', $html);
            $this->assertSame(1, substr_count($html, 'class="order-sheet"'));
        }
    }

    public function test_each_template_has_a_professional_blank_table_with_the_required_number_of_rows(): void
    {
        $branch = (new BranchModel)->forceFill(['name' => 'Cabang Template']);
        $branch->setRelation('apotekProfile', null);
        $service = app(DocumentArchiveService::class);

        $narcotic = $this->renderTemplate($service, $branch, 'narkotika');
        $psychotropic = $this->renderTemplate($service, $branch, 'psikotropika');
        $precursor = $this->renderTemplate($service, $branch, 'prekursor');

        $this->assertStringContainsString('class="medicine-table"', $narcotic);
        $this->assertSame(1, substr_count($narcotic, 'class="empty-row is-single-item"'));
        $this->assertSame(5, substr_count($psychotropic, 'class="empty-row"'));
        $this->assertSame(5, substr_count($precursor, 'class="empty-row"'));
    }

    private function renderTemplate(
        DocumentArchiveService $service,
        BranchModel $branch,
        string $type,
    ): string {
        return view('medcare.menu.dokumen.template-surat-pesanan', [
            'branch' => $branch,
            'type' => $type,
            'meta' => $service->typeMetadata($type),
            'autoPrint' => false,
        ])->render();
    }
}
