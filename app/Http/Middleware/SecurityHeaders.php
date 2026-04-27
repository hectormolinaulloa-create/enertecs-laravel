<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Content-Security-Policy', $this->buildCsp());
        return $response;
    }

    private function buildCsp(): string
    {
        $vite = $this->viteOrigin();

        return
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$vite}unpkg.com cdn.jsdelivr.net www.youtube.com www.youtube-nocookie.com s.ytimg.com; " .
            "style-src 'self' 'unsafe-inline' {$vite}fonts.googleapis.com unpkg.com; " .
            "font-src 'self' fonts.gstatic.com; " .
            "img-src 'self' data: blob: *.openstreetmap.org *.cartocdn.com basemaps.cartocdn.com i.ytimg.com; " .
            "frame-src www.youtube.com www.youtube-nocookie.com; " .
            "connect-src 'self' {$vite}" . ($vite ? str_replace('http://', 'ws://', $vite) : '') . "unpkg.com; " .
            "frame-ancestors 'none';";
    }

    private function viteOrigin(): string
    {
        $hotFile = public_path('hot');
        if (! app()->isLocal() || ! file_exists($hotFile)) {
            return '';
        }
        $url = rtrim(file_get_contents($hotFile));
        // Normalizar: asegurar que el origen no termina en /
        $parsed = parse_url($url);
        $origin = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? 'localhost') . ':' . ($parsed['port'] ?? 5173);
        return $origin . ' ';
    }
}
