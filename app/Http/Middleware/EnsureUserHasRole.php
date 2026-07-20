<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Restrict a route to specific roles. Admins always pass (see
     * User::hasRole()). Usage: ->middleware('role:procurement,inventory_manager')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !$request->user()->hasRole($roles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
