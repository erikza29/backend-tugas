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

    public function store(StatusPekerjaanRequest $request)
    {
        $existing = status_pekerjaan::where('user_id', $request->user_id)
            ->where('loker_id', $request->loker_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan sudah tercatat.'
            ], 409);
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


    public function riwayat(Request $request)
    {
        $user = $request->user();

        // FIX → include deadline_end
        $data = status_pekerjaan::with(['loker:id,judul,deadline_end'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pekerjaan',
            'data'    => $data
        ]);
    }


    public function updateStatus(Request $request, $id, $status)
    {
        $validStatus = ['aktif', 'selesai', 'dibatalkan', 'ditolak'];
        if (!in_array($status, $validStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid.'
            ], 422);
        }

        $pekerjaan = status_pekerjaan::find($id);

        if (!$pekerjaan) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan tidak ditemukan'
            ], 404);
        }

        if (in_array($status, ['selesai', 'dibatalkan'])) {
            $pekerjaan->tanggal_selesai = now();
        } else {
            $pekerjaan->tanggal_selesai = null;
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

        $lamaran = status_kerja::with('loker')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($item) {
                return [
                    'type'   => 'lamaran',
                    'id'     => $item->id,
                    'loker'  => [
                        'judul' => $item->loker->judul ?? null,
                        'deadline_end' => $item->loker->deadline_end ?? null,
                    ],
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
                    'loker'  => [
                        'judul' => $item->loker->judul ?? null,
                        'deadline_end' => $item->loker->deadline_end ?? null,
                    ],
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
