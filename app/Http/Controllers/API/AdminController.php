<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Loker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ============================
    // USER MANAGEMENT
    // ============================

    public function users()
{
    $data = User::with('profil')->get();

    $data->map(function ($user) {
        if($user->profil) {
            // pakai gambar_url jika ada, kalau tidak pakai gambar
            if($user->profil->gambar_url) {
                $user->profil->gambar_url = str_starts_with($user->profil->gambar_url, 'http')
                    ? $user->profil->gambar_url
                    : url($user->profil->gambar_url);
            } elseif($user->profil->gambar) {
                $user->profil->gambar_url = asset('storage/profil/' . $user->profil->gambar);
            } else {
                $user->profil->gambar_url = null;
            }
        }
        return $user;
    });

    return response()->json([
        'status' => 'success',
        'data' => $data
    ], 200);
}


    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->profil && $user->profil->gambar && Storage::exists('public/' . $user->profil->gambar)) {
            Storage::delete('public/' . $user->profil->gambar);
        }
        $user->profil?->delete();

        // hapus semua loker miliknya + gambar
        foreach ($user->lokers as $loker) {
            if ($loker->gambar && Storage::exists('public/' . $loker->gambar)) {
                Storage::delete('public/' . $loker->gambar);
            }
        }
        $user->lokers()->delete();
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User berhasil dihapus'
        ]);
    }

    // ============================
    // LOKER MANAGEMENT
    // ============================

   public function lokers()
    {
        $data = Loker::with('user.profil')->get();

        $data->map(function ($item) {
            // URL loker
            $item->gambar_url = $item->gambar ? asset('uploads/loker/' . $item->gambar) : null;

            // URL profil user
            if ($item->user && $item->user->profil) {
                $profil = $item->user->profil;

                // Jika gambar_url sudah ada, pakai langsung
                if ($profil->gambar_url) {
                    // pastikan jadi URL lengkap
                    $profil->gambar_url = str_starts_with($profil->gambar_url, 'http')
                        ? $profil->gambar_url
                        : url($profil->gambar_url);
                } elseif ($profil->gambar) {
                    // fallback pakai nama file mentah
                    $profil->gambar_url = asset('storage/profil/' . $profil->gambar);
                } else {
                    $profil->gambar_url = null;
                }
            }

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }



    public function deleteLoker($id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->gambar && Storage::exists('public/' . $loker->gambar)) {
            Storage::delete('public/' . $loker->gambar);
        }

        $loker->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Loker berhasil dihapus'
        ]);
    }

    public function detailLoker($id)
    {
        $loker = Loker::with(['user.profil'])->find($id);

        if (!$loker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loker tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $loker
        ]);
    }

}

