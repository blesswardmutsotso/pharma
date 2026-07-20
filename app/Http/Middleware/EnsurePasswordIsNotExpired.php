<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotExpired
{
    /**
     * Pharma BRD security requirement: force a password change every 90 days.
     * Redirects to the password-change screen instead of the routes that
     * change it (or logout), to avoid a redirect loop.
     */
    private const EXEMPT_ROUTES = [
        'password.confirm', 'password.update', 'logout', 'profile.edit',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isPasswordExpired() && !$request->routeIs(self::EXEMPT_ROUTES)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Your password has expired. Please set a new one to continue.');
        }

        return $next($request);
    }
}
