<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Auth::check()) {
            return redirect()->route('login'); // Redirige a la página de inicio de sesión
        }

        if (!in_array(\Auth::user()->role, ['user', 'moderator', 'admin'])) {
            return response()->json('Opps! You do not have permission to access.', 403);
        }

        return $next($request);
    }
}
