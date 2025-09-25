<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LokerRequest;
use App\Models\Loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LokerController extends Controller
{

    public function index()
    {
        $lokers = Loker::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan kerja',
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

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan',
            'data' => $loker
        ]);
    }

    public function update(LokerRequest $request, $id)
    {
        $loker = Loker::findOrFail($id);

        // pastikan hanya pemilik yang bisa update
        if ($loker->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan'
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
                'message' => 'Tidak diizinkan'
            ], 403);
        }

        $loker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus'
        ]);
    }
}
