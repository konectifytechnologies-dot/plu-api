<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         Log::info('=== SANCTUM DEBUG START ===', [
            'path' => $request->path(),

            // Cookies
            'xsrf_cookie' => $request->cookie('XSRF-TOKEN'),
            'laravel_session_cookie' => $request->cookie('laravel_session'),

            // Headers
            'xsrf_header' => $request->header('X-XSRF-TOKEN'),
            'origin' => $request->header('Origin'),
            'referer' => $request->header('Referer'),

            // Session
            'session_id' => $request->session()->getId(),
            'session_all' => $request->session()->all(),

            // Auth
            'auth_check' => Auth::check(),
            'auth_user' => Auth::user(),

            // HTTPS detection
            'is_secure' => $request->isSecure(),
            'scheme' => $request->getScheme(),
            'full_url' => $request->fullUrl(),

            // All headers
            'all_headers' => $request->headers->all(),
        ]);
        return $next($request);
    }
}
