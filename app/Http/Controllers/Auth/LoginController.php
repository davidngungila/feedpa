<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user && $user->is_locked) {
            Audit::log('login_failed', "Locked user login attempt for email: {$request->email}");
            return back()->withErrors([
                'email' => 'Your account has been locked. Please contact the administrator for assistance.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $currentSessionId = Session::getId();
            
            // Try to create new session, if fails fall back to updateOrCreate
            try {
                UserSession::create([
                    'user_id' => Auth::id(),
                    'session_id' => $currentSessionId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_activity' => now(),
                ]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // If unique constraint still exists, fall back to updateOrCreate
                UserSession::updateOrCreate(
                    ['user_id' => Auth::id()],
                    [
                        'session_id' => $currentSessionId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'last_activity' => now(),
                    ]
                );
            }
            
            Audit::log('login', 'User logged in successfully');

            return redirect()->intended('/dashboard')->with('success', 'Login successful! Welcome back.');
        }
        
        Audit::log('login_failed', "Failed login attempt for email: {$request->email}");

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Audit::log('logout', 'User logged out');
        
        // Delete only the current session record
        $currentSessionId = Session::getId();
        UserSession::where('user_id', Auth::id())
            ->where('session_id', $currentSessionId)
            ->delete();
        
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
