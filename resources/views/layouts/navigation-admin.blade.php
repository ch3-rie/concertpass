<nav class="admin-nav">
    <div class="admin-nav-container">
        <a href="{{ route('admin.dashboard') }}" class="admin-nav-brand">
            <span>🛠️</span>
            <span>ConcertPass Admin</span>
        </a>

        <div class="admin-nav-links">
            <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) active @endif">Overview</a>
            <a href="{{ route('admin.concerts.index') }}" class="@if(request()->routeIs('admin.concerts.*')) active @endif">Concerts</a>
            <a href="{{ route('admin.venues.index') }}" class="@if(request()->routeIs('admin.venues.*')) active @endif">Venues</a>
            <a href="{{ route('home') }}">View User Site</a>
        </div>

        <div class="admin-nav-user">
            <div class="admin-user-badge">
                <span>{{ explode(' ', Auth::user()->name)[0] }}</span>
                <span class="admin-role-badge">admin panel</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="admin-logout-btn">Log Out</button>
            </form>
        </div>
    </div>
</nav>
