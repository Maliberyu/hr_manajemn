<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role ?? 'karyawan', $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            // Karyawan diarahkan ke ESS, bukan halaman error
            if (($user->role ?? 'karyawan') === 'karyawan') {
                return redirect()->route('ess.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            abort(403, 'Akses ditolak.');
        }

        return $next($request);
    }
}
