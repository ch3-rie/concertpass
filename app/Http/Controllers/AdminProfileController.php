<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('admin.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $oldName = $user->name;
        $oldEmail = $user->email;
        $oldRole = $user->role;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if (!is_string($value) || $value === '') {
                        return;
                    }

                    if (Hash::check($value, $user->password)) {
                        $fail('New password cannot be the same as the old password.');
                    }
                },
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $changes = [];
        if ($oldName !== $user->name) {
            $changes[] = 'name';
        }
        if ($oldEmail !== $user->email) {
            $changes[] = 'email';
        }
        if ($oldRole !== $user->role) {
            $changes[] = 'role';
        }
        if (!empty($validated['password'])) {
            $changes[] = 'password';
        }

        if (!empty($changes)) {
            ActivityLog::record([
                'user_id' => $user->id,
                'action' => 'update',
                'entity_type' => 'profile',
                'entity_id' => $user->id,
                'description' => 'Updated admin profile: '.implode(', ', $changes),
            ]);
        }

        return redirect()
            ->route('admin.profile.show')
            ->with('success', 'Profile updated successfully.');
    }
}
