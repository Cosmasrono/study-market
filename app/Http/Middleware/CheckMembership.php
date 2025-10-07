<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if membership is pending
        if ($user->membershipPending()) {
            return redirect()->route('membership.payment')
                ->with('warning', 'Please complete your membership payment to access this feature.');
        }

        // Check if membership is expired
        if ($user->membershipExpired()) {
            return redirect()->route('membership.renew')
                ->with('warning', 'Your membership has expired. Please renew to continue using our services.');
        }

        // Check if membership is suspended
        if ($user->membership_status === 'suspended') {
            return redirect()->route('home')
                ->with('error', 'Your membership has been suspended. Please contact support for assistance.');
        }

        // Allow access if membership is active
        if ($user->hasMembership()) {
            return $next($request);
        }

        // Default: redirect to membership payment
        return redirect()->route('membership.payment')
            ->with('error', 'Active membership required to access this feature.');
    }
}
