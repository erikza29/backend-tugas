<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Loker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    // Daftar email SuperAdmin
    protected $superAdminEmails = [
        'superadmin@a.b',
        // Tambahkan email superadmin lain
    ];

    // Cek apakah user termasuk SuperAdmin
    protected function isSuperAdmin($user)
    {
        return $user && in_array($user->email, $this->superAdminEmails);
    }

    /* =========================
       Lokers: read & delete
       ========================= */
    public function lokersIndex(Request $request)
    {
        $user = $request->user();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        // Ambil semua lokers beserta user + profil
        $lokers = Loker::with(['user.profil'])->latest()->get();
        $lokers->map(function ($l) {
            $l->gambar_url = $l->gambar ? asset('uploads/loker/' . $l->gambar) : null;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua lowongan',
            'data' => $lokers
        ]);
    }

    public function lokersDestroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $loker = Loker::findOrFail($id);

        // Hapus gambar fisik jika ada
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
       Users: read & delete
       ========================= */
    public function usersIndex(Request $request)
    {
        $user = $request->user();
        if (!$this->isSuperAdmin($user)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $users = User::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar semua user',
            'data' => $users
        ]);
    }

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

        // Hapus user
        $targetUser->delete();

        Log::info("SuperAdmin {$user->email} menghapus user {$targetUser->email}");

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
