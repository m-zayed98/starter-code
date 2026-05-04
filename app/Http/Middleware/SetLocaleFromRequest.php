<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromRequest
{
    private const DEFAULT_LOCALE = 'ar';

    /**
     * Locales the API accepts from X-Locale or Accept-Language. Others fall back to default.
     *
     * @var list<string>s
     */
    private const SUPPORTED = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);
        app()->setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $raw = $request->header('X-Locale') ?? $request->header('Accept-Language');
        if ($raw === null || $raw === '') {
            return self::DEFAULT_LOCALE;
        }

        $primary = trim(explode(',', $raw)[0]);
        $primary = explode(';', $primary)[0];
        $primary = trim($primary);
        if ($primary === '') {
            return self::DEFAULT_LOCALE;
        }

        $tag = strtolower(explode('-', $primary)[0]);

        return in_array($tag, self::SUPPORTED, true) ? $tag : self::DEFAULT_LOCALE;
    }
}
