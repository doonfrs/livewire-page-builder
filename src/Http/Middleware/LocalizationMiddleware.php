<?php

namespace Trinavo\LivewirePageBuilder\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Trinavo\LivewirePageBuilder\Services\LocalizationService;

class LocalizationMiddleware
{
    /**
     * @var LocalizationService
     */
    protected $localizationService;

    /**
     * Create a new middleware instance.
     */
    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get locale from session first
        $locale = Session::get('page_builder_locale');

        // Fall back to cookie if session doesn't have it
        if (!$locale && $request->hasCookie('page_builder_locale')) {
            $locale = $request->cookie('page_builder_locale');
            // Also set it in session to ensure consistency
            Session::put('page_builder_locale', $locale);
        }

        // Check if locale exists in available UI locales
        if ($locale && array_key_exists($locale, $this->localizationService->getUiLocales())) {
            // Set the application locale
            App::setLocale($locale);
        }

        return $next($request);
    }
}
