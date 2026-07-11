<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictToProfileIfNoPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // A user is considered to have permissions if they:
            // 1. are a Super Admin
            // 2. have any roles via Spatie
            // 3. have any direct permissions via Spatie
            $hasAnyAccess = $user->hasRole('Super Admin') || 
                            $user->roles()->exists() || 
                            $user->permissions()->exists();

            if (!$hasAnyAccess) {
                // If they don't have access, they can ONLY access profile-related URLs and logout
                $allowedPaths = [
                    'user/profile',
                    'user/profile-information',
                    'user/password',
                    'user/two-factor-authentication',
                    'user/two-factor-qr-code',
                    'user/two-factor-recovery-codes',
                    'user/confirmed-two-factor-authentication',
                    'user/other-browser-sessions',
                    'logout',
                ];

                $currentPath = ltrim($request->path(), '/');

                $isAllowed = false;
                foreach ($allowedPaths as $path) {
                    if ($currentPath === $path || str_starts_with($currentPath, $path . '/')) {
                        $isAllowed = true;
                        break;
                    }
                }

                if (!$isAllowed) {
                    flash()->warning('You do not have permission to access other pages. Please update your profile.');
                    return redirect()->route('profile.show');
                }
            }
        }

        return $next($request);
    }
}
