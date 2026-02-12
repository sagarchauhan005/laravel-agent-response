<?php

namespace YourVendor\LaravelLlmsTxt\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AddLlmsTxtLinkHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $path = config('llms-txt.path', 'llms.txt');
        $path = Str::start($path, '/');
        $url = URL::to($path);

        $response->headers->set('Link', '<'.$url.'>; rel="llms-txt"');

        return $response;
    }
}
