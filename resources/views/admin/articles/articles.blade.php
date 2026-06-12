<x-admin>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-500">Content Management</span>
                <h1 class="text-2xl font-black text-[#111827] mt-2 tracking-tight">Platform Articles</h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="bg-white border border-gray-200 rounded-2xl px-4 py-2 shadow-sm">
                    <span class="text-xs text-gray-400 font-bold uppercase tracking-wider block">Total Articles</span>
                    <span class="text-lg font-black text-[#111827]">{{ $articles->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-[2rem] shadow-sm overflow-hidden p-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 px-5 py-5">
                <div>
                    <h2 class="text-lg font-black text-[#111827] tracking-tight">All Written Articles</h2>
                </div>
                <div class="relative w-full lg:w-64">
                    <form action="" method="GET">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..." class="w-full h-11 rounded-2xl border border-gray-200 bg-[#fafafa] pl-4 pr-4 text-sm font-medium text-gray-700 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition">

                            @if (request('search'))
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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
                @foreach ($articles as $article)
                    <a href='{{ route('admin.article.show',$article) }}' class="p-5 bg-[#efefef] rounded-2xl">
                        <h3 class="text-xl line-clamp-1 font-black leading-tight tracking-tight text-gray-800">{{ $article->title }}</h3>
                        <p class="mt-2 h-10 text-gray-600 text-sm leading-relaxed line-clamp-2">{{ $article->excerpt }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-gray-100">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Published</p>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase tracking-[0.18em] font-bold text-gray-400">Date</p>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">{{ $article->created_at->format('d M Y') }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="my-2 font-semibold">
                {{ $articles->appends(['search' => request('search')])->links() }}
            </div>
        </div>
    </div>
</x-admin>
