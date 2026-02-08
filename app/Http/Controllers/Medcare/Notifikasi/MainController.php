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
            ->addColumn('actions', function ($row) {
                return '
                    <button 
                        class="btn btn-sm btn-primary"
                        onclick="readNotifikasi(
                            \'' . $row['id'] . '\',
                            \'' . ($row['url'] ?? '#') . '\'
                        )"
                    >
                        <i class="mdi mdi-eye"></i>
                    </button>
                ';
            })

            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    public function readNotifikasi(Request $request)
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        // panggil service
        $this->NotifikasiService->markAsRead($request->id);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function latest()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notifs = $user
            ->unreadNotifications()
            ->latest()
            ->get();

        return response()->json(
            $notifs->map(function ($n) {
                return [
                    'id'      => $n->id,
                    'title'   => $n->data['title'] ?? 'Notifikasi',
                    'message' => $n->data['message'] ?? '',
                    'url'     => $n->data['url'] ?? '#',
                    'time'    => $n->created_at->diffForHumans(),
                ];
            })
        );
    }

    public function markAllRead()
    {
        Auth::user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('medcare.menu.notifikasi.semuaNotifikasi');
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
