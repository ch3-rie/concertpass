<aside class="admin-sidebar">
    <div class="admin-brand">
        <span class="admin-brand-mark">CM</span>
        <div>
            <p>Concert Control</p>
            <h1>Admin Panel</h1>
        </div>
    </div>

    <ul class="admin-nav-list">
        <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Dashboard</a></li>
        <li><a href="{{ route('admin.concerts.index') }}" class="{{ request()->routeIs('admin.concerts.*') ? 'is-active' : '' }}">Manage Concerts</a></li>
        <li><a href="{{ route('admin.ticket-management') }}" class="{{ request()->routeIs('admin.ticket-management') ? 'is-active' : '' }}">Ticket Management</a></li>
        <li><a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'is-active' : '' }}">Manage Transactions</a></li>
        <li><a href="{{ route('admin.venues.index') }}" class="{{ request()->routeIs('admin.venues.*') ? 'is-active' : '' }}">Manage Venues</a></li>
        <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">Manage Users</a></li>
        <li><a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'is-active' : '' }}">Analytics</a></li>
        <li><a href="{{ route('admin.activity-logs') }}" class="{{ request()->routeIs('admin.activity-logs') ? 'is-active' : '' }}">Activity Logs</a></li>
    </ul>

    <div class="admin-sidebar-actions">
        <a href="{{ route('admin.profile.show') }}" class="ad-btn ad-btn-profile-action {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}">
            Admin Profile
        </a>

        <form method="POST" action="{{ route('logout') }}" class="admin-sidebar-logout">
            @csrf
            <button type="submit" class="ad-btn ad-btn-logout-action">Logout</button>
        </form>
    </div>
</aside>
