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
        $user = Auth::user();

        // Jika profil belum dibuat → fallback ke data user
        if (!$profil) {
            return response()->json([
                'success' => true,
                'message' => 'Data profil belum ada, gunakan data user',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'deskripsi' => '',
                    'gambar_url' => null,
                    'whatsapp' => $user->whatsapp, // WA dari tabel users
                ]
            ]);
        }

        // Convert gambar_url ke full URL
        if ($profil->gambar_url) {
            $profil->gambar_url = url($profil->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data' => [
                'id' => $profil->id,
                'nama' => $profil->nama,
                'deskripsi' => $profil->deskripsi,
                'gambar_url' => $profil->gambar_url,
                'whatsapp' => $user->whatsapp, // WA tetap dari users
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'whatsapp' => 'nullable|string|max:20', // input WA untuk table users
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

        // Upload foto baru
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('profil', $filename, 'public');

            // hapus foto lama
            if ($profil && $profil->gambar_url) {
                $oldPath = str_replace('/storage/', '', $profil->gambar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $data['gambar_url'] = Storage::url($path);
        }

        // Simpan atau update profil
        $profil = Profil::updateOrCreate(
            ['pekerja_id' => Auth::id()],
            $data
        );

        // Update WhatsApp di tabel users (bukan di profils)
        if ($request->filled('whatsapp')) {
            $user = Auth::user();
            $user->whatsapp = $request->whatsapp;
            $user->save();
        }

        // Convert full URL
        if ($profil->gambar_url) {
            $profil->gambar_url = url($profil->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil disimpan',
            'data' => [
                'id' => $profil->id,
                'nama' => $profil->nama,
                'deskripsi' => $profil->deskripsi,
                'gambar_url' => $profil->gambar_url,
                'whatsapp' => Auth::user()->whatsapp, // WA dari users
            ]
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

        if ($profil->gambar_url) {
            $profil->gambar_url = url($profil->gambar_url);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data profil pelamar berhasil diambil',
            'data' => [
                'id' => $profil->id,
                'nama' => $profil->nama,
                'deskripsi' => $profil->deskripsi,
                'gambar_url' => $profil->gambar_url,
                'whatsapp' => $profil->pekerja->whatsapp ?? null, // WA dari tabel users
            ]
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

        // Buat profil jika belum ada
        $profil = Profil::firstOrCreate(
            ['pekerja_id' => $user->id],
            [
                'nama' => $user->name,
                'deskripsi' => '',
                'gambar_url' => null,
            ]
        );

        // Hapus foto lama
        if ($profil->gambar_url) {
            $oldPath = str_replace('/storage/', '', $profil->gambar_url);
            Storage::disk('public')->delete($oldPath);
        }

        $profil->gambar_url = Storage::url($path);
        $profil->save();

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diupload',
            'data' => [
                'gambar_url' => url($profil->gambar_url),
                'whatsapp' => $user->whatsapp, // tetap ikut dikirim jika diperlukan
            ]
        ]);
    }
}
