<x-layout>
    <section class="bg-[#f5f7fb] min-h-screen pb-20">

        {{-- HERO SECTION --}}
        <div class="relative overflow-hidden bg-black">

            <div class="absolute inset-0 opacity-20">
                <img
                    src="{{ asset('storage/' . $article->cover_path) }}"
                    alt="{{ $article->title }}"
                    class="w-full h-full object-cover"
                >
            </div>

            <div class="relative max-w-7xl mx-auto px-5 lg:px-8 py-20 lg:py-28">

                <div class="max-w-4xl">

                    {{-- CATEGORY + STATUS --}}
                    <div class="flex flex-wrap items-center gap-3 mb-6">

                        <span class="bg-white/10 backdrop-blur-md border border-white/10 text-white px-4 py-2 rounded-full text-[11px] font-black uppercase tracking-[0.2em]">
                            {{ $article->category->name }}
                        </span>

                        <span class="bg-emerald-500 text-white px-4 py-2 rounded-full text-[11px] font-black uppercase tracking-[0.2em]">
                            {{ $article->status }}
                        </span>

                    </div>

                    {{-- TITLE --}}
                    <h1 class="text-white text-4xl md:text-5xl lg:text-7xl font-black leading-tight tracking-tight max-w-5xl">
                        {{ $article->title }}
                    </h1>

                    {{-- EXCERPT --}}
                    <p class="mt-8 text-gray-300 text-lg md:text-xl leading-relaxed max-w-3xl font-medium">
                        {{ $article->excerpt }}
                    </p>

                    {{-- META INFO --}}
                    <div class="mt-10 flex flex-wrap items-center gap-6 text-white/80 text-sm font-semibold">

                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-white/10 border border-white/10 flex items-center justify-center text-lg font-black uppercase">
                                {{ strtoupper(substr($article->user->name, 0, 1)) }}
                            </div>

                            <div>
                                <p class="font-black text-white uppercase tracking-wide text-xs">
                                    {{ $article->user->name }}
                                </p>
                                <p class="text-gray-400 text-xs mt-1">
                                    Author
                                </p>
                            </div>
                        </div>

                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">
                                Published
                            </p>
                            <p class="mt-1 font-bold text-white">
                                {{ $article->created_at->format('F d, Y') }}
                            </p>
                        </div>

                        <div class="h-10 w-px bg-white/10 hidden md:block"></div>

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-black">
                                Views
                            </p>
                            <p class="mt-1 font-bold text-white">
                                {{ number_format($article->view_count) }} Views
                            </p>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="max-w-7xl mx-auto px-5 lg:px-8 mt-10">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

                {{-- ARTICLE CONTENT --}}
                <div class="lg:col-span-8">

                    <article class="bg-white rounded-[2rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden">

                        {{-- FEATURE IMAGE --}}
                        <div class="overflow-hidden">
                            <img
                                src="{{ asset('storage/' . $article->cover_path) }}"
                                alt="{{ $article->title }}"
                                class="w-full h-[250px] md:h-[450px] object-cover hover:scale-105 duration-700"
                            >
                        </div>

                        {{-- BODY --}}
                        <div class="p-6 md:p-10 lg:p-14">

                            <div class="flex items-center gap-3 mb-10">

                                <span class="bg-black text-white px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.2em]">
                                    {{ $article->category->name }}
                                </span>

                                <span class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    {{ $article->created_at->diffForHumans() }}
                                </span>

                            </div>

                            <div class="prose prose-lg max-w-none prose-headings:font-black prose-headings:text-gray-900 prose-p:text-gray-700 prose-p:leading-9 prose-p:text-[17px] prose-img:rounded-3xl prose-a:text-black hover:prose-a:text-gray-600">

                                {!! nl2br(e($article->body)) !!}

                            </div>

                        </div>

                    </article>

                    {{-- AUTHOR CARD --}}
                    <div class="mt-10 bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100 p-8 md:p-10">

                        <div class="flex flex-col md:flex-row md:items-center gap-6">

                            <div class="w-24 h-24 rounded-full bg-black text-white flex items-center justify-center text-3xl font-black shrink-0">
                                {{ strtoupper(substr($article->user->name, 0, 1)) }}
                            </div>

                            <div>
                                <p class="text-[11px] uppercase tracking-[0.2em] text-gray-400 font-black mb-2">
                                    Written By
                                </p>

                                <h3 class="text-3xl font-black text-gray-900 tracking-tight">
                                    {{ $article->user->name }}
                                </h3>

                                <p class="mt-4 text-gray-600 leading-8 text-[15px] max-w-3xl">
                                    Passionate content creator focused on writing valuable and insightful articles for readers around the world.
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

                {{-- SIDEBAR --}}
                <aside class="lg:col-span-4 space-y-8">

                    {{-- QUICK INFO --}}
                    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-lg shadow-gray-100 p-8 sticky top-8">

                        <div class="border-l-4 border-black pl-4 mb-8">
                            <h3 class="text-2xl font-black tracking-tight text-gray-900">
                                Article Insights
                            </h3>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-gray-400 font-black mt-1">
                                Reader Statistics
                            </p>
                        </div>

                        <div class="space-y-6">

                            <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                                <span class="text-gray-500 font-semibold text-sm">Status</span>
                                <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full text-xs font-black uppercase tracking-wide">
                                    {{ $article->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                                <span class="text-gray-500 font-semibold text-sm">Views</span>
                                <span class="font-black text-gray-900">
                                    {{ number_format($article->view_count) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                                <span class="text-gray-500 font-semibold text-sm">Category</span>
                                <span class="font-black text-gray-900">
                                    {{ $article->category->name }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between border-b border-gray-100 pb-5">
                                <span class="text-gray-500 font-semibold text-sm">Created</span>
                                <span class="font-black text-gray-900 text-right">
                                    {{ $article->created_at->format('M d, Y') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-500 font-semibold text-sm">Last Update</span>
                                <span class="font-black text-gray-900 text-right">
                                    {{ $article->updated_at->diffForHumans() }}
                                </span>
                            </div>

                        </div>

                    </div>

                </aside>

            </div>

        </div>

    </section>
</x-layout>
