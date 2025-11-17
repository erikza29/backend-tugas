<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokerRequest;
use App\Models\Loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokerController extends Controller
{
    // 🔹 Hanya menampilkan lowongan milik user login
    public function index()
    {
        $lokers = Loker::with(['user.profil'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $lokers->map(function ($l) {
            $l->gambar_url = $l->gambar ? asset('uploads/loker/' . $l->gambar) : null;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan milik Anda',
            'data' => $lokers
        ]);
    }

    // 🔹 Store (buat lowongan baru)
    public function store(LokerRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();

        // Simpan gambar
        if ($request->hasFile('gambar')) {
            $filename = time() . '.' . $request->gambar->extension();
            $request->gambar->move(public_path('uploads/loker'), $filename);
            $data['gambar'] = $filename;
        }

        // Simpan awal, deadline belum dihitung (hanya disimpan setting-nya)
        $loker = Loker::create($data);

        $loker->load(['user.profil']);
        $loker->gambar_url = $loker->gambar ? asset('uploads/loker/' . $loker->gambar) : null;

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat',
            'data' => $loker
        ]);
    }

    // 🔹 Show (detail lowongan)
    public function show($id)
    {
        $loker = Loker::with(['user.profil'])->findOrFail($id);

        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan melihat lowongan ini'
            ], 403);
        }

        $loker->gambar_url = $loker->gambar ? asset('uploads/loker/' . $loker->gambar) : null;

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan',
            'data' => $loker
        ]);
    }

    // 🔹 Update (update data + gambar)
    public function update(LokerRequest $request, $id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan memperbarui lowongan ini'
            ], 403);
        }

        $data = $request->all();

        // Update gambar
        if ($request->hasFile('gambar')) {
            if ($loker->gambar && file_exists(public_path('uploads/loker/' . $loker->gambar))) {
                unlink(public_path('uploads/loker/' . $loker->gambar));
            }

            $filename = time() . '.' . $request->gambar->extension();
            $request->gambar->move(public_path('uploads/loker'), $filename);
            $data['gambar'] = $filename;
        }

        $loker->update($data);

        $loker->load(['user.profil']);
        $loker->gambar_url = $loker->gambar ? asset('uploads/loker/' . $loker->gambar) : null;

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diperbarui',
            'data' => $loker
        ]);
    }

    // 🔹 Hapus lowongan
    public function destroy($id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan menghapus lowongan ini'
            ], 403);
        }

        if ($loker->gambar && file_exists(public_path('uploads/loker/' . $loker->gambar))) {
            unlink(public_path('uploads/loker/' . $loker->gambar));
        }

        $loker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus'
        ]);
    }

    // 🔹 Public list (untuk semua visitor)
    public function publicIndex()
    {
        $lokers = Loker::with(['user.profil'])->latest()->get();

        $lokers->map(function ($l) {
            $l->gambar_url = $l->gambar ? asset('uploads/loker/' . $l->gambar) : null;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua lowongan tersedia',
            'data' => $lokers
        ]);
    }

    // 🔹 Public detail
    public function publicShow($id)
    {
        $loker = Loker::with(['user.profil'])->find($id);

        if (!$loker) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak ditemukan'
            ], 404);
        }

        $loker->gambar_url = $loker->gambar ? asset('uploads/loker/' . $loker->gambar) : null;

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan',
            'data' => $loker
        ]);
    }

    // 🔹 Update status lowongan (DIPAKAI ADMIN ACC/TOLAK PELAMAR)
    public function updateStatus(Request $request, $id)
    {
        $loker = Loker::find($id);

        if (!$loker) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:aktif,tutup'
        ]);

        $oldStatus = $loker->status;
        $newStatus = $request->status;

        // 🔥 Jika status berubah dari AKTIF → TUTUP, maka deadline mulai dihitung
        if ($oldStatus === 'aktif' && $newStatus === 'tutup') {
            if ($loker->deadline_value && $loker->deadline_unit) {
                if ($loker->deadline_unit === 'jam') {
                    $loker->deadline_end = now()->addHours($loker->deadline_value);
                } elseif ($loker->deadline_unit === 'hari') {
                    $loker->deadline_end = now()->addDays($loker->deadline_value);
                }
            }
        }

        // 🔥 Jika status dari TUTUP → AKTIF, reset deadline
        if ($oldStatus === 'tutup' && $newStatus === 'aktif') {
            $loker->deadline_end = null;
        }

        // Update status
        $loker->status = $newStatus;
        $loker->save();

        $loker->load(['user.profil']);
        $loker->gambar_url = $loker->gambar ? asset('uploads/loker/' . $loker->gambar) : null;

        return response()->json([
            'success' => true,
            'data' => $loker
        ]);
    }
}
