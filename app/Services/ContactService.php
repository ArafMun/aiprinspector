<?php

namespace App\Services;

use App\Mail\ContactFormSubmitted;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function sendContactForm(array $data): bool
    {
        try {
            Mail::to(config('ai.contact_email'))->send(new ContactFormSubmitted($data));

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
