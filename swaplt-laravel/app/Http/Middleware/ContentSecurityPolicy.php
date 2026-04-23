<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://accounts.google.com https://apis.google.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "img-src 'self' data: https: http:; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self' https://accounts.google.com https://oauth2.googleapis.com; " .
               "frame-src 'self' https://accounts.google.com; " .
               "frame-ancestors 'self'; " .
               "form-action 'self' https://accounts.google.com;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
} 