<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view (mock flow).
     */
    public function create(Request $request): View
    {
        if (! $request->session()->has('mock_password_reset_email')) {
            abort(403, 'Reset session expired. Please request a new password reset.');
        }

        return view('auth.reset-password', [
            'request' => $request,
            'email' => $request->session()->get('mock_password_reset_email'),
        ]);
    }

    /**
     * Handle an incoming new password request (mock flow).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $sessionEmail = $request->session()->pull('mock_password_reset_email');

        if (! $sessionEmail || $sessionEmail !== $request->input('email')) {
            throw ValidationException::withMessages([
                'email' => 'Reset session expired. Please request a new password reset.',
            ]);
        }

        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user && Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Please choose a new password that is different from your old password.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been successfully reset.');
    }
}
