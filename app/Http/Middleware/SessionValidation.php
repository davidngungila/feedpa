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
            
            // Check if user has an active session
            $userSession = UserSession::where('user_id', $user->id)->first();
            
            if (!$userSession) {
                // No session exists, create one
                UserSession::create([
                    'user_id' => $user->id,
                    'session_id' => $currentSessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => now(),
                ]);
            } else {
                // Session exists, check if it's the current one
                if ($userSession->session_id !== $currentSessionId) {
                    // Different session, log out the user
                    Auth::logout();
                    Session::flush();
                    return redirect()->route('login')->with('error', 'You have been logged out because you signed in from another browser/device.');
                }
                
                // Check session timeout (30 minutes)
                $timeoutMinutes = 30;
                if ($userSession->last_activity->diffInMinutes(now()) > $timeoutMinutes) {
                    // Session expired
                    $userSession->delete();
                    Auth::logout();
                    Session::flush();
                    return redirect()->route('login')->with('error', 'Your session has expired due to inactivity.');
                }
                
                // Update last activity timestamp
                $userSession->update([
                    'last_activity' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
        }

        return $next($request);
    }
}
