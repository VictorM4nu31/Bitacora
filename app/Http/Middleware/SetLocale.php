<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set the application locale from the authenticated user (or the request).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = $user ? $user->locale : null;

        if (! is_string($locale) || $locale === '') {
            $preferred = $request->getPreferredLanguage();
            $locale = is_string($preferred) && $preferred !== '' ? $preferred : config('app.locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
