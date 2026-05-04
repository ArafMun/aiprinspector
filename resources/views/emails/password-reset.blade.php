@component('mail::message')
# Reset Password Notification

Hello!

You are receiving this email because we received a password reset request for your account.

@component('mail::button', ['url' => $resetUrl])
Reset Password
@endcomponent

This password reset link will expire in 24 hours.

If you did not request a password reset, no further action is required.

Regards,<br>
{{ config('app.name') }}
@endcomponent
