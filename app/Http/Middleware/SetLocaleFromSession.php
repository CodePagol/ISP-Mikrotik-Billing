<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use App\Models\MainSiteData;

class SetLocaleFromSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (class_exists(MainSiteData::class) && \Illuminate\Support\Facades\Schema::hasTable('main_site_data')) {
                $host = $request->getHost();
                if ($host && (str_starts_with($host, 'billing.') || str_starts_with($host, 'portal.'))) {
                    // System Language for administrative subdomains
                    $locale = MainSiteData::getValue('site_locale', 'en');
                } else {
                    // Main Site Language: Check session override first, then fall back to DB setting
                    if (session()->has('main_site_locale')) {
                        $locale = session()->get('main_site_locale');
                    } else {
                        $locale = MainSiteData::getValue('main_site_locale', 'en');
                    }
                }
                App::setLocale($locale);
            }
        } catch (\Throwable $e) {
            // Avoid issues during early setup/migrations
        }

        return $next($request);
    }
}
