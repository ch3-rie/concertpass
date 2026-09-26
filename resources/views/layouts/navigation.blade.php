<nav>
    <div class="nav-container">
        <a href="{{ route('home') }}" class="nav-brand">
            <span>🎫</span>
            <span>ConcertPass</span>
        </a>

        <div class="nav-links">
            <a href="{{ route('home') }}" class="@if(request()->routeIs('home')) active @endif">Home</a>
            <a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard')) active @endif">Dashboard</a>
            @auth
                <a href="{{ route('bookings.index') }}" class="@if(request()->routeIs('bookings.*')) active @endif">My Bookings</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.*')) active @endif">Admin</a>
                @endif
            @endauth
        </div>

        <div class="nav-user">
            @auth
                <a href="{{ route('profile.edit') }}" class="user-badge user-badge-link">
                    <span>{{ explode(' ', Auth::user()->name)[0] }}</span>
                    <span class="role">{{ auth()->user()->role }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-small">Log Out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-small">Sign In</a>
            @endauth
        </div>
    </div>
</nav>
