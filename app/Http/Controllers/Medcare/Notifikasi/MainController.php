<?php

namespace App\Http\Controllers\Medcare\Notifikasi;

use App\Http\Controllers\Controller;
use App\Services\Notifikasi\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class MainController extends Controller
{
    protected $NotifikasiService;

    public function __construct(NotifikasiService $NotifikasiService)
    {
        $this->NotifikasiService = $NotifikasiService;
    }

    /**
     * Display a listing of the resource.
     */
    public function table()
    {
        $data = $this->NotifikasiService->getNotifikasi();

        return DataTables::of($data)
            ->addIndexColumn()
            ->rawColumns(['document', 'summary', 'status_badge', 'actions'])
            ->make(true);
    }

    public function readNotifikasi(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $notification = $this->NotifikasiService->markAsRead($request->id);

        if (! $notification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'notification' => $notification,
        ]);
    }

    public function latest()
    {
        return response()->json($this->NotifikasiService->latestUnread());
    }

    public function markAllRead()
    {
        Auth::user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'status' => 'success',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('medcare.menu.notifikasi.semuaNotifikasi', [
            'summary' => $this->NotifikasiService->summaryForUser(),
        ]);
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
