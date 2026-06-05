<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Show the form to request a password reset link.
     *
     * @return \Illuminate\View\View
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a new password to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->is_locked) {
            throw ValidationException::withMessages([
                'email' => ['Your account is locked. Please contact the administrator.'],
            ]);
        }

        // Generate a new random password
        $newPassword = Str::random(10);

        // Update user's password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Send email with new password
        try {
            // Configure mail system with database settings
            $emailConfig = new EmailConfigService();
            $emailConfig->configureMail();
            
            $config = (new EmailConfigService())->getEmailConfig();
            
            $emailTemplate = $this->buildPasswordResetEmailTemplate($user, $newPassword);
            
            Mail::html($emailTemplate['html'], function ($message) use ($user, $emailTemplate, $config) {
                $message->to($user->email)
                        ->subject($emailTemplate['subject'])
                        ->from($config['from_address'], $config['from_name']);
            });
            
            Log::info('Password reset email sent successfully', [
                'email' => $user->email,
                'user_id' => $user->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $user->email
            ]);
            
            throw ValidationException::withMessages([
                'email' => ['Failed to send the new password to your email. Please try again later.'],
            ]);
        }

        return redirect()->route('login')->with('success', 'A new password has been sent to your email address!');
    }
    
    /**
     * Build password reset email template
     */
    private function buildPasswordResetEmailTemplate(User $user, string $newPassword): array
    {
        $subject = "Your New Password - FeedTan CMG";
        
        $htmlBody = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Your New Password - FeedTan CMG</title>
    <link href=\"https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap\" rel=\"stylesheet\">
    <style>
        body { margin: 0; padding: 0; background-color: #f0f4f8; font-family: 'Poppins', sans-serif; color: #333; line-height: 1.6; }
        .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #059669, #10b981); padding: 30px 25px; text-align: center; color: white; }
        .header .title { font-size: 26px; font-weight: 700; margin-bottom: 5px; }
        .header .sub-title { font-size: 14px; opacity: 0.9; }
        .content { padding: 30px 25px; }
        .greeting { font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 15px; }
        
        .card { background-color: #f7fafc; border: 1px solid #edf2f7; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        
        .password-box { background: linear-gradient(135deg, #f0fdf4, #d1fae5); border: 2px dashed #10b981; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .password-label { font-size: 12px; text-transform: uppercase; font-weight: 700; color: #065f46; letter-spacing: 0.5px; margin-bottom: 8px; }
        .password-value { font-family: 'Courier New', monospace; font-size: 24px; font-weight: 700; color: #059669; letter-spacing: 2px; }
        
        .warning { background-color: #fffbeb; border-left: 5px solid #f59e0b; padding: 15px; border-radius: 8px; margin: 25px 0; font-size: 14px; color: #92400e; }
        
        .signature { margin-top: 40px; font-size: 14px; color: #4a5568; }
        .footer { background: linear-gradient(135deg, #059669, #10b981); color: white; text-align: center; padding: 15px; font-size: 12px; letter-spacing: 0.5px; opacity: 0.9; }
        
        .transaction-details { background-color: #f0fff4; border: 1px solid #c6f6d5; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .transaction-details h4 { color: #2f855a; margin-bottom: 15px; font-size: 16px; }
        .transaction-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .transaction-row:last-child { border-bottom: none; }
        .transaction-label { color: #4a5568; font-weight: 500; }
        .transaction-value { color: #2d3748; font-weight: 600; }
    </style>
</head>
<body>
    <div class=\"email-container\">
        <div class=\"header\">
            <div class=\"title\">FeedTan Community Microfinance Group</div>
            <div class=\"sub-title\">P.O.Box 7744, Ushirika Sokoine Road, Moshi, Kilimanjaro, Tanzania</div>
        </div>
        <div class=\"content\">
            <p class=\"greeting\">Hello {$user->name},</p>
            <p style=\"font-size: 14px; color: #4a5568;\">You have requested a new password for your account. Below are your login details:</p>
            
            <div class=\"transaction-details\">
                <h4>&#128273; Account Login Details</h4>
                <div class=\"transaction-row\">
                    <span class=\"transaction-label\">Email Address:</span>
                    <span class=\"transaction-value\">{$user->email}</span>
                </div>
            </div>

            <div class=\"password-box\">
                <div class=\"password-label\">Your New Password</div>
                <div class=\"password-value\">{$newPassword}</div>
            </div>

            <div class=\"warning\">
                <strong>Important:</strong> For security reasons, we recommend changing this password immediately after logging in. You can do this from your profile page.
            </div>
            
            <p style=\"font-size: 14px; color: #4a5568;\">Thank you for being a valued member of FeedTan Community Microfinance Group!</p>

            <div class=\"signature\">
                <p>Best regards,<br><strong>Timu ya FeedTan CMG</strong></p>
                <p style=\"font-weight: 600; color: #059669;\">Let's Grow Together! &#x1F91D;</p>
            </div>
        </div>
        <div class=\"footer\">
            FeedTan CMG Payment System V1.1.0.2026
        </div>
    </div>
</body>
</html>";

        return [
            'subject' => $subject,
            'html' => $htmlBody
        ];
    }
}
