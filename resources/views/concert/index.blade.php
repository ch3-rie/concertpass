<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-3xl md:text-5xl font-extrabold text-white mb-2">ALL CONCERTS</h1>
            <p class="text-gray-400 text-lg">Discover and book tickets for upcoming concerts</p>
        </div>

        <!-- Filters -->
        <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 md:p-8 mb-12">
            <form method="GET" action="{{ route('concerts.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label for="search" style="display: block; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Concert or artist name" style="width: 100%; padding: 0.75rem; background: #111; border: 1px solid #333; border-radius: 0.25rem; color: #fff; font-size: 0.9rem;">
                </div>
                <div>
                    <label for="location" style="display: block; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Location</label>
                    <select name="location" id="location" style="width: 100%; padding: 0.75rem; background: #111; border: 1px solid #333; border-radius: 0.25rem; color: #fff; font-size: 0.9rem;">
                        <option value="" style="background: #111; color: #fff;">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }} style="background: #111; color: #fff;">{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date" style="display: block; color: #555; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.1em; margin-bottom: 0.75rem;">Date</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}" style="width: 100%; padding: 0.75rem; background: #111; border: 1px solid #333; border-radius: 0.25rem; color: #fff; font-size: 0.9rem;">
                </div>
                <div style="display: flex; align-items: end;">
                    <button type="submit" style="width: 100%; padding: 0.75rem; background: #ff6600; border: none; border-radius: 0.25rem; color: #000; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: background 0.3s;">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Concerts Grid -->
        @if($concerts->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-12">
                @foreach($concerts as $concert)
                    <div class="bg-gray-900 border border-gray-800 rounded-lg overflow-hidden transition-all duration-300 flex flex-col h-full">
                        @if($concert->poster_url)
                            <div class="w-full h-48 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $concert->poster_url) }}');"></div>
                        @else
                            <div class="w-full h-48 bg-gradient-to-br from-orange-500 to-pink-600 flex items-center justify-center text-5xl">
                                🎤
                            </div>
                        @endif
                        <div class="p-6 flex flex-col flex-1">
                            <h3 class="text-xl font-bold mb-2 text-white">{{ $concert->title }}</h3>
                            <p class="text-orange-500 font-semibold mb-2">by {{ $concert->artist }}</p>
                            <p class="text-gray-500 text-sm mb-2">{{ $concert->venue->location }}</p>
                            <p class="text-gray-400 text-sm mb-auto">{{ $concert->date->format('M d, Y') }} at {{ $concert->time->format('g:i A') }}</p>
                            <a href="{{ route('concerts.show', $concert) }}" class="inline-flex items-center justify-center w-full h-11 bg-orange-500 hover:bg-orange-600 border-none rounded text-black font-bold uppercase tracking-wider no-underline transition-colors duration-300">
                                Book Now
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="text-align: center;">
                {{ $concerts->links() }}
            </div>
        @else
            <div class="text-center py-16 px-8">
                <div class="text-6xl mb-8">🎟️</div>
                <h3 class="text-2xl font-bold mb-2 text-white">No concerts found</h3>
                <p class="text-gray-400 text-lg">Try adjusting your filters or check back later for new events.</p>
            </div>
        @endif
    </div>
</x-app-layout></content>