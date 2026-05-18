<x-admin>
    <div class="space-y-4 max-w-[1600px] mx-auto">
        {{-- TOP HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-[#111827]">
                    Dashboard Overview
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    Welcome back, {{ auth()->user()->name }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 shadow-sm self-start sm:self-auto">
                <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black">
                    Today
                </p>
                <p class="text-xs font-bold text-gray-700 mt-0.5">
                    {{ now()->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- STATS GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- ARTICLES --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Articles
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $articles->count() }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total published articles
                </p>
            </div>

            {{-- USERS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Users
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $users->count() }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Registered users
                </p>
            </div>

            {{-- COMMENTS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H12m3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0Zm0 0H15m-8.25 8.25h10.5A2.25 2.25 0 0019.5 15.75v-7.5A2.25 2.25 0 0017.25 6H6.75A2.25 2.25 0 004.5 8.25v7.5A2.25 2.25 0 006.75 18Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Comments
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $comments->count() }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total article comments
                </p>
            </div>

            {{-- VIEWS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="w-5 h-5 text-gray-700">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 5.25 12 5.25 21.75 12 21.75 12 18.75 18.75 12 18.75 2.25 12 2.25 12Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5Z" />
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em]">
                        Views
                    </span>
                </div>
                <h2 class="mt-4 text-2xl font-black text-[#111827]">
                    {{ $views->count() }}
                </h2>
                <p class="mt-1 text-xs text-gray-400">
                    Total article views
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 items-start">
            {{-- LATEST ARTICLE ANALYSIS (Shows Top 6 Articles) --}}
            <div class="xl:col-span-2 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-black text-[#111827]">
                        Latest Article Analysis
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Performance breakdown of your top 6 highest performing items
                    </p>
                </div>

                {{-- ARTICLE ITEMS LIST CONTAINER --}}
                <div class="space-y-2">
                    @foreach ($articles->sortByDesc('id')->take(4) as $article)
                        <div class="flex items-center justify-between p-3 bg-[#f3f4f6] rounded-xl hover:bg-gray-200/60 transition group">
                        <div class="flex items-center gap-3 truncate max-w-[70%]">
                            <span class="w-6 h-6 rounded-md bg-gray-900 text-white flex items-center justify-center font-bold text-[10px]">01</span>
                            <span class="text-xs font-bold text-gray-900 truncate">{{ $article->title }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-right">
                            <div>
                                <span class="text-[9px] uppercase font-black text-gray-400 tracking-wider block">Views</span>
                                <span class="text-xs font-black text-[#111827]">{{  }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT PANEL (Replicating exact layout tracker elements from your image) --}}
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col gap-5">
                <div class="border-b border-gray-100 pb-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Remaining Drafts</span>
                    <span class="text-2xl font-extrabold text-[#111827] mt-0.5 block">14</span>
                </div>

                <div class="border-b border-gray-100 pb-3">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Likes</span>
                    <span class="text-2xl font-extrabold text-[#111827] mt-0.5 block">84,320</span>
                </div>

                <div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Highest Article Poster Name</span>
                    <div class="flex items-center gap-3 mt-2 bg-[#f3f4f6] p-2.5 rounded-xl border border-gray-100">
                        <img class="w-8 h-8 rounded-full object-cover" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=80&auto=format&fit=crop&q=60" alt="">
                        <div class="truncate">
                            <span class="text-xs font-bold text-[#111827] block truncate">Sarah Jenkins</span>
                            <span class="text-[10px] text-gray-500 block">42 Articles written</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5 pt-1">
                    <div class="flex items-center gap-4 text-[10px] font-bold text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-[#2a292e] rounded-sm"></span> High Engagement</span>
                        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-yellow-400 rounded-sm"></span> In Draft</span>
                    </div>
                    <div class="w-full h-8 bg-[#2a292e] rounded-lg"></div>
                    <div class="w-full h-8 bg-yellow-400 rounded-lg"></div>
                </div>
            </div>
        </div>
    </div>
</x-admin>
{{-- 248 --}}
