<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\loker;
use App\Models\notifikasi;
use App\Models\status_kerja;
use App\Models\status_pekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LamaranMasukController extends Controller
{
    public function updateStatus($lamaranId, $status)
    {
        $validStatus = ['diterima', 'ditolak'];
        if (!in_array($status, $validStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid'
            ], 400);
        }

        $lamaran = status_pekerjaan::find($lamaranId);

        if (!$lamaran) {
            return response()->json([
                'success' => false,
                'message' => 'Lamaran tidak ditemukan'
            ], 404);
        }

        $pemberiKerjaId = Auth::id();
        if ($lamaran->loker->user_id != $pemberiKerjaId) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak berhak mengubah lamaran ini'
            ], 403);
        }

        $lamaran->status = $status;
        $lamaran->save();

        if ($status === 'diterima') {
            status_kerja::create([
                'loker_id' => $lamaran->loker_id,
                'pekerja_id' => $lamaran->user_id,
                'status' => 'dikerjakan',
            ]);

            $pesan = 'Lamaran Anda telah diterima!';
        } else {
            $pesan = 'Lamaran Anda ditolak.';
        }

        notifikasi::create([
            'pekerja_id' => $lamaran->user_id,
            'isi_pesan' => $pesan,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Lamaran berhasil {$status}",
            'lamaran' => $lamaran
        ]);
    }
    public function index()
    {
        $user = Auth::user();

        $lokers = loker::with(['statusPekerjaans.user'])
            ->where('user_id', $user->id)
            ->get();

        $result = $lokers->map(function ($loker) {
            return [
                'loker_id' => $loker->id,
                'judul' => $loker->judul,
                'pelamar' => $loker->statusPekerjaans->map(function ($lamaran) {
                    return [
                        'user_id' => $lamaran->user->id,
                        'nama' => $lamaran->user->name,
                        'email' => $lamaran->user->email,
                        'status' => $lamaran->status,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar lamaran masuk berhasil diambil',
            'data' => $result
        ]);
    }

    public function setujui(Request $request)
    {
        $request->validate([
            'loker_id' => 'required|exists:lokers,id',
            'pekerja_id' => 'required|exists:users,id',
        ]);

        $pemberiKerjaId = Auth::id();

        $loker = \App\Models\Loker::where('id', $request->loker_id)
            ->where('user_id', $pemberiKerjaId)
            ->first();

        if (!$loker) {
            return response()->json([
                'success' => false,
                'message' => 'Loker tidak ditemukan atau bukan milik Anda',
            ], 403);
        }

        $status = status_pekerjaan::where('loker_id', $request->loker_id)
            ->where('user_id', $request->pekerja_id)
            ->first();

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Lamaran tidak ditemukan',
            ], 404);
        }

        $status->status = 'diterima';
        $status->save();

        status_kerja::create([
            'loker_id' => $request->loker_id,
            'pekerja_id' => $request->pekerja_id,
            'status' => 'dikerjakan',
        ]);

        notifikasi::create([
            'pekerja_id' => $request->pekerja_id,
            'isi_pesan' => 'Lamaran Anda telah diterima!',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil disetujui',
        ]);
    }
    public function tolak(Request $request)
{
    $request->validate([
        'loker_id' => 'required|exists:lokers,id',
        'pekerja_id' => 'required|exists:users,id',
    ]);

    $pemberiKerjaId = Auth::id();

    $loker = loker::where('id', $request->loker_id)
        ->where('user_id', $pemberiKerjaId)
        ->first();

    if (!$loker) {
        return response()->json([
            'success' => false,
            'message' => 'Loker tidak ditemukan atau bukan milik Anda',
        ], 403);
    }

    $status = status_pekerjaan::where('loker_id', $request->loker_id)
        ->where('user_id', $request->pekerja_id)
        ->first();

    if (!$status) {
        return response()->json([
            'success' => false,
            'message' => 'Lamaran tidak ditemukan',
        ], 404);
    }

    $status->status = 'ditolak';
    $status->save();

    notifikasi::create([
        'pekerja_id' => $request->pekerja_id,
        'isi_pesan' => 'Lamaran Anda ditolak.',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Lamaran berhasil ditolak',
    ]);
}

}
