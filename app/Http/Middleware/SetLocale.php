<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLanguages = ['en', 'es'];
        $preferredLanguage = $request->header('Accept-Language') ? $request->header('Accept-Language') : 'en';

        if (in_array($preferredLanguage, $supportedLanguages)) {
            app()->setLocale($preferredLanguage);
        }
        return $next($request);
    }
}
