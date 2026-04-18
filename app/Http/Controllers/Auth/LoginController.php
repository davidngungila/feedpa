<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
            'admin_pin' => ['required', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
        ]);

        // Check if PIN is correct (2026)
        if ($request->input('admin_pin') === '2026') {
            // Create admin session
            session(['admin_authenticated' => true, 'admin_pin_time' => time()]);
            
            // Remember functionality
            if ($request->has('remember')) {
                session(['admin_remember' => true]);
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'admin_pin' => 'PIN si sahihi. Tafadhali jaribu tena.',
        ])->onlyInput('admin_pin', 'remember');
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Clear admin session
        session()->forget(['admin_authenticated', 'admin_pin_time', 'admin_remember']);

        return redirect('/login');
    }
}
