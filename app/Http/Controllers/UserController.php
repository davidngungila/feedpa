<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Audit;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_admin' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'position' => $request->position,
            'is_admin' => $request->is_admin ?? false,
        ];

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        $user = User::create($userData);
        
        Audit::log('create_user', "Created user: {$user->name} ({$user->email})");

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_admin' => 'nullable|boolean',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
            'is_admin' => $request->is_admin ?? false,
        ];

        if ($request->password) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        $user->update($userData);
        
        Audit::log('update_user', "Updated user: {$user->name} ({$user->email})");

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $user = User::findOrFail($id);
        
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $deletedUserName = $user->name;
        $deletedUserEmail = $user->email;
        
        $user->delete();
        
        Audit::log('delete_user', "Deleted user: {$deletedUserName} ({$deletedUserEmail})");
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
    
    /**
     * Reset user password.
     */
    public function resetPassword(string $id)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized');
        }
        
        $user = User::findOrFail($id);
        
        $newPassword = \Illuminate\Support\Str::random(8);
        
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
        
        Audit::log('reset_password', "Reset password for user: {$user->name} ({$user->email})");
        
        return back()->with('success', "Password reset successfully! New password: {$newPassword}");
    }

    /**
     * Show the authenticated user's profile.
     */
    public function profile()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    /**
     * Show the authenticated user's profile edit form.
     */
    public function editProfile()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position' => $request->position,
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $userData['avatar'] = $avatarPath;
        }

        $user->update($userData);

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.index')->with('success', 'Password updated successfully');
    }
    
    /**
     * Get active sessions for the authenticated user (API).
     */
    public function getActiveSessions()
    {
        $user = auth()->user();
        $currentSessionId = Session::getId();
        
        $sessions = UserSession::where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();
        
        // Check if current session is in the list
        $hasCurrentSession = $sessions->contains('session_id', $currentSessionId);
        
        // If not, add it
        if (!$hasCurrentSession) {
            $currentSession = (object)[
                'id' => 0,
                'session_id' => $currentSessionId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'last_activity' => now(),
                'created_at' => now(),
            ];
            $sessions->prepend($currentSession);
        }
        
        $formattedSessions = $sessions->map(function($session) use ($currentSessionId) {
            return [
                'id' => $session->id ?? 0,
                'session_id' => $session->session_id,
                'is_current' => $session->session_id === $currentSessionId,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity' => isset($session->last_activity) ? $session->last_activity->diffForHumans() : 'Just now',
                'created_at' => isset($session->created_at) ? $session->created_at->format('M d, Y H:i') : now()->format('M d, Y H:i'),
            ];
        });
        
        return response()->json(['sessions' => $formattedSessions]);
    }
    
    /**
     * Logout a specific session.
     */
    public function logoutSession($sessionId)
    {
        $user = auth()->user();
        $currentSessionId = Session::getId();
        
        if ($sessionId === $currentSessionId) {
            return response()->json(['error' => 'Cannot logout current session'], 400);
        }
        
        $session = UserSession::where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();
        
        if ($session) {
            $session->delete();
            return response()->json(['success' => true, 'message' => 'Session logged out successfully']);
        }
        
        return response()->json(['error' => 'Session not found'], 404);
    }
    
    /**
     * Logout all other sessions except current.
     */
    public function logoutOtherSessions()
    {
        $user = auth()->user();
        $currentSessionId = Session::getId();
        
        UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->delete();
        
        return back()->with('success', 'All other sessions logged out successfully');
    }
    
    /**
     * Show 2FA setup page.
     */
    public function showTwoFactorSetup()
    {
        $user = auth()->user();
        
        if ($user->two_factor_enabled) {
            return redirect()->route('profile.index')->with('info', 'Two-factor authentication is already enabled');
        }
        
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        Session::put('two_factor_setup_secret', $secret);
        
        // Generate QR code using SimpleSoftwareIO\QrCode
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        // Generate SVG QR code
        $qrCodeSvg = QrCode::size(200)->margin(1)->generate($qrCodeUrl);
        
        return view('profile.two-factor-setup', compact('qrCodeSvg', 'secret'));
    }
    
    /**
     * Verify 2FA code and enable it.
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        
        $user = auth()->user();
        $google2fa = new Google2FA();
        $secret = Session::get('two_factor_setup_secret');
        
        if (!$secret) {
            return back()->with('error', 'Two-factor setup session expired');
        }
        
        $valid = $google2fa->verifyKey($secret, $request->code, 2);
        
        if (!$valid) {
            return back()->with('error', 'Invalid verification code');
        }
        
        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }
        
        $user->update([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);
        
        Session::forget('two_factor_setup_secret');
        
        Audit::log('enable_2fa', "Enabled two-factor authentication for user: {$user->name} ({$user->email})");
        
        return view('profile.two-factor-recovery-codes', compact('recoveryCodes'));
    }
    
    /**
     * Show 2FA disable confirmation.
     */
    public function showDisableTwoFactor()
    {
        return view('profile.disable-two-factor');
    }
    
    /**
     * Disable 2FA.
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);
        
        $user = auth()->user();
        
        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'The password is incorrect');
        }
        
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
        
        Audit::log('disable_2fa', "Disabled two-factor authentication for user: {$user->name} ({$user->email})");
        
        return redirect()->route('profile.index')->with('success', 'Two-factor authentication disabled');
    }
    
    /**
     * Regenerate 2FA recovery codes.
     */
    public function regenerateRecoveryCodes()
    {
        $user = auth()->user();
        
        // Generate new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = Str::random(10) . '-' . Str::random(10);
        }
        
        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
        ]);
        
        Audit::log('regenerate_2fa_codes', "Regenerated two-factor recovery codes for user: {$user->name} ({$user->email})");
        
        return view('profile.two-factor-recovery-codes', compact('recoveryCodes'));
    }
}
