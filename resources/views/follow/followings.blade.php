<x-layout>
    <section class="max-w-3xl mx-auto px-4 py-10">
        <div class="flex items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-black">Following</h1>

                <p class="text-sm text-gray-500 mt-1">
                    {{ $followings->total() }} following total
                </p>
            </div>

            <form action="" method="GET" class="hidden sm:block">
                <div class="relative">
                    <input type="text" name="following" value="{{ request('following') }}" placeholder="Search following..." class="w-64 h-11 rounded-2xl border border-gray-200 bg-white pl-4 pr-11 text-sm font-medium text-black placeholder:text-gray-400 focus:outline-none focus:border-black transition-all duration-200">

                    @if (request('following'))
                        <a href="{{ url()->current() }}" class="absolute right-11 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-gray-200 hover:bg-black hover:text-white text-gray-600 flex items-center justify-center transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif

                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-black transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-3">
            @forelse ($followings as $following)
                @if ($following->followed)
                    <a href="{{ route('profile.show',$following->followed) }}" class="block group bg-white border border-gray-200 hover:border-gray-400 rounded-2xl px-5 py-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-14 h-14 rounded-full overflow-hidden bg-gray-100 shrink-0 border border-gray-200">
                                    @if ($following->followed->avtar)
                                        <img src="{{ asset('storage/' . $following->followed->avtar) }}" alt="{{ $following->followed->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-black text-white flex items-center justify-center text-sm font-bold uppercase">
                                            {{ substr($following->followed->name, 0, 2) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <h2 class="text-sm font-bold text-black truncate">
                                        {{ $following->followed->name }}
                                    </h2>

                                    <p class="text-xs text-gray-500 truncate mt-0.5">
                                        {{ $following->followed->email }}
                                    </p>
                                </div>
                            </div>

                            <button class="h-10 px-5 rounded-full bg-black text-white text-xs font-bold tracking-wide hover:bg-red-600 transition-all duration-300 shrink-0">
                                Visite Profile
                            </button>
                        </div>
                    </a>
                @endif
            @empty
                <div class="border border-dashed border-gray-300 rounded-3xl p-14 text-center bg-white">
                    <h2 class="text-lg font-bold text-black">No Following Found</h2>
                    <p class="text-sm text-gray-500 mt-2">Users you follow will appear here.</p>
                </div>
            @endforelse
        </div>


        <div class="mt-8">
            <a href="{{ route('profile.show',$user) }}" class="group inline-flex items-center gap-2 text-sm font-extrabold text-gray-500 hover:text-gray-800 transition-colors duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4 text-gray-400 group-hover:text-gray-700 group-hover:-translate-x-1 transition-transform duration-150">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Go back</span>
            </a>
        </div>

        <div class="mt-8">
            {{ $followings->links() }}
        </div>
    </section>
</x-layout>
