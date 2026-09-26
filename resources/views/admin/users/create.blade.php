<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div>
                        <h2>Add User</h2>
                        <p>Create a new account with role-based access controls.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.users.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Account Information</h3>
                    <form method="POST" action="{{ route('admin.users.store') }}" class="ad-form-grid-3">
                        @csrf
                        <div class="ad-field">
                            <label class="ad-label" for="name">Name</label>
                            <input class="ad-input" id="name" type="text" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="email">Email</label>
                            <input class="ad-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="role">Role</label>
                            <select class="ad-select" id="role" name="role" required>
                                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="ad-field">
                            <label class="ad-label" for="password">Password</label>
                            <input class="ad-input" id="password" type="password" name="password" required>
                        </div>
                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <button type="submit" class="ad-btn ad-btn-primary">Create User</button>
                            </div>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
</x-app-layout>
