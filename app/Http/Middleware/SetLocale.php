<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = collect($request->getLanguages())
            ->map(fn (string $language): string => strtolower(str_replace('_', '-', $language)))
            ->first(fn (string $language): bool => in_array($language, ['en', 'nb', 'nb-no', 'no', 'nn'], true));

        $locale = in_array($locale, ['nb', 'nb-no', 'no', 'nn'], true) ? 'nb' : 'en';

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
