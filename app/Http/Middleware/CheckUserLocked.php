<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserLocked
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_locked) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been locked. Please contact an administrator.');
        }
        
        return $next($request);
    }
}
