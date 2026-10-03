<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'unauthenticated',
                'message' => 'Anda harus login terlebih dahulu.',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Akun Anda tidak aktif atau menunggu verifikasi.',
            ], 403);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            return response()->json([
                'status' => 'forbidden',
                'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses resource ini.',
            ], 403);
        }

        return $next($request);
    }
}
