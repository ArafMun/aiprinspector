<?php

namespace App\Services;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function attemptLogin(array $credentials, bool $remember): User
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        return Auth::user();
    }

    public function registerUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);
    }

    public function sendPasswordResetLink(string $email): string
    {
        $user = User::findByEmail($email);

        if (! $user) {
            return 'user_not_found';
        }

        $status = Password::sendResetLink(['email' => $email]);

        $this->logPasswordResetRequest($user, request());

        return $status === Password::RESET_LINK_SENT ? 'success' : 'failed';
    }

    public function resetPassword(array $data): bool
    {
        $resetRecord = PasswordReset::findByEmail($data['email']);

        if (! $resetRecord || ! Hash::check($data['token'], $resetRecord->token)) {
            return false;
        }

        if ($resetRecord->created_at->lt(now()->subHours(24))) {
            return false;
        }

        $user = User::findByEmail($data['email']);

        if (! $user) {
            return false;
        }

        $user->update(['password' => Hash::make($data['password'])]);

        PasswordReset::deleteByEmail($data['email']);

        $this->logPasswordResetCompleted($user, request());

        return true;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    private function logPasswordResetRequest(User $user, $request): void
    {
        \Log::channel('audit')->info('Password reset requested', [
            'action' => 'password_reset_requested',
            'business_context' => 'user_management',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'security',
        ]);
    }

    private function logPasswordResetCompleted(User $user, $request): void
    {
        \Log::channel('audit')->info('Password reset completed', [
            'action' => 'password_reset_completed',
            'business_context' => 'user_management',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event_type' => 'security',
        ]);
    }
}
