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
            <form action="" method="GET" class="relative flex items-center bg-[#1c1f24] border border-white/10 rounded-xl focus-within:border-gray-500 transition-all shadow-2xl">
                <input type="text" wire:model.live="search" placeholder="Search archives..." class="flex-1 pl-5 pr-24 py-3.5 outline-none text-gray-200 text-sm font-light bg-transparent" value="{{ request('search') }}">

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

    @if($articles->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8 mt-10">
            @include('components.articleLayout')
        </div>
    @else
        <div class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-200 rounded-[2rem] m-4 bg-[#fafafa]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            
            <h3 class="text-md font-black text-gray-700">No Articles Yet</h3>
            
            <p class="text-sm text-gray-500 mt-1 max-w-xs">
                We have not published any articles yet. Please create one!
            </p>

            <div class="mt-5">
                <a href="{{ route('articles.create') }}" 
                wire:navigate 
                class="inline-flex items-center gap-2 bg-black hover:bg-gray-800 text-white font-semibold text-xs px-5 py-2.5 rounded-xl transition-all shadow-md group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Create Fresh Article
                </a>
            </div>
        </div>
    @endif
    <div class="mt-8 font-semibold">
        {{ $articles->appends(['search' => request('search')])->links() }}
    </div>
</section>
