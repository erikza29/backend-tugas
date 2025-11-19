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

        if (!$profil) {
            return response()->json([
                'success' => true,
                'message' => 'Data profil belum ada, gunakan data user',
                'data' => [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'deskripsi' => '',
                    'gambar_url' => null,
                    'whatsapp' => $user->whatsapp,
                ]
            ]);
        }

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
                'whatsapp' => $user->whatsapp,
            ]
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'whatsapp' => 'nullable|string|max:20',
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

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('profil', $filename, 'public');

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

        if ($request->filled('whatsapp')) {
            $user = Auth::user();
            $user->whatsapp = $request->whatsapp;
            $user->save();
        }

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
                'whatsapp' => Auth::user()->whatsapp,
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
                'whatsapp' => $profil->pekerja->whatsapp ?? null,
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

        $profil = Profil::firstOrCreate(
            ['pekerja_id' => $user->id],
            [
                'nama' => $user->name,
                'deskripsi' => '',
                'gambar_url' => null,
            ]
        );

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
                'whatsapp' => $user->whatsapp, 
            ]
        ]);
    }
}
