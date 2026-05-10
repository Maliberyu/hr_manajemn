<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckFeature
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        if (!config("features.{$feature}", true)) {
            $msg = 'Fitur ini belum tersedia, masih dalam pengembangan.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 503);
            }

            return redirect()->route('dashboard')->with('feature_disabled', $msg);
        }

        return $next($request);
    }
}
