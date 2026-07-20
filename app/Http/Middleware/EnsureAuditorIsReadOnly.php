<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuditorIsReadOnly
{
    /**
     * Blanket safety net: the Auditor role can never perform a mutating
     * request anywhere in the app, regardless of which per-controller
     * role list a route happens to be on.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isAuditor() && !$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            abort(403, 'Auditors have read-only access.');
        }

        return $next($request);
    }
}
