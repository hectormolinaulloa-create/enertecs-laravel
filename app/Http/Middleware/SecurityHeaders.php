<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' unpkg.com; " .
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com unpkg.com; " .
            "font-src 'self' fonts.gstatic.com; " .
            "img-src 'self' data: blob: *.openstreetmap.org *.cartocdn.com basemaps.cartocdn.com; " .
            "connect-src 'self'; " .
            "frame-ancestors 'none';"
        );
        return $response;
    }
}
