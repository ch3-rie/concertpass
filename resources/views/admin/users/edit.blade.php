<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')
            <main class="admin-main">
                @include('admin.partials.flash')
                <header class="admin-header">
                    <div>
                        <h2>Edit User</h2>
                        <p>Update account details and role settings for the selected user.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn"><span id="themeToggleIcon">◐</span></button>
                        <a href="{{ route('admin.users.index') }}" class="ad-btn">Back</a>
                    </div>
                </header>

                <section class="ad-card">
                    <h3 class="ad-panel-title">Edit User #{{ $user->id }}</h3>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="ad-form-grid-3" id="adminUserEditForm">
                        @csrf
                        @method('PUT')

                        <div class="ad-field">
                            <label class="ad-label" for="name">Name</label>
                            <input class="ad-input" id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="email">Email</label>
                            <input class="ad-input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="role">Role</label>
                            <select class="ad-select" id="role" name="role" required>
                                <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div class="ad-field">
                            <label class="ad-label" for="password">New Password (optional)</label>
                            <input class="ad-input" id="password" type="password" name="password">
                        </div>

                        <div class="ad-field ad-field-full">
                            <div class="ad-actions-row">
                                <a href="{{ route('admin.users.index') }}" class="ad-btn">Cancel</a>
                                <button type="submit" class="ad-btn ad-btn-primary" id="adminUserUpdateBtn" disabled>Update User</button>
                            </div>
                        </div>
                    </form>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
    <script>
        (function () {
            const editForm = document.getElementById('adminUserEditForm');
            const submitButton = document.getElementById('adminUserUpdateBtn');
            if (!editForm || !submitButton) {
                return;
            }

            const trackedFields = Array.from(editForm.elements).filter((field) => {
                return field.name && !field.disabled && field.type !== 'hidden';
            });
            const initialValues = new Map(
                trackedFields.map((field) => [
                    field.name,
                    field.type === 'file' ? '' : field.value,
                ])
            );

            const evaluateDirty = () => {
                const hasChanges = trackedFields.some((field) => {
                    if (field.type === 'file') {
                        return field.files && field.files.length > 0;
                    }
                    return field.value !== initialValues.get(field.name);
                });
                submitButton.disabled = !hasChanges;
            };

            trackedFields.forEach((field) => {
                field.addEventListener('input', evaluateDirty);
                field.addEventListener('change', evaluateDirty);
            });

            evaluateDirty();
        })();
    </script>
</x-app-layout>
