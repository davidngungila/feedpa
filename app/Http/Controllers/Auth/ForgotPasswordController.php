<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Services\EmailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    private const ENTRY_SESSION_KEY = 'secure_login_entry';
    private const ENTRY_TTL_MINUTES = 5;

    /**
     * Show form to request password reset (enter email)
     */
    public function showLinkRequestForm(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send OTP to user's email
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_locked) {
            return back()->withErrors(['email' => 'Your account is locked. Please contact the administrator.']);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing tokens for this email
        PasswordResetToken::where('email', $request->email)->delete();

        // Create new token
        PasswordResetToken::create([
            'user_id' => $user->id,
            'email' => $request->email,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email with OTP
        try {
            // Configure mail system
            $emailConfigService = new EmailConfigService();
            $emailConfigService->configureMail();
            $config = $emailConfigService->getEmailConfig();

            Mail::html($this->buildOtpEmail($user, $otp), function ($message) use ($user, $config) {
                $message->to($user->email)
                    ->subject('Password Reset OTP')
                    ->from($config['from_address'], $config['from_name']);
            });

            Log::info('Password reset OTP sent', ['email' => $user->email]);

        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP', [
                'error' => $e->getMessage(),
                'email' => $user->email,
            ]);
            return back()->withErrors(['email' => 'Failed to send OTP. Please try again later.']);
        }

        // Redirect to OTP verification page with email in session
        session()->put('password_reset_email', $request->email);
        return redirect()->route('password.otp');
    }

    /**
     * Show OTP verification form
     */
    public function showOtpForm(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        if (!session()->has('password_reset_email')) {
            return redirect('/');
        }
        return view('auth.forgot-password-otp');
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        $email = session()->get('password_reset_email');

        if (!$email) {
            return redirect('/');
        }

        // Find valid token
        $token = PasswordResetToken::where('email', $email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$token) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // OTP is valid, store token in session
        session()->put('password_reset_token', $token->id);
        return redirect()->route('password.reset');
    }

    /**
     * Show reset password form
     */
    public function showResetPasswordForm(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        if (!session()->has('password_reset_token')) {
            return redirect('/');
        }

        $token = PasswordResetToken::find(session()->get('password_reset_token'));
        if (!$token || $token->expires_at < now()) {
            session()->forget(['password_reset_token', 'password_reset_email']);
            return redirect()->route('password.request')->withErrors(['email' => 'Token expired. Please request a new OTP.']);
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        if (!$this->hasEntryAccess($request)) {
            return redirect('/');
        }

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $token = PasswordResetToken::find(session()->get('password_reset_token'));

        if (!$token || $token->expires_at < now()) {
            session()->forget(['password_reset_token', 'password_reset_email']);
            return redirect()->route('password.request')->withErrors(['email' => 'Token expired. Please request a new OTP.']);
        }

        // Update user password
        $user = $token->user;
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete all tokens for this user
        PasswordResetToken::where('email', $user->email)->delete();
        session()->forget(['password_reset_token', 'password_reset_email']);

        return redirect()->route('login')->with('success', 'Password reset successfully! Please login with your new password.');
    }

    /**
     * Build OTP email content
     */
    private function buildOtpEmail(User $user, string $otp): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Poppins', sans-serif; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #059669, #10b981); padding: 30px 25px; text-align: center; color: white; }
        .header .title { font-size: 26px; font-weight: 700; margin: 0; }
        .header .sub-title { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 25px; }
        .greeting { font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 15px; }
        .otp-box { background: linear-gradient(135deg, #f0fdf4, #d1fae5); border: 2px dashed #10b981; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .otp-label { font-size: 12px; text-transform: uppercase; font-weight: 700; color: #065f46; letter-spacing: 0.5px; margin-bottom: 8px; }
        .otp-value { font-family: 'Courier New', monospace; font-size: 32px; font-weight: 700; color: #059669; letter-spacing: 4px; }
        .warning { background-color: #fffbeb; border-left: 5px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 25px 0; font-size: 14px; color: #92400e; }
        .signature { margin-top: 40px; font-size: 14px; color: #4a5568; }
        .footer { background: linear-gradient(135deg, #059669, #10b981); color: white; text-align: center; padding: 15px; font-size: 12px; letter-spacing: 0.5px; opacity: 0.9; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="title">FeedTan Community Microfinance Group</div>
            <div class="sub-title">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
        </div>
        <div class="content">
            <p class="greeting">Hello {$user->name},</p>
            <p style="font-size: 14px; color: #4a5568;">You have requested to reset your password. Please use the OTP below to continue:</p>
            
            <div class="otp-box">
                <div class="otp-label">Password Reset OTP</div>
                <div class="otp-value">{$otp}</div>
            </div>

            <div class="warning">
                <strong>Important:</strong> This OTP will expire in 15 minutes. If you did not request a password reset, please ignore this email.
            </div>

            <p style="font-size: 14px; color: #4a5568;">Thank you for being a valued member!</p>

            <div class="signature">
                <p>Best regards,<br><strong>Team FeedTan CMG</strong></p>
                <p style="font-weight: 600; color: #059669;">Let's Grow Together! 🤝</p>
            </div>
        </div>
        <div class="footer">
            FeedTan CMG Payment System
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function hasEntryAccess(Request $request): bool
    {
        $entry = $request->session()->get(self::ENTRY_SESSION_KEY);

        if (!is_array($entry) || empty($entry['granted_at'])) {
            return false;
        }

        return (now()->timestamp - (int) $entry['granted_at']) <= (self::ENTRY_TTL_MINUTES * 60);
    }
}
