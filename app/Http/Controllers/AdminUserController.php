<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function index(Request $request)
    {
        $users = User::orderByDesc('id')->paginate(15);

        return view('admin.manage-users', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create($validated);

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'create',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'Created user account: '.$user->email,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'user'])],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'update',
            'entity_type' => 'user',
            'entity_id' => $user->id,
            'description' => 'Updated user account: '.$user->email,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $email = $user->email;
        $id = $user->id;
        $user->delete();

        ActivityLog::record([
            'user_id' => auth()->id(),
            'action' => 'delete',
            'entity_type' => 'user',
            'entity_id' => $id,
            'description' => 'Deleted user account: '.$email,
        ]);

        return back()->with('success', 'User deleted successfully.');
    }
}
