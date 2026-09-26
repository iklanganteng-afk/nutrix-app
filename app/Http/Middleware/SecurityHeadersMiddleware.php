<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('X-XSS-Protection', '1; mode=block', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('Permissions-Policy', 'geolocation=(), camera=(), microphone=()', false);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);

        if ($request->isMethod('GET') && ! $request->expectsJson()) {
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data: blob:; connect-src 'self'; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net; frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self';", false);
        }

        return $response;
    }
}
