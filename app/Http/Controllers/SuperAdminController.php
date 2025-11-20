<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\loker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    // Daftar email superadmin
    protected $superAdminEmails = [
        'superadmin@a.b',
        // tambah email lain kalau perlu
    ];

    // Cek status superadmin
    protected function isSuperAdmin($user)
    {
        return $user && in_array($user->email, $this->superAdminEmails);
    }

    /* =========================
       📌 READ Lokers
       ========================= */
    public function lokersIndex(Request $request)
    {
        $user = $request->user();

        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Ambil semua loker + user + profil
        $lokers = loker::with(['user.profil'])->latest()->get();

        $lokers->map(function ($l) {
            $l->gambar_url = $l->gambar
                ? asset('uploads/loker/' . $l->gambar)
                : null;
        });

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
        $user = $request->user();

        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $loker = loker::findOrFail($id);

        // Hapus gambar fisik
        if ($loker->gambar && file_exists(public_path('uploads/loker/' . $loker->gambar))) {
            unlink(public_path('uploads/loker/' . $loker->gambar));
        }

        $loker->delete();

        Log::info("SuperAdmin {$user->email} menghapus loker {$loker->judul}");

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
        $user = $request->user();

        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // wajib eager load profil!
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
        $user = $request->user();

        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $targetUser = User::findOrFail($id);

        if ($targetUser->id === $user->id) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri'], 403);
        }

        $targetUser->delete();

        Log::info("SuperAdmin {$user->email} menghapus user {$targetUser->email}");

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
