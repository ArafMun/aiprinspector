<?php

namespace App\Http\Controllers;

use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index()
    {
        return view('contact');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'newsletter' => 'nullable|string',
        ]);

        $validated['newsletter'] = $request->has('newsletter');

        if ($this->contactService->sendContactForm($validated)) {
            return back()->with('success', 'Thank you for your message! We will get back to you soon.');
        }

        return back()->with('error', 'Sorry, there was an error sending your message. Please try again later.')
            ->withInput();
    }
}
