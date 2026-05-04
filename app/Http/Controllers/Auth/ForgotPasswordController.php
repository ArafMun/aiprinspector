<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the password reset request form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'We can\'t find a user with that email address.');
        }

        // Use Laravel's built-in password reset functionality
        $status = Password::sendResetLink($request->only('email'));

        // Log the password reset request
        \Log::channel('audit')->info('Password reset requested', [
            'action' => 'password_reset_requested',
            'business_context' => 'user_management',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'security'
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Password reset link has been sent to your email.');
        }

        return back()->with('error', 'Unable to send password reset link. Please try again.');
    }

    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Find the password reset record
        $resetRecord = \DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return back()->with('error', 'Invalid password reset token.');
        }

        // Check if token is not expired (24 hours)
        if ($resetRecord->created_at->lt(now()->subHours(24))) {
            return back()->with('error', 'Password reset token has expired.');
        }

        // Find the user and reset password
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'We can\'t find a user with that email address.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete the password reset record
        \DB::table('password_resets')
            ->where('email', $request->email)
            ->delete();

        // Log the password reset
        \Log::channel('audit')->info('Password reset completed', [
            'action' => 'password_reset_completed',
            'business_context' => 'user_management',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'security'
        ]);

        return redirect()->route('login')->with('status', 'Your password has been reset successfully.');
    }
}
