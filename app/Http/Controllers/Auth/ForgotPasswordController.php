<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

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

        $result = $this->authService->sendPasswordResetLink($request->email);

        if ($result === 'user_not_found') {
            return back()->with('error', 'We can\'t find a user with that email address.');
        }

        if ($result === 'success') {
            return back()->with('status', 'Password reset link has been sent to your email.');
        }

        return back()->with('error', 'Unable to send password reset link. Please try again.');
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

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

        $result = $this->authService->resetPassword([
            'token' => $request->token,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (! $result) {
            return back()->with('error', 'Invalid or expired password reset token.');
        }

        return redirect()->route('login')->with('status', 'Your password has been reset successfully.');
    }
}
