<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            //401 means user is not logged in
            abort(401);
        }
        if (! auth()->user()->roles()->where('name', $role)->exists()) {
            //403 means user doesn't have the ability
            abort(403);
        }

        return $next($request);
    }
}
