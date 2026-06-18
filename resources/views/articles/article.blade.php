<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <h1 class="text-5xl font-black tracking-tight text-gray-900">
                Latest Articles
            </h1>

            <p class="mt-4 text-sm text-gray-500 font-medium max-w-lg leading-relaxed">
                Explore fresh perspectives and deep dives from our community of thinkers.
            </p>
        </div>

        <div class="w-full max-w-md lg:ml-auto">
            <form action="" wire:submit.prevent="$refresh" method="GET" class="relative flex items-center bg-[#1c1f24] border border-white/10 rounded-xl focus-within:border-gray-500 transition-all shadow-2xl">

                <input type="text" wire:model.blur="search" placeholder="Search archives..." class="flex-1 pl-5 pr-24 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent" value="{{ request('search') }}">

                @if ($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute right-28 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-[#1c1f24] hover:bg-black hover:text-white text-gray-200 flex items-center justify-center transition-all duration-200 z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif

                <button type="submit" class="bg-white text-black px-6 py-3.5 text-sm font-bold hover:bg-gray-200 rounded-r-xl transition-all active:scale-95">Search</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-10">
        @include('components.articleLayout')
    </div>
    <div class="mt-8 font-semibold">
        {{ $articles->appends(['search' => request('search')])->links() }}
    </div>
</section>
