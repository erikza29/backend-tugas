<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
     public function index()
    {
        $user = Auth::user();
        $notifs = Notifikasi::where('pekerja_id', $user->id)
                            ->orderBy('dibuat_pada', 'desc')
                            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi',
            'data' => $notifs
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pekerja_id' => 'required|exists:users,id',
            'isi_pesan' => 'required|string'
        ]);

        $notif = Notifikasi::create([
            'pekerja_id' => $request->pekerja_id,
            'isi_pesan' => $request->isi_pesan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dikirim',
            'data' => $notif
        ]);
    }
}
