<?php

use App\Http\Controllers\AvatarController;
use App\Models\MainSiteData;

/**
 * Created by Md. Jahangir Alam Rohan.
 * User: Md. Jahangir Alam Rohan.
 * Date: 25-Jun-2024
 * Time: 03.01 PM
 */

if (! function_exists('siteUrlSettings')) {
    function siteUrlSettings($key)
    {
        static $hasTable = null;
        static $runtimeCache = [];

        try {
            if ($hasTable === null) {
                $hasTable = \Illuminate\Support\Facades\Schema::hasTable('main_site_data');
            }
            if (! $hasTable) {
                return null;
            }

            if (array_key_exists($key, $runtimeCache)) {
                return $runtimeCache[$key];
            }

            return $runtimeCache[$key] = MainSiteData::getValue($key);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

if (! function_exists('generate_avatar')) {
    function generate_avatar($name)
    {
        $controller = app(AvatarController::class);

        return $controller->generateAvatar($name);
    }
}

if (! function_exists('hasAccess')) {
    function hasAccess(array $roles = [], array $permissions = []): bool
    {
        $user = auth()->user();

        return $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) ||
            (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permissions))
        );
    }
}

if (! function_exists('abortIfNoAccess')) {
    function abortIfNoAccess(array $roles = [], array $permissions = [], string $message = 'You do not have permission.'): bool
    {
        if (! hasAccess($roles, $permissions)) {
            flash()->error($message);

            return true;
        }

        return false;
    }
}

if (! function_exists('warningIfNoAccess')) {
    function warningIfNoAccess(array $roles = [], array $permissions = [], string $message = 'You do not have permission.'): bool
    {
        if (! hasAccess($roles, $permissions)) {
            flash()->warning($message);

            return true;
        }

        return false;
    }
}

if (! function_exists('site_image')) {
    /**
     * Correctly resolve site images from public or storage.
     * Handles both string paths and Filament FileUpload array values.
     */
    function site_image($path, $fallback = 'images/logo.png')
    {
        // Filament FileUpload sometimes returns an array — unwrap it
        if (is_array($path)) {
            $path = array_values(array_filter($path))[0] ?? null;
        }

        if (! $path || ! is_string($path)) {
            return asset($fallback);
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Path clean up
        $path = ltrim($path, '/');

        // Check 1: public folder
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Check 2: storage folder (standard Filament/Laravel location)
        if (file_exists(public_path('storage/'.$path))) {
            return asset('storage/'.$path);
        }

        // Final fallback: asset with original path
        return asset($path);
    }
}
