<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Loker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    /* =========================
       📌 READ Lokers
       ========================= */
    public function lokersIndex(Request $request)
    {
        $lokers = Loker::with(['user.profil'])->latest()->get();

        foreach ($lokers as $l) {
            $l->gambar_url = $l->gambar
                ? asset('uploads/loker/' . $l->gambar)
                : null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua lowongan',
            'data' => $lokers
        ]);
    }

    /* =========================
       📌 DELETE Loker
       ========================= */
    public function lokersDestroy(Request $request, $id)
    {
        $loker = Loker::findOrFail($id);

        if ($loker->gambar && file_exists(public_path('uploads/loker/' . $loker->gambar))) {
            unlink(public_path('uploads/loker/' . $loker->gambar));
        }

        $loker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus'
        ]);
    }

    /* =========================
       📌 READ Users
       ========================= */
    public function usersIndex(Request $request)
    {
        $users = User::with('profil')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua user',
            'data' => $users
        ]);
    }

    /* =========================
       📌 DELETE User
       ========================= */
    public function usersDestroy(Request $request, $id)
    {
        $targetUser = User::findOrFail($id);

        $targetUser->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
