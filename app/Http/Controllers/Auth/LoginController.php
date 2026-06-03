<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');
        $user = $this->authService->attemptLogin($credentials, $remember);

        $request->session()->regenerate();

        if ($user->isAdmin() || $user->hasPermission('admin.dashboard')) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        $this->authService->logout();

        return redirect('/');
    }
}
