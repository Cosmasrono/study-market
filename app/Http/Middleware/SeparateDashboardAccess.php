<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SeparateDashboardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Check if the request is for an admin dashboard
        if ($request->is('admin/*')) {
            // Ensure only admin users can access admin dashboard
            if (!Auth::guard('admin')->check()) {
                return redirect()->route('admin.login')
                    ->with('error', 'You must be logged in as an admin to access this area.');
            }
        } 
        // Check if the request is for a user dashboard
        elseif ($request->is('account/*') || $request->is('account')) {
            // Ensure only regular users can access user dashboard
            if (!Auth::guard('web')->check()) {
                return redirect()->route('login')
                    ->with('error', 'You must be logged in to access your account.');
            }
        }

        return $next($request);
    }
}
