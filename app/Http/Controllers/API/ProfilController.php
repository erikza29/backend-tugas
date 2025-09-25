<?php

namespace App\Http\Controllers\API;

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

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data' => $profil
        ]);
    }

    // Menyimpan atau mengupdate profil pekerja
    public function storeOrUpdate(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'gambar_url' => 'nullable|string',
        ]);

        $profil = Profil::updateOrCreate(
            ['pekerja_id' => Auth::id()
],
            [
                'nama' => $request->nama,
                'deskripsi' => $request->deskripsi,
                'gambar_url' => $request->gambar_url,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil disimpan',
            'data' => $profil
        ]);
    }

    public function uploadFoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:2048',
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
                'nama' => $user->name,   // default pakai nama user
                'deskripsi' => '',       // deskripsi kosong default
                'gambar_url' => null,
            ]
        );

        // Update foto profil
        $profil->gambar_url = Storage::url($path);
        $profil->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diupload',
            'data' => [
                'gambar_url' => asset(Storage::url($path)),
            ]
        ]);
    }

}
