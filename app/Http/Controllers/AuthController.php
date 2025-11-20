<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Daftar email superadmin statis
    protected $superAdminEmails = [
        's@s.s',
        // Tambahkan jika ingin lebih banyak
    ];

    // Password superadmin (bebas ditentukan)
    protected $superAdminPassword = '1';

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $credentials['email'];
        $password = $credentials['password'];

        /* ========================================
           1. LOGIN SUPERADMIN (TANPA DATABASE)
        ======================================== */
        if (in_array($email, $this->superAdminEmails)) {

            // Cek password superadmin
            if ($password !== $this->superAdminPassword) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password superadmin salah',
                    'data' => null
                ], 401);
            }

            // Buat "user virtual" tanpa database
            $fake = new User();
            $fake->id = 999999; // ID palsu, aman
            $fake->name = "Super Admin";
            $fake->email = $email;

            // Generate token untuk sanctum
            $token = $fake->createToken('superadmin_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login superadmin berhasil',
                'data' => [
                    'token' => $token,
                    'user' => $fake,
                    'is_superadmin' => true
                ]
            ]);

        }

        /* ========================================
           2. LOGIN USER BIASA (NORMAL)
        ======================================== */
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah',
                'data' => null
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user,
                'is_superadmin' => false
            ]
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
            'data' => null
        ]);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'whatsapp' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data'    => $user
            ], 201);

        } catch (Exception $e) {
            Log::error('Error saat registrasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
