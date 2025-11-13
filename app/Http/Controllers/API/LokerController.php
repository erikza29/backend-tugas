<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokerRequest;
use App\Models\Loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokerController extends Controller
{
    // hanya menampilkan lowongan milik user login
    public function index()
    {
        $lokers = Loker::with('user')
            ->where('user_id', Auth::id()) // filter hanya milik user login
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan milik Anda',
            'data' => $lokers
        ]);
    }

    public function store(LokerRequest $request)
    {
        $loker = Loker::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'lokasi' => $request->lokasi,
            'gaji' => $request->gaji,
            'deadline' => $request->deadline,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat',
            'data' => $loker
        ]);
    }

    public function show($id)
    {
        $loker = Loker::with('user')->findOrFail($id);

        // hanya pemilik yang bisa lihat detail
        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan melihat lowongan ini'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan',
            'data' => $loker
        ]);
    }

    public function update(LokerRequest $request, $id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan memperbarui lowongan ini'
            ], 403);
        }

        $loker->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diperbarui',
            'data' => $loker
        ]);
    }

    public function destroy($id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan menghapus lowongan ini'
            ], 403);
        }

        $loker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus'
        ]);
    }
    public function publicIndex()
    {
        $lokers = Loker::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua lowongan tersedia',
            'data' => $lokers
        ]);
    }

    public function publicShow($id)
    {
        $loker = Loker::with('user')->find($id);

        if (!$loker) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan',
            'data' => $loker
        ]);
    }

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

        $loker->status = $request->status;
        $loker->save();

        return response()->json([
            'success' => true,
            'data' => $loker
        ]);
    }

}
