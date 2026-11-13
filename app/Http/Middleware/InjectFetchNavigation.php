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
        $closingHeadPosition = strripos($content, '</head>');
        $closingBodyPosition = strripos($content, '</body>');

        if ($closingBodyPosition === false) {
            return $response;
        }

        if ($closingHeadPosition !== false) {
            $paletteVersion = filemtime(public_path('css/palette.css'));
            $paletteUrl = htmlspecialchars(asset('css/palette.css').'?v='.$paletteVersion, ENT_QUOTES, 'UTF-8');
            $stylesheet = '<link rel="stylesheet" href="'.$paletteUrl.'">';
            $faviconUrl = htmlspecialchars(asset('img/LogoGaucho_png.png'), ENT_QUOTES, 'UTF-8');
            $favicon = '<link rel="icon" type="image/png" href="'.$faviconUrl.'">';
            $content = substr_replace($content, $stylesheet.$favicon.'</head>', $closingHeadPosition, strlen('</head>'));
            $closingBodyPosition = strripos($content, '</body>');
        }

        $scriptVersion = filemtime(public_path('js/fetch-navigation.js'));
        $scriptUrl = htmlspecialchars(asset('js/fetch-navigation.js').'?v='.$scriptVersion, ENT_QUOTES, 'UTF-8');
        $script = '<script src="'.$scriptUrl.'" data-fetch-navigation></script>';
        if (str_contains($content, 'data-live-fragment=')) {
            $liveScriptVersion = filemtime(public_path('js/live-fragments.js'));
            $liveScriptUrl = htmlspecialchars(asset('js/live-fragments.js').'?v='.$liveScriptVersion, ENT_QUOTES, 'UTF-8');
            $script .= '<script src="'.$liveScriptUrl.'"></script>';
        }
        $content = substr_replace($content, $script.'</body>', $closingBodyPosition, strlen('</body>'));

        $response->setContent($content);
        $response->headers->remove('Content-Length');

        return $response;
    }
}
