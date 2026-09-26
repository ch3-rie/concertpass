@if(session('success'))
    <div class="ad-card" style="margin-bottom:0.8rem; border-color: rgba(20,184,166,.45);">
        <p>{{ session('success') }}</p>
    </div>
@endif

@if(session('warning'))
    <div class="ad-card" style="margin-bottom:0.8rem; border-color: rgba(245,158,11,.45);">
        <p>{{ session('warning') }}</p>
    </div>
@endif

@if(session('error'))
    <div class="ad-card" style="margin-bottom:0.8rem; border-color: rgba(220,38,38,.45);">
        <p>{{ session('error') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="ad-card" style="margin-bottom:0.8rem; border-color: rgba(245,158,11,.45);">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
