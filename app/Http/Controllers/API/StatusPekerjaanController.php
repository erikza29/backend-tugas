<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\status_kerja;
use App\Models\status_pekerjaan;
use App\Models\loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusPekerjaanController extends Controller
{
    /**
     * Terima lamaran: buat pekerjaan baru & tutup lowongan
     */
    public function terimaLamaran(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'loker_id' => 'required|exists:lokers,id',
        ]);

        $lamaran = status_kerja::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if (!$lamaran) {
            return response()->json(['success' => false, 'message' => 'Lamaran tidak ditemukan'], 404);
        }

        // Update lamaran
        $lamaran->status = 'diterima';
        $lamaran->save();

        // Tambahkan ke status_pekerjaan
        $pekerjaan = status_pekerjaan::create([
            'user_id'       => $request->user_id,
            'loker_id'      => $request->loker_id,
            'tanggal_mulai' => now(),
            'status'        => 'aktif',
        ]);

        // Tutup lowongan
        $loker = loker::find($request->loker_id);
        $loker->status = 'tutup';
        $loker->save();

        return response()->json([
            'success' => true,
            'message' => 'Pelamar diterima, lowongan ditutup, dan pekerjaan dimulai.',
            'data'    => $pekerjaan
        ]);
    }

    /**
     * Tolak / batalkan pekerjaan → buka kembali lowongan
     */
    public function tolakPekerjaan(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'loker_id' => 'required|exists:lokers,id',
        ]);

        $pekerjaan = status_pekerjaan::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if (!$pekerjaan) {
            return response()->json(['success' => false, 'message' => 'Pekerjaan tidak ditemukan'], 404);
        }

        // Ubah status pekerjaan jadi dibatalkan
        $pekerjaan->status = 'dibatalkan';
        $pekerjaan->tanggal_selesai = now();
        $pekerjaan->save();

        // Ubah lamaran jadi ditolak
        $lamaran = status_kerja::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if ($lamaran) {
            $lamaran->status = 'ditolak';
            $lamaran->save();
        }

        // Buka kembali lowongan
        $loker = loker::find($request->loker_id);
        $loker->status = 'aktif';
        $loker->save();

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan dibatalkan, lamaran ditolak, dan lowongan dibuka kembali.',
            'data'    => $pekerjaan
        ]);
    }

    /**
     * Riwayat gabungan lamaran + pekerjaan
     */
    public function riwayatGabungan()
    {
        $userId = Auth::id();

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

        $riwayat = $lamaran->merge($pekerjaan)->sortByDesc('tanggal')->values();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat berhasil diambil',
            'data'    => $riwayat
        ]);
    }
}
