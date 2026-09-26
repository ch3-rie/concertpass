<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldEmail = $user->email;
        $oldName = $user->name;

        $user->fill($request->validated());

        $user->save();

        // Log profile update
        $changes = [];
        if ($oldName !== $user->name) {
            $changes[] = 'name';
        }
        if ($oldEmail !== $user->email) {
            $changes[] = 'email';
        }

        if (!empty($changes)) {
            ActivityLog::record([
                'user_id' => $user->id,
                'action' => 'update',
                'entity_type' => 'profile',
                'entity_id' => $user->id,
                'description' => 'Updated profile: ' . implode(', ', $changes),
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log account deletion before deleting
        ActivityLog::record([
            'user_id' => $user->id,
            'action' => 'delete',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'Deleted user account: ' . $user->email,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
