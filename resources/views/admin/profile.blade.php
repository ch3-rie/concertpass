<x-app-layout>
    <section class="admin-dashboard" id="adminDashboard">
        <div class="admin-shell">
            @include('admin.partials.sidebar')

            <main class="admin-main">
                @include('admin.partials.flash')

                <header class="admin-header">
                    <div>
                        <h2>Admin Profile</h2>
                        <p>Manage your account details and update your profile information.</p>
                    </div>
                    <div class="admin-header-actions">
                        <button type="button" class="ad-btn ad-icon-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle theme">
                            <span id="themeToggleIcon" aria-hidden="true">◐</span>
                        </button>
                        <button type="button" class="ad-btn ad-btn-primary" id="editProfileBtn">Edit Profile</button>
                    </div>
                </header>

                <section class="ad-grid ad-profile-page" id="profileLayout">
                    <article class="ad-card ad-profile-details-card">
                        <div class="ad-profile-overview">
                            <div class="ad-profile-avatar" aria-hidden="true">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="ad-panel-title">Profile Details</h3>
                                <p class="ad-profile-subtitle">Account information currently associated with your admin access.</p>
                            </div>
                        </div>

                        <div class="ad-detail-list ad-profile-detail-list">
                            <div class="ad-detail-item ad-profile-detail-item">
                                <p class="ad-label">Name</p>
                                <p class="value">{{ $user->name }}</p>
                            </div>
                            <div class="ad-detail-item ad-profile-detail-item">
                                <p class="ad-label">Email</p>
                                <p class="value">{{ $user->email }}</p>
                            </div>
                            <div class="ad-detail-item ad-profile-detail-item">
                                <p class="ad-label">Role</p>
                                <p class="value"><span class="ad-role-badge">{{ ucfirst($user->role) }}</span></p>
                            </div>
                        </div>
                    </article>

                    <article class="ad-card ad-profile-edit-card" id="editProfileCard">
                        <h3 class="ad-panel-title">Edit Profile</h3>
                        <form method="POST" action="{{ route('admin.profile.update') }}" class="ad-form-grid-2" id="adminProfileEditForm">
                            @csrf
                            @method('PATCH')

                            <div class="ad-field">
                                <label class="ad-label" for="name">Name</label>
                                <input class="ad-input" id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="email">Email</label>
                                <input class="ad-input" id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="ad-field ad-field-full">
                                <label class="ad-label" for="role">Role</label>
                                <select class="ad-select" id="role" name="role" required>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                                </select>
                            </div>

                            <div class="ad-field ad-field-full">
                                <h4 class="ad-section-label">Change Password</h4>
                                <p class="ad-profile-subtitle">Leave these fields blank if you do not want to change your password.</p>
                            </div>

                            <div class="ad-field ad-field-full">
                                <label class="ad-label" for="current_password">Current Password</label>
                                <input class="ad-input" id="current_password" type="password" name="current_password" autocomplete="current-password">
                                @error('current_password')
                                    <p class="ad-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="password">New Password</label>
                                <input class="ad-input" id="password" type="password" name="password" autocomplete="new-password">
                                @error('password')
                                    <p class="ad-field-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="ad-field">
                                <label class="ad-label" for="password_confirmation">Confirm New Password</label>
                                <input class="ad-input" id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                            </div>

                            <div class="ad-field ad-field-full">
                                <div class="ad-actions-row">
                                    <button type="submit" class="ad-btn ad-btn-primary" id="adminProfileSaveBtn" disabled>Save Profile</button>
                                </div>
                            </div>
                        </form>
                    </article>
                </section>
            </main>
        </div>
    </section>

    @include('admin.partials.theme-script')
    <script>
        (function () {
            const editCard = document.getElementById('editProfileCard');
            const editButton = document.getElementById('editProfileBtn');
            const profileLayout = document.getElementById('profileLayout');
            if (!editCard || !editButton || !profileLayout) {
                return;
            }

            const hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (hasValidationErrors) {
                editCard.classList.add('is-visible');
                profileLayout.classList.add('is-editing');
            }

            editButton.addEventListener('click', function () {
                editCard.classList.toggle('is-visible');
                profileLayout.classList.toggle('is-editing', editCard.classList.contains('is-visible'));

                if (editCard.classList.contains('is-visible')) {
                    const firstField = editCard.querySelector('input, select');
                    if (firstField) {
                        firstField.focus();
                    }
                }
            });

            const profileForm = document.getElementById('adminProfileEditForm');
            const profileSaveButton = document.getElementById('adminProfileSaveBtn');
            if (profileForm && profileSaveButton) {
                const trackedFields = Array.from(profileForm.elements).filter((field) => {
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

                    profileSaveButton.disabled = !hasChanges;
                };

                trackedFields.forEach((field) => {
                    field.addEventListener('input', evaluateDirty);
                    field.addEventListener('change', evaluateDirty);
                });

                evaluateDirty();
            }
        })();
    </script>
</x-app-layout>
