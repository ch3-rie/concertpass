<x-app-layout>
    <!-- HERO SECTION -->
    <section class="max-w-7xl mx-auto px-4 py-8 md:px-8 md:py-12 text-center">
        <h1 class="text-3xl md:text-5xl font-extrabold mb-4 text-white">Book Tickets Of Your Favorite Singers!</h1>
        <p class="text-base md:text-lg text-orange-500 font-semibold mb-12">Make Your Night Unforgettable</p>
    </section>

    <!-- CAROUSEL SECTION -->
    <section class="max-w-7xl mx-auto px-4 py-8 md:px-8">
        <div class="flex gap-6 overflow-x-auto pb-4">
            @foreach($concerts->take(8) as $concert)
                <div class="flex-none w-72 bg-gray-800 rounded-lg overflow-hidden cursor-pointer transition-all duration-300 border border-gray-700 flex flex-col">
                    @if($concert->poster_url)
                    <div class="w-full h-48 bg-cover bg-center flex items-end p-6 text-white text-3xl" style="background-image: url('{{ asset('storage/' . $concert->poster_url) }}');">
                        &nbsp;
                    </div>
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-orange-500/80 to-pink-500/60 flex items-end p-6 text-white text-3xl">
                        🎤
                    </div>
                @endif
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-xl font-bold mb-2 text-white">{{ $concert->title }}</h3>
                        <p class="text-orange-500 font-semibold mb-2">{{ $concert->artist }}</p>
                        <p class="text-gray-300 text-sm mb-auto">{{ $concert->date->format('M d, Y') }}</p>
                        <a href="{{ route('concerts.show', $concert) }}" class="btn btn-primary w-full mt-0 inline-flex justify-center h-11 items-center">Book Now</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('concerts.index') }}" class="btn btn-primary px-12 py-3 text-lg">View All Concerts</a>
        </div>
    </section>

    <!-- OUR BENEFITS SECTION -->
    <section class="max-w-7xl mx-auto px-4 py-12 md:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-12 text-white">Our Benefits</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="bg-gray-800 p-8 rounded-lg text-center border border-gray-700">
                <div class="text-5xl mb-4">💺</div>
                <h3 class="text-xl font-bold mb-2 text-white">Interactive Seat Selection</h3>
                <p class="text-gray-400 text-sm">Choose your preferred seats with real-time availability</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg text-center border border-gray-700">
                <div class="text-5xl mb-4">🔒</div>
                <h3 class="text-xl font-bold mb-2 text-white">Secure Payment Processing</h3>
                <p class="text-gray-400 text-sm">Credit card payments with encrypted transactions and instant confirmation</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg text-center border border-gray-700">
                <div class="text-5xl mb-4">📱</div>
                <h3 class="text-xl font-bold mb-2 text-white">Instant Ticket Delivery</h3>
                <p class="text-gray-400 text-sm">Get your digital tickets immediately after payment confirmation</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg text-center border border-gray-700">
                <div class="text-5xl mb-4">📊</div>
                <h3 class="text-xl font-bold mb-2 text-white">Easy Booking Management</h3>
                <p class="text-gray-400 text-sm">Track all your tickets, bookings, and payment history in one place</p>
            </div>
        </div>
    </section>

    <!-- TIME IS RUNNING OUT SECTION -->
    <section class="max-w-7xl mx-auto px-4 py-12 md:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-4 text-white">Time is Running Out!</h2>
        <p class="text-center text-gray-400 mb-12">Explore newly released events and book your tickets</p>
        
        <div class="flex gap-6 overflow-x-auto pb-4">
            @foreach($concerts->take(5) as $concert)
                <div class="flex-none w-64 bg-gray-800 rounded-lg overflow-hidden border border-gray-700 cursor-pointer transition-all duration-300 flex flex-col">
                    @if($concert->poster_url)
                        <div class="w-full h-40 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $concert->poster_url) }}');"></div>
                    @else
                        <div class="w-full h-40 bg-gradient-to-br from-orange-500 to-pink-600 flex items-center justify-center text-6xl">
                            🎵
                        </div>
                    @endif
                    <div class="p-6 flex flex-col flex-1">
                        <h3 class="text-xl font-bold mb-2 text-white">{{ $concert->artist }}</h3>
                        <p class="text-gray-400 text-sm mb-auto">{{ $concert->date->format('M d, Y') }}</p>
                        <a href="{{ route('concerts.show', $concert) }}" class="btn btn-secondary w-full mt-0 inline-flex justify-center h-11 items-center">Book Now</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- 4 EASY STEPS SECTION -->
    <section class="max-w-7xl mx-auto px-4 py-12 md:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-12 text-white">4 Easy Steps To Buy a Ticket!</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div class="bg-gray-800 p-8 rounded-lg border border-gray-700">
                <div class="w-16 h-16 bg-orange-500 rounded-full mx-auto mb-6 flex items-center justify-center text-xl font-bold text-black">1</div>
                <h3 class="text-lg font-bold mb-2 text-white">Browse & Select Concert</h3>
                <p class="text-gray-400 text-sm">Find your favorite concerts by artist, venue, or date</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg border border-gray-700">
                <div class="w-16 h-16 bg-pink-600 rounded-full mx-auto mb-6 flex items-center justify-center text-xl font-bold text-black">2</div>
                <h3 class="text-lg font-bold mb-2 text-white">Pick Your Seats</h3>
                <p class="text-gray-400 text-sm">Choose your preferred seats from the interactive seat map</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg border border-gray-700">
                <div class="w-16 h-16 bg-cyan-400 rounded-full mx-auto mb-6 flex items-center justify-center text-xl font-bold text-black">3</div>
                <h3 class="text-lg font-bold mb-2 text-white">Select & Pay</h3>
                <p class="text-gray-400 text-sm">Choose ticket options and complete payment securely</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-lg border border-gray-700">
                <div class="w-16 h-16 bg-orange-500 rounded-full mx-auto mb-6 flex items-center justify-center text-xl font-bold text-black">4</div>
                <h3 class="text-lg font-bold mb-2 text-white">Get Your Ticket</h3>
                <p class="text-gray-400 text-sm">Receive instant digital ticket for venue entry</p>
            </div>
        </div>
    </section>
</x-app-layout>
