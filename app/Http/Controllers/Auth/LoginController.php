<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class LoginController extends Controller
{
    private const ENTRY_SESSION_KEY = 'secure_login_entry';
    private const ENTRY_TTL_MINUTES = 5;

    public function issueEntry(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }

        $nonce = (string) Str::uuid();

        Cache::put($this->entryCacheKey($nonce), [
            'user_agent_hash' => sha1((string) $request->userAgent()),
            'created_at' => now()->toIso8601String(),
        ], now()->addMinutes(self::ENTRY_TTL_MINUTES));

        $token = $this->encodePathToken(Crypt::encryptString(json_encode([
            'nonce' => $nonce,
        ])));

        return redirect('/' . $token);
    }

    public function showLoginForm(Request $request, string $entryToken)
    {
        if (Auth::check()) {
            return redirect()->intended('/dashboard');
        }

        if (!$this->grantEntryAccess($request, $entryToken)) {
            return redirect('/');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request, string $entryToken)
    {
        if (!$this->hasEntryAccess($request, $entryToken)) {
            return redirect('/');
        }

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

        if (Auth::validate($credentials)) {
            if ($user->two_factor_enabled) {
                Session::put('two_factor_login_id', $user->id);
                Session::put('two_factor_remember', $request->boolean('remember'));
                return redirect()->route('two-factor.login');
            }
            
            // If 2FA not enabled, log them in directly
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $this->clearEntryAccess($request);
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
     * Show two-factor authentication login form.
     */
    public function showTwoFactorLoginForm(Request $request)
    {
        if (!Session::has('two_factor_login_id') || !$this->hasEntryAccess($request)) {
            return redirect('/');
        }
        
        return view('auth.two-factor-login');
    }
    
    /**
     * Verify two-factor authentication code.
     */
    public function verifyTwoFactor(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        $request->validate([
            'code' => 'required|string',
        ]);
        
        if (!Session::has('two_factor_login_id')) {
            return redirect('/');
        }
        
        $user = \App\Models\User::findOrFail(Session::get('two_factor_login_id'));
        $google2fa = new Google2FA();
        $secret = Crypt::decryptString($user->two_factor_secret);
        
        $valid = $google2fa->verifyKey($secret, $request->code, 2);
        
        // Check recovery codes if code is invalid
        if (!$valid) {
            $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
            $codeIndex = array_search($request->code, $recoveryCodes);
            
            if ($codeIndex !== false) {
                // Remove used recovery code
                unset($recoveryCodes[$codeIndex]);
                $user->update([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($recoveryCodes))),
                ]);
                $valid = true;
                Audit::log('login_2fa_recovery', "Used recovery code for user: {$user->name} ({$user->email})");
            }
        }
        
        if (!$valid) {
            Audit::log('login_failed_2fa', "Invalid 2FA code for user: {$user->name} ({$user->email})");
            return back()->withErrors([
                'code' => 'The provided two-factor authentication code is invalid.',
            ]);
        }
        
        // Log the user in
        Auth::login($user, Session::get('two_factor_remember', false));
        $request->session()->regenerate();
        $this->clearEntryAccess($request);
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
        
        Session::forget('two_factor_login_id');
        Session::forget('two_factor_remember');
        
        Audit::log('login_2fa', "User logged in successfully with 2FA: {$user->name} ({$user->email})");
        return redirect()->intended('/dashboard')->with('success', 'Login successful! Welcome back.');
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

        return redirect('/');
    }

    private function grantEntryAccess(Request $request, string $entryToken): bool
    {
        if ($this->hasEntryAccess($request, $entryToken)) {
            return true;
        }

        try {
            $payload = json_decode(
                Crypt::decryptString($this->decodePathToken($entryToken)),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $e) {
            return false;
        }

        $nonce = $payload['nonce'] ?? null;

        if (!$nonce) {
            return false;
        }

        $entry = Cache::pull($this->entryCacheKey($nonce));

        if (!is_array($entry)) {
            return false;
        }

        if (($entry['user_agent_hash'] ?? null) !== sha1((string) $request->userAgent())) {
            return false;
        }

        $request->session()->put(self::ENTRY_SESSION_KEY, [
            'nonce' => $nonce,
            'path_token' => $entryToken,
            'granted_at' => now()->timestamp,
        ]);

        return true;
    }

    private function hasEntryAccess(Request $request, ?string $entryToken = null): bool
    {
        $entry = $request->session()->get(self::ENTRY_SESSION_KEY);

        if (!is_array($entry) || empty($entry['granted_at'])) {
            return false;
        }

        if ((now()->timestamp - (int) $entry['granted_at']) > (self::ENTRY_TTL_MINUTES * 60)) {
            return false;
        }

        if ($entryToken !== null && ($entry['path_token'] ?? null) !== $entryToken) {
            return false;
        }

        return true;
    }

    private function clearEntryAccess(Request $request): void
    {
        $request->session()->forget(self::ENTRY_SESSION_KEY);
    }

    private function entryCacheKey(string $nonce): string
    {
        return 'secure_login_entry:' . $nonce;
    }

    private function encodePathToken(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function decodePathToken(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        if ($decoded === false) {
            throw new \RuntimeException('Invalid secure token.');
        }

        return $decoded;
    }
}
