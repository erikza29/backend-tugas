<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatusPekerjaanRequest;
use App\Models\status_kerja;
use App\Models\status_pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StatusPekerjaanController extends Controller
{
    /**
     * Tambah pekerjaan baru (misalnya setelah diterima).
     */
    public function store(StatusPekerjaanRequest $request)
    {
        // Cegah duplikasi
        $existing = status_pekerjaan::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan sudah tercatat.'
            ], 409); // Conflict
        }

        $pekerjaan = status_pekerjaan::create([
            'user_id'       => $request->user_id,
            'loker_id'      => $request->loker_id,
            'tanggal_mulai' => $request->tanggal_mulai ?? now(),
            'status'        => 'aktif'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil dimulai',
            'data'    => $pekerjaan
        ], 201);
    }

    /**
     * Riwayat pekerjaan berdasarkan user login
     */
    public function riwayat(Request $request)
    {
        $user = $request->user();

        $data = status_pekerjaan::with('loker')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pekerjaan',
            'data'    => $data
        ]);
    }

    /**
     * Update status pekerjaan
     */
    public function updateStatus(Request $request, $id, $status)
    {
        // validasi status
        $validStatus = ['aktif', 'selesai', 'dibatalkan', 'ditolak'];
        if (!in_array($status, $validStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.'
            ], 422);
        }

        // ambil pekerjaan
        $pekerjaan = status_pekerjaan::find($id); // pastikan model pakai PascalCase

        if (!$pekerjaan) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ], 404);
        }

        // isi tanggal_selesai jika selesai/dibatalkan
        if (in_array($status, ['selesai', 'dibatalkan'])) {
            $pekerjaan->tanggal_selesai = now();
        } else {
            $pekerjaan->tanggal_selesai = null; // reset jika status kembali ke aktif/ditolak
        }

        $pekerjaan->status = $status;

        try {
            $pekerjaan->save();
        } catch (\Exception $e) {
            Log::error('Gagal update status pekerjaan: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Status pekerjaan diperbarui menjadi $status",
            'data'    => $pekerjaan
        ]);
    }


    /**
     * Tolak lamaran (ubah status jadi ditolak)
     */
    public function tolak(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'loker_id' => 'required|exists:lokers,id',
        ]);

        $status = status_pekerjaan::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Data lamaran tidak ditemukan'
            ], 404);
        }

        $status->status = 'ditolak';
        $status->save();

        return response()->json([
            'success' => true,
            'message' => 'Lamaran telah ditolak',
            'data'    => $status
        ]);
    }


public function riwayatGabungan()
{
    $userId = Auth::id();

    // Ambil semua lamaran user
    $lamaran = status_kerja::with('loker')
        ->where('user_id', $userId)
        ->get()
        ->map(function ($item) {
            return [
                'type'   => 'lamaran',
                'id'     => $item->id,
                'loker'  => $item->loker->judul ?? null,
                'status' => $item->status,
                'tanggal'=> $item->created_at,
            ];
        });

    // Ambil semua pekerjaan user
    $pekerjaan = status_pekerjaan::with('loker')
        ->where('user_id', $userId)
        ->get()
        ->map(function ($item) {
            return [
                'type'   => 'pekerjaan',
                'id'     => $item->id,
                'loker'  => $item->loker->judul ?? null,
                'status' => $item->status,
                'tanggal'=> $item->tanggal_mulai,
            ];
        });

    // Gabungkan lamaran + pekerjaan
    $riwayat = $lamaran->merge($pekerjaan)->sortByDesc('tanggal')->values();

    return response()->json([
        'success' => true,
        'message' => 'Riwayat berhasil diambil',
        'data'    => $riwayat
    ]);
}

}
