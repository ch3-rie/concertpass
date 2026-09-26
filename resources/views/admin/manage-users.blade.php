<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div>
                        <h2>Manage Users</h2>
                        <p>Administer user access, account roles, and account lifecycle actions.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <a href="{{ route('admin.users.create') }}" class="ad-btn ad-btn-primary">Add User</a>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Accounts</h3>
                    <div class="ad-table-wrap">
                        <table class="ad-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="ad-status {{ $user->role === 'admin' ? 'info' : 'success' }}">{{ ucfirst($user->role) }}</span>
                                        </td>
                                        <td style="display:flex; gap:0.45rem;">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="ad-btn">Edit</a>
                                            @if(auth()->id() !== $user->id)
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="true" data-confirm-message="Are you sure to delete this user account?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ad-btn ad-btn-logout" data-loading-text="Deleting...">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:0.8rem;">
                        {{ $users->links() }}
                    </div>
                </section>

            </main>
        </div>
    </section>

    @include('admin.partials.confirm-modal')
    @include('admin.partials.theme-script')
</x-app-layout>
