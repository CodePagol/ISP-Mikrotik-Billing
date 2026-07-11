<?php

namespace App\Http\Middleware;

use App\Models\MainSiteData;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $maintenance = (bool) MainSiteData::getValue('site_maintenance', false);
            $status = MainSiteData::getValue('site_status', 'active');
            $disabled = ($status === 'disabled');
        } catch (\Throwable $e) {
            $maintenance = false;
            $disabled = false;
        }

        if ($maintenance || $disabled) {
            $host = $request->getHost();
            $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url');

            // Exclude admin panel subdomain (billing.*) so admins can access it to enable the site again
            if (str_starts_with($host, 'billing.')) {
                return $next($request);
            }

            // Exclude local or vendor paths that might be essential, but block the general page view
            if ($host === $baseDomain || str_starts_with($host, 'portal.')) {
                $title = $maintenance ? 'Under Maintenance' : 'Site Temporarily Offline';
                $heading = $maintenance ? 'We\'ll Be Back Soon' : 'Closed Temporarily';
                $message = MainSiteData::getValue('site_message') ?: ($maintenance
                    ? 'We are currently performing scheduled maintenance. Please check back shortly.'
                    : 'This portal and website are temporarily offline. Please check back later.');

                return response()->view('errors.site-closed', [
                    'title' => $title,
                    'heading' => $heading,
                    'message' => $message,
                    'is_maintenance' => $maintenance,
                ], 503);
            }
        }

        return $next($request);
    }
}
