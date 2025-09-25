<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatusKerjaRequest;
use App\Models\status_kerja; // <-- pakai ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusKerjaController extends Controller
{
    // Melamar pekerjaan
    public function store(StatusKerjaRequest $request)
    {
        // Cek apakah sudah pernah melamar
        $existing = status_kerja::where('user_id', Auth::id())
            ->where('loker_id', $request->loker_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah melamar pekerjaan ini.'
            ], 409);
        }

        $lamaran = status_kerja::create([
            'user_id' => Auth::id(),
            'loker_id' => $request->loker_id,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil dikirim',
            'data' => $lamaran
        ]);
    }

    // Melihat pelamar dari satu loker
    public function pelamarList($loker_id)
    {
        $pelamars = status_kerja::with('user')
            ->where('loker_id', $loker_id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelamar untuk loker ini',
            'data' => $pelamars
        ]);
    }

    // Menyetujui atau menolak lamaran
   // Menyetujui atau menolak lamaran
public function updateStatus($id, $status)
{
    if (!in_array($status, ['diterima', 'ditolak'])) {
        return response()->json([
            'success' => false,
            'message' => 'Status tidak valid.'
        ], 422);
    }

    $lamaran = status_kerja::findOrFail($id);
    $lamaran->status = $status;
    $lamaran->save();

    // Jika diterima → otomatis buat status_pekerjaan aktif
    if ($status === 'diterima') {
        \App\Models\status_pekerjaan::firstOrCreate(
            [
                'user_id'  => $lamaran->user_id,
                'loker_id' => $lamaran->loker_id,
            ],
            [
                'tanggal_mulai' => now(),
                'status'        => 'aktif',
            ]
        );
    }

    return response()->json([
        'success' => true,
        'message' => 'Status lamaran diperbarui',
        'data' => $lamaran
    ]);
}


    // Riwayat kerja
    public function index(Request $request)
    {
        $user = Auth::user();

        $data = status_kerja::with(['user:id,name', 'loker:id,judul,user_id'])
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // pekerja
                      ->orWhereHas('loker', function ($q) use ($user) {
                          $q->where('user_id', $user->id); // pemberi kerja
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat kerja ditemukan',
            'data' => $data,
        ]);
    }


}
