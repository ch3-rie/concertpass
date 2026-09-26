<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request (mock flow).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'No existing account was found for this email address.',
        ]);

        // Mock-only flow: keep the email in session and move to reset page.
        $request->session()->put('mock_password_reset_email', $request->input('email'));

        return redirect()
            ->route('password.reset', ['token' => 'mock-reset-token'])
            ->with('status', 'If this email exists, a password reset link has been sent.');
    }
}
