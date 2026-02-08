<?php

namespace App\Http\Controllers\Medcare\Menu\PembelianDanPenerimaan\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Menu\PembelianPenerimaan\PembelianService;
use App\Services\Settings\Master\DistributorService;
use App\Services\Settings\Master\MasterObatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\PoCreatedNotification;
use Illuminate\Support\Facades\Notification;


class PembelianController extends Controller
{

    protected $PembelianService, $DistributorService, $MasterObatService;
    public function __construct(PembelianService $PembelianService, DistributorService $DistributorService, MasterObatService $MasterObatService)
    {
        $this->PembelianService = $PembelianService;
        $this->DistributorService = $DistributorService;
        $this->MasterObatService = $MasterObatService;
    }
    /**
     * Display a listing of the resource.
     */
    public function pembelian()
    {
        return view('medcare.menu.pembelianPenerimaan.pembelian.pembelian');
    }
    public function table()
    {
        $Pembelian = $this->PembelianService->getPembelianTable();

        return DataTables::of($Pembelian)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataPembelian) {
            return '
                <button class="btn btn-sm btn-success" onclick="editPembelian(' . $dataPembelian['id'] . ')"> 
                    <i class="mdi mdi-pencil"></i>
                </button> 
                <button class="btn btn-sm btn-info" onclick="lihatPembelian(' . $dataPembelian['id'] . ')"> 
                    <i class="mdi mdi-eye"></i>
                </button> 
                <button class="btn btn-sm btn-danger"  data-mode="edit" onclick="deletePembelian(' . $dataPembelian['id'] . ')">  
                    <i class="mdi mdi-delete"></i>
                </button>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);
    }
    public function generateNoPO()
    {
        $Pembelian = $this->PembelianService->generatePo();
        return response()->json($Pembelian);
    }

    public function getDistributor(){
        $distributor = $this->DistributorService->getDistributor();    
        return response()->json($distributor);
    }

    public function getObat(){
        $obat = $this->MasterObatService->getMasterObat();    
        return response()->json($obat);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_po'            => 'required|string|max:50|unique:purchase_orders,no_po',
            'distributor_id'   => 'required|integer',
            'tanggal'          => 'required|string',
            'catatan'          => 'nullable|string',
            'total_estimasi'   => 'required|numeric',

            'obat_id.*'        => 'required|integer',
            'qty.*'            => 'required|numeric|min:1',
            'harga_estimasi.*' => 'required|numeric|min:0',
            'subtotal.*'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            /** @var \App\Models\User $user */
                $user = Auth::user();
            // Insert header
            $po = $this->PembelianService->createPembelian([
                'no_po'          => $request->no_po,
                'distributor_id' => $request->distributor_id,
                'branch_id'      => $user->branches()->value('branch_id'),
                'tanggal_po'     => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $request->total_estimasi,
                'catatan'        => $request->catatan,
                'created_by'     => Auth::user()->id,
            ]);

            // Insert detail
            foreach ($request->obat_id as $i => $id) {
                $this->PembelianService->createPembelianDetail([
                    'purchase_order_id' => $po->id,
                    'obat_id'           => $id,
                    'qty'               => $request->qty[$i],
                    'harga_estimasi'    => $request->harga_estimasi[$i],
                    'subtotal'          => $request->subtotal[$i],
                ]);
            }

            DB::commit();

            // ======================
            // 🔔 KIRIM NOTIFIKASI
            // ======================
            $approvers = User::role(['admin'])->get();

            if ($approvers->count()) {
                Notification::send(
                    $approvers,
                    new PoCreatedNotification($po)
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pembelian berhasil ditambahkan'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Pembelian = $this->PembelianService->DetailPembelian($id);
        return response()->json($Pembelian);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Pembelian = $this->PembelianService->findByIdPembelian($id);
        Log::info($Pembelian);
        return response()->json($Pembelian);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'no_po'            => 'required|string|max:50|unique:purchase_orders,no_po,' . $id,
            'distributor_id'   => 'required|integer',
            'tanggal'          => 'required|string',
            'catatan'          => 'nullable|string',
            'total_estimasi'   => 'required|numeric',

            'obat_id.*'        => 'required|integer',
            'qty.*'            => 'required|numeric|min:1',
            'harga_estimasi.*' => 'required|numeric|min:0',
            'subtotal.*'       => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // =========================== UPDATE HEADER ============================
            $po = $this->PembelianService->updatePembelian($id, [
                'no_po'          => $request->no_po,
                'distributor_id' => $request->distributor_id,
                'branch_id'      => $user->branches()->value('branch_id'),
                'tanggal_po'     => Carbon::createFromFormat('d-m-Y', $request->tanggal)->format('Y-m-d'),
                'total_estimasi' => $request->total_estimasi,
                'catatan'        => $request->catatan,
            ]);

            // =========================== RESET DETAIL ============================
            // Hapus semua detail PO lama
            $this->PembelianService->deletePembelianDetail($id);

            // =========================== INSERT DETAIL BARU =======================
            foreach ($request->obat_id as $i => $obatId) {
                $this->PembelianService->createPembelianDetail([
                    'purchase_order_id' => $id,
                    'obat_id'           => $obatId,
                    'qty'               => $request->qty[$i],
                    'harga_estimasi'    => $request->harga_estimasi[$i],
                    'subtotal'          => $request->subtotal[$i],
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembelian berhasil diperbarui',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->PembelianService->deletePembelian($id);
            return response()->json([
                'success' => true,
                'message' => 'PO berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            // Tangani jika terjadi kesalahan
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
