<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if HTTPS enforcement is enabled (default to production environment)
        $forceHttps = config('app.force_https', app()->environment('production'));

        if ($forceHttps) {
            // Force the URL generator to use HTTPS
            URL::forceScheme('https');

            // Check if request is secure (HTTPS)
            $isSecure = $request->secure() ||
                       $request->getScheme() === 'https' ||
                       $request->getPort() == 443 ||
                       (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            // Redirect HTTP requests to HTTPS
            if (!$isSecure) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        return $next($request);
    }
}
