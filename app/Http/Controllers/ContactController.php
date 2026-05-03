<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmitted;

class ContactController extends Controller
{
    /**
     * Show the contact form.
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle the contact form submission.
     */
    public function submit(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'newsletter' => 'nullable|string',
        ]);

        // Convert newsletter checkbox to boolean
        $validated['newsletter'] = $request->has('newsletter');

        try {
            // Send email notification
            Mail::to(config('ai.contact_email'))->send(new ContactFormSubmitted($validated));

            // Store in database if needed (optional)
            // You can create a Contact model and store submissions there

            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            return back()->with('error', 'Sorry, there was an error sending your message. Please try again later.')
                        ->withInput();
        }
    }
}
