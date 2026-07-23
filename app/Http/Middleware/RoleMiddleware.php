<?php
 
namespace App\Http\Middleware;
 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
 
class RoleMiddleware
{
    /**
     * Usage in routes:
     * ->middleware('role:admin')
     * ->middleware('role:admin,cashier')
     *
     * IMPORTANT: Laravel splits everything after the colon by comma and
     * passes each piece as a SEPARATE argument to this method. Using
     * "string ...$roles" (variadic) captures all of them correctly.
     * A plain "string $roles" only captures the first one, silently
     * dropping the rest — which was the original bug.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }
 
        return $next($request);
    }
}
