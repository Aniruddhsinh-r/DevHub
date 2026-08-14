<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        if (! Auth::user()->hasRole([UserRole::ADMIN->value, UserRole::SUPERADMIN->value])) {
            abort(403, 'Unauthorized action');
        }

        return $next($request);
    }
}
