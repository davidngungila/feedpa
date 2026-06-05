<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = Session::getId();
            
            // Check if current session exists in UserSession
            $userSession = UserSession::where('user_id', $user->id)
                ->where('session_id', $currentSessionId)
                ->first();
            
            if (!$userSession) {
                // Session was deleted (logged out from another device)
                Auth::logout();
                Session::invalidate();
                Session::regenerateToken();
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['redirect' => route('login'), 'message' => 'You have been logged out because you signed in from another browser/device'], 401);
                }
                
                return redirect()->route('login')->with('error', 'You have been logged out because you signed in from another browser/device');
            }
            
            // Check session timeout (30 minutes)
            $timeoutMinutes = 30;
            if ($userSession->last_activity->diffInMinutes(now()) > $timeoutMinutes) {
                // Session expired
                $userSession->delete();
                Auth::logout();
                Session::invalidate();
                Session::regenerateToken();
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['redirect' => route('login'), 'message' => 'Your session has expired due to inactivity'], 401);
                }
                
                return redirect()->route('login')->with('error', 'Your session has expired due to inactivity');
            }
            
            // Update last activity timestamp
            $userSession->update([
                'last_activity' => now(),
            ]);
        }

        return $next($request);
    }
}
