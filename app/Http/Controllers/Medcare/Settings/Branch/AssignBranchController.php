<?php

namespace App\Http\Controllers\Medcare\Settings\Branch;

use App\Http\Controllers\Controller;
use App\Models\BranchModel;
use App\Services\Settings\Auth\UserService;
use App\Services\Settings\Master\BranchService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AssignBranchController extends Controller
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
    public function assignBranch()
    {
        return view('medcare.settings.assignBranch.assignBranch');
    }

    public function table()
    {
        $Branch = $this->BranchService->getBranches();

        return DataTables::of($Branch)
        ->addIndexColumn()
        ->addColumn('actions', function ($dataBranch) {
            return '
                <div class="branch-action-group">
                    <button type="button" class="btn branch-action-btn branch-action-assign" title="Assign user" onclick="assignBranch(' . $dataBranch['id'] . ')"> 
                        <i class="mdi mdi-account-plus-outline"></i>
                    </button>
                </div>
            ';
        })

        ->rawColumns(['actions'])
        ->make(true);

    }

    public function getUser(){
        return response($this->UserService->getData());
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|array',     // multiple user
            'user_id.*' => 'exists:users,id',
            'branchId'  => 'required|exists:branches,id',
        ]);

        try {
            // Ambil branch
            $branch = $this->BranchService->findBranch($validated['branchId']);

            // Simpan relasi user - branch
            $branch->users()->sync($validated['user_id']); 
            // kalau mau nambah tanpa hapus yg lama -> syncWithoutDetaching()

            return response()->json([
                'success' => true,
                'message' => 'User berhasil di-assign ke branch.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal assign user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignedUsers(BranchModel $branch)
    {
        return response()->json(
            $branch->users()->select('users.id', 'users.name')->get()
        );
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
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
