<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (penyewa or pengelola).
     * Pengelola starts in 'pending' status until verified by admin.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $role = $request->input('role', 'penyewa');
        $status = ($role === 'pengelola') ? 'pending' : 'active';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $role,
            'status' => $status,
        ]);

        $token = null;
        if ($status === 'active') {
            $token = $user->createToken('auth-token')->plainTextToken;
        }

        $message = ($status === 'pending')
            ? 'Pendaftaran akun pengelola berhasil! Akun Anda sedang menunggu verifikasi dari Admin sebelum dapat digunakan.'
            : 'Pendaftaran berhasil! Selamat datang di platform booking lapangan.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'is_pending_approval' => ($status === 'pending'),
            ],
        ], 201);
    }

    /**
     * Login user and issue Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kombinasi email dan kata sandi tidak cocok.'],
            ]);
        }

        if ($user->status === 'pending') {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Akun pengelola Anda masih dalam proses verifikasi oleh Admin. Silakan hubungi admin jika butuh bantuan.',
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Get profile of authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($request->user()),
        ]);
    }

    /**
     * Logout and revoke tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }
}
