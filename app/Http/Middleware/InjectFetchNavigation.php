<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectFetchNavigation
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->headers->get('Content-Type') !== 'text/html; charset=UTF-8'
            && ! str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        if (! method_exists($response, 'getContent') || ! is_string($response->getContent())) {
            return $response;
        }

        $content = $response->getContent();
        $closingBodyPosition = strripos($content, '</body>');

        if ($closingBodyPosition === false) {
            return $response;
        }

        $scriptVersion = filemtime(public_path('js/fetch-navigation.js'));
        $scriptUrl = htmlspecialchars(asset('js/fetch-navigation.js').'?v='.$scriptVersion, ENT_QUOTES, 'UTF-8');
        $script = '<script src="'.$scriptUrl.'" data-fetch-navigation></script>';
        $content = substr_replace($content, $script.'</body>', $closingBodyPosition, strlen('</body>'));

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
