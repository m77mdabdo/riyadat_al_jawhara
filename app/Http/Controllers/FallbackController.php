<?php

namespace App\Http\Controllers;

use App\Support\LocalizedUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FallbackController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $first = $request->segment(1);

        if (LocalizedUrl::isSupported($first)) {
            // /ar/unknown-page: SetLocale already chose the language.
            abort(404);
        }

        // Legacy un-prefixed public URL: does it exist under the default locale?
        $target = '/'.LocalizedUrl::default().'/'.ltrim($request->path(), '/');

        if ($this->matchesPublicRoute($target)) {
            // Raw query string: getQueryString() would re-sort/normalise it.
            $query = $request->server('QUERY_STRING');

            return redirect($target.($query ? '?'.$query : ''), 301);
        }

        app()->setLocale(LocalizedUrl::default());

        throw new HttpException(404);
    }

    private function matchesPublicRoute(string $path): bool
    {
        try {
            $route = Route::getRoutes()->match(Request::create($path, 'GET'));
        } catch (\Throwable) {
            return false;
        }

        return ! $route->isFallback && in_array('locale', $route->parameterNames(), true);
    }
}
