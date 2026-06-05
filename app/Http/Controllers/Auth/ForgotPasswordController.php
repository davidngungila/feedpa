<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Display the form to request a password reset link.
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
            Mail::send([], [], function ($message) use ($user, $newPassword) {
                $config = (new \App\Services\EmailConfigService())->getEmailConfig();
                $message->to($user->email)
                        ->subject('Your New Password')
                        ->from($config['from_address'], $config['from_name'])
                        ->html('
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <meta charset="UTF-8">
                                <title>Password Reset</title>
                            </head>
                            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
                                <table role="presentation" cellspacing="0" cellpadding="0" width="100%" style="max-width: 600px; margin: 30px auto;">
                                    <tr>
                                        <td style="background-color: #10b981; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
                                            <h1 style="margin: 0; font-size: 24px;">FEEDTAN DIGITAL PAYMENT SYSTEM</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="background-color: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                            <p style="font-size: 16px; line-height: 1.6; color: #333;">Hello ' . $user->name . ',</p>
                                            <p style="font-size: 16px; line-height: 1.6; color: #333;">You have requested a new password for your account. Here are your login details:</p>
                                            <table role="presentation" cellspacing="0" cellpadding="10" style="background-color: #f9fafb; width: 100%; margin: 20px 0; border-radius: 8px;">
                                                <tr>
                                                    <td style="font-weight: bold; color: #065f46;">Email:</td>
                                                    <td style="color: #065f46;">' . $user->email . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: bold; color: #065f46;">Password:</td>
                                                    <td style="color: #065f46; font-family: monospace; font-size: 18px;">' . $newPassword . '</td>
                                                </tr>
                                            </table>
                                            <p style="font-size: 16px; line-height: 1.6; color: #333;">For security reasons, we recommend changing this password after logging in.</p>
                                            <p style="font-size: 16px; line-height: 1.6; color: #333;">Thank you!</p>
                                            <p style="font-size: 14px; line-height: 1.6; color: #6b7280; margin-top: 30px;">If you did not request this password reset, please contact the administrator immediately.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center; padding: 20px; color: #9ca3af; font-size: 12px;">
                                            &copy; ' . date('Y') . ' FEEDTAN Community Microfinance Group. All rights reserved.
                                        </td>
                                    </tr>
                                </table>
                            </body>
                            </html>
                        ');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send password reset email: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => ['Failed to send the new password to your email. Please try again later.'],
            ]);
        }

        return back()->with('status', 'A new password has been sent to your email address!');
    }
}
