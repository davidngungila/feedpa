<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AdminPinMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated with PIN
        if (!Session::has('admin_authenticated') || Session::get('admin_authenticated') !== true) {
            // Redirect to login form
            return redirect()->route('login');
        }
        
        // Check if PIN session expired (24 hours)
        $pinTime = Session::get('admin_pin_time', 0);
        if (time() - $pinTime > 86400) { // 24 hours = 86400 seconds
            Session::forget(['admin_authenticated', 'admin_pin_time', 'admin_remember']);
            return redirect()->route('login')
                ->with('error', 'Session yako imeisha. Tafadhali ingiza PIN tena.');
        }
        
        return $next($request);
    }
}
