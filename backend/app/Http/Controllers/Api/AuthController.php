<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // Mobile hanya untuk USER. Auth::attempt memakai guard web secara default,
        // jadi sesi yang terbentuk di sini harus segera dihapus setelah token dibuat.
        if (! Auth::attempt($request->validated())) {
            return ApiResponse::error('Email atau password salah.', 'INVALID_CREDENTIALS', null, 401);
        }

        $user = Auth::user();

        if (! $user || $user->role !== 'USER') {
            Auth::guard('web')->logout();

            return ApiResponse::error('Akun ADMIN tidak dapat login melalui mobile.', 'FORBIDDEN', null, 403);
        }

        $token = $user->createToken('asset-app')->plainTextToken;
        Auth::guard('web')->logout();

        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'division' => $user->division?->name,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login berhasil.');
    }

    public function me(): JsonResponse
    {
        $user = auth()->user()->load('division');

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'division' => $user->division?->name,
        ], 'Data pengguna berhasil diambil.');
    }

    public function logout(Request $request): JsonResponse
    {
        // Bisa null (Sanctum::actingAs) atau TransientToken (auth via session),
        // keduanya bukan token DB yang bisa dicabut.
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return ApiResponse::success(null, 'Logout berhasil.');
    }
}
