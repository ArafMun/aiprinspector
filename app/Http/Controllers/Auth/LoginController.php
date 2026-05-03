<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        \Log::info('LoginController: Login attempt', [
            'action' => 'user_login_attempt',
            'business_context' => 'user_management',
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'remember' => $request->boolean('remember'),
            'event_type' => 'authentication'
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Log::info('LoginController: Attempting authentication', [
            'action' => 'user_authentication_attempt',
            'business_context' => 'user_management',
            'email' => $request->email,
            'event_type' => 'authentication'
        ]);

        $remember = $request->boolean('remember');
        \Log::info('LoginController: Remember me value', [
            'action' => 'user_login_remember_check',
            'business_context' => 'user_management',
            'remember_checkbox' => $remember,
            'remember_input' => $request->input('remember'),
            'all_request_data' => $request->all(),
            'event_type' => 'authentication'
        ]);

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            \Log::info('LoginController: Authentication successful', [
                'action' => 'user_login_success',
                'business_context' => 'user_management',
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'is_admin_legacy' => $user->is_admin,
                'roles' => $user->roles->pluck('name')->toArray(),
                'permissions' => $user->getPermissions(),
                'is_admin_check' => $user->isAdmin(),
                'has_dashboard_permission' => $user->hasPermission('admin.dashboard'),
                'remember_me_used' => $remember,
                'remember_token_set' => !empty($user->remember_token),
                'remember_token_value' => $user->remember_token,
                'event_type' => 'authentication'
            ]);

            // Redirect admin users to admin dashboard (using role-based check)
            if ($user->isAdmin() || $user->hasPermission('admin.dashboard')) {
                \Log::info('LoginController: Redirecting to admin dashboard', [
                    'action' => 'user_redirect_admin_dashboard',
                    'business_context' => 'user_management',
                    'user_id' => $user->id,
                    'is_admin_legacy' => $user->isAdmin(),
                    'has_dashboard_permission' => $user->hasPermission('admin.dashboard'),
                    'redirect_url' => route('admin.dashboard'),
                    'event_type' => 'navigation'
                ]);
                return redirect()->intended(route('admin.dashboard'));
            }

            \Log::info('LoginController: Redirecting to home', [
                'action' => 'user_redirect_home',
                'business_context' => 'user_management',
                'user_id' => $user->id,
                'redirect_url' => '/',
                'event_type' => 'navigation'
            ]);
            return redirect()->intended('/');
        }

        \Log::warning('LoginController: Authentication failed', [
            'action' => 'user_login_failed',
            'business_context' => 'user_management',
            'email' => $request->email,
            'ip' => $request->ip(),
            'event_type' => 'authentication',
            'warning_type' => 'security'
        ]);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
