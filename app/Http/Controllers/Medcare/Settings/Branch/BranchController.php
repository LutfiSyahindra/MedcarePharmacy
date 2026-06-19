<?php

namespace App\Http\Controllers\Medcare\Settings\Branch;

use App\Http\Controllers\Controller;
use App\Services\Settings\Auth\UserService;
use App\Services\Settings\Master\BranchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{

    protected $UserService, $BranchService;
    public function __construct(UserService $UserService, BranchService $BranchService)
    {
        $this->UserService = $UserService;
        $this->BranchService = $BranchService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('medcare.settings.branch.branch');
    }

    public function updateStatus(Request $request){
        return $this->BranchService->updateStatus($request->id, $request->status);
    }

    public function table()
    {
        $Branch = $this->BranchService->getBranches();

        return DataTables::of($Branch)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataBranch) {
            return '
                <div class="branch-action-group">
                    <button type="button" class="btn branch-action-btn branch-action-edit" title="Edit branch" onclick="editBranch(' . $dataBranch['id'] . ')"> 
                        <i class="mdi mdi-pencil-outline"></i>
                    </button> 
                    <button type="button" class="btn branch-action-btn branch-action-delete" title="Hapus branch" onclick="deleteBranch(' . $dataBranch['id'] . ')">  
                        <i class="mdi mdi-delete-outline"></i>
                    </button>
                </div>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);

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
            'code'    => 'required|string|max:50|unique:branches,code',
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
        ]);

        Log::info($validated);

        $branchCreate = $this->BranchService->createBranch($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch berhasil dibuat',
            'data' => $branchCreate
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $dataBranch = $this->BranchService->findBranch($id);
        return response()->json($dataBranch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:50|unique:branches,code,' . $id,
            'name'    => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:100',
        ]);

        $data = [
            'name'    => $validated['name'],
            'code'    => $validated['code'],
            'address' => $validated['address'] ?? null,
            'phone'   => $validated['phone'] ?? null,
            'email'   => $validated['email'] ?? null,
        ];

        // panggil service
        $branch = $this->BranchService->editBranch($id, $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Branch berhasil diperbarui',
            'data'    => $branch
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->BranchService->deleteBranch($id);
            return response()->json([
                'success' => true,
                'message' => 'Branch berhasil dihapus.'
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
