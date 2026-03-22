<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes:  ->middleware('role:admin,enfermeria')
     * Passes if the authenticated user's role is among the listed roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->guard()->check()) {
            return redirect()->route('login');
        }

        if (! in_array(auth()->guard()->user()->role, $roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}