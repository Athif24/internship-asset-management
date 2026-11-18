<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * @param  string  ...$roles  Daftar role yang diizinkan, mis. ADMIN, USER
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Akses tidak sah untuk role ini.');
        }

        return $next($request);
    }
}
