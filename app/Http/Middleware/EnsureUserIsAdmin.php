<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Harap login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        if (! Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Rute ini hanya untuk Administrator.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak. Anda bukan Administrator.');
        }

        return $next($request);
    }
}
