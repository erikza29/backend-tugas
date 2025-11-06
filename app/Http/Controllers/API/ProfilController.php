<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfilController extends Controller
{
    public function show()
    {
        $profil = Profil::where('pekerja_id', Auth::id())->first();

        if (!$profil) {
            // fallback data dari tabel users
            $user = Auth::user();
            return response()->json([
                'success' => true,
                'message' => 'Data profil belum ada, gunakan data user',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'deskripsi' => '',
                    'gambar_url' => null,
                ]
            ]);
        }

        // pastikan gambar_url jadi full URL
        if ($profil->gambar_url) {
            $profil->gambar_url = url($profil->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data' => $profil
        ]);
    }

    // Menyimpan atau mengupdate profil pekerja
    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,HEIC|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $profil = Profil::where('pekerja_id', Auth::id())->first();
        $data = [
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ];

        // jika ada foto baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('profil', $filename, 'public');

            // hapus foto lama jika ada
            if ($profil && $profil->gambar_url) {
                $oldPath = str_replace('/storage/', '', $profil->gambar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $data['gambar_url'] = Storage::url($path);
        }

        $profil = Profil::updateOrCreate(
            ['pekerja_id' => Auth::id()],
            $data
        );

        // ubah jadi full URL
        if ($profil->gambar_url) {
            $profil->gambar_url = url($profil->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil disimpan',
            'data' => $profil
        ]);
    }
    public function showPublic($id)
{
    $profil = Profil::where('pekerja_id', $id)->first();

    if (!$profil) {
        return response()->json([
            'success' => false,
            'message' => 'Profil tidak ditemukan',
        ], 404);
    }

    // ubah URL gambar jadi full path
    if ($profil->gambar_url) {
        $profil->gambar_url = url($profil->gambar_url);
    }

    return response()->json([
        'success' => true,
        'message' => 'Data profil pelamar berhasil diambil',
        'data' => $profil
    ]);
}


    public function uploadFoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg,HEIC|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $file = $request->file('foto');
        $filename = uniqid().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('profil', $filename, 'public');

        // Cek apakah profil sudah ada
        $profil = Profil::firstOrCreate(
            ['pekerja_id' => $user->id],
            [
                'nama' => $user->name,
                'deskripsi' => '',
                'gambar_url' => null,
            ]
        );

        // hapus foto lama jika ada
        if ($profil->gambar_url) {
            $oldPath = str_replace('/storage/', '', $profil->gambar_url);
            Storage::disk('public')->delete($oldPath);
        }

        // Update foto profil
        $profil->gambar_url = Storage::url($path);
        $profil->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diupload',
            'data' => [
                'gambar_url' => url($profil->gambar_url),
            ]
        ]);
    }
}

