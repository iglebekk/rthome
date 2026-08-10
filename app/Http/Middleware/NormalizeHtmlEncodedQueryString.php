<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeHtmlEncodedQueryString
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $queryString = (string) $request->server->get('QUERY_STRING', '');
        $normalizedQueryString = str_replace(['&amp;', '&AMP;'], '&', $queryString);

        if ($normalizedQueryString !== $queryString) {
            parse_str($normalizedQueryString, $query);
            $request->server->set('QUERY_STRING', $normalizedQueryString);
            $request->query->replace($query);
        }

        return $next($request);
    }
}
